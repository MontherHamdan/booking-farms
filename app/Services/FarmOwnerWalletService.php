<?php

namespace App\Services;

use App\Models\FarmBooking;
use App\Models\FarmOwnerWallet;
use App\Models\User;
use App\Models\ManualPayment;
use App\Models\FarmOwnerBankAccount;
use App\Models\PlatformSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class FarmOwnerWalletService
{
    /**
     * Get or create wallet for farm owner
     */
    public function getOrCreateWallet(int $userId): FarmOwnerWallet
    {
        return FarmOwnerWallet::firstOrCreate(
            ['user_id' => $userId],
            [
                'balance' => 0.00,
                'pending_balance' => 0.00,
                'total_earned' => 0.00,
                'total_paid_out' => 0.00,
                'platform_commission_rate' => config('app.default_commission_rate', 15.00),
                'is_active' => true,
            ]
        );
    }

    /**
     * Process earnings from a confirmed booking
     */
    public function processBookingEarning(FarmBooking $booking): array
    {
        if ($booking->earnings_processed) {
            throw new \InvalidArgumentException('Booking earnings already processed');
        }

        if ($booking->booking_status !== FarmBooking::BOOKING_STATUS_CONFIRMED) {
            throw new \InvalidArgumentException('Only confirmed bookings can generate earnings');
        }

        try {
            DB::beginTransaction();

            // Get farm owner's wallet
            $farmOwner = $booking->farm->user;
            $wallet = $this->getOrCreateWallet($farmOwner->id);

            // Calculate commission and earnings
            $totalAmount = $booking->total_amount;
            $commissionRate = $wallet->platform_commission_rate;
            $commissionAmount = ($totalAmount * $commissionRate) / 100;
            $farmOwnerEarning = $totalAmount - $commissionAmount;

            // Update booking with earning details
            $booking->update([
                'platform_commission_rate' => $commissionRate,
                'platform_commission_amount' => $commissionAmount,
                'farm_owner_earning' => $farmOwnerEarning,
                'earnings_processed' => true,
                'earnings_processed_at' => now(),
            ]);

            // Add earning to wallet
            $transaction = $wallet->addFunds(
                $farmOwnerEarning,
                "Earning from booking #{$booking->booking_reference}",
                [
                    'booking_id' => $booking->id,
                    'booking_reference' => $booking->booking_reference,
                    'total_amount' => $totalAmount,
                    'commission_rate' => $commissionRate,
                    'commission_amount' => $commissionAmount,
                    'farm_owner_earning' => $farmOwnerEarning,
                    'customer_name' => $booking->customer_name,
                    'booking_dates' => $booking->booking_dates,
                ]
            );

            // Create commission transaction record (for platform tracking)
            $wallet->transactions()->create([
                'booking_id' => $booking->id,
                'reference' => 'COMM-' . strtoupper(uniqid()) . '-' . $wallet->user_id,
                'type' => 'commission',
                'amount' => -$commissionAmount, // Negative for platform commission
                'balance_before' => $wallet->balance, // After earning was added
                'balance_after' => $wallet->balance, // No change to wallet balance
                'description' => "Platform commission for booking #{$booking->booking_reference}",
                'status' => 'completed',
                'metadata' => [
                    'booking_id' => $booking->id,
                    'booking_reference' => $booking->booking_reference,
                    'commission_rate' => $commissionRate,
                    'total_booking_amount' => $totalAmount,
                ],
                'processed_at' => now(),
            ]);

            DB::commit();

            Log::info('Booking earning processed successfully', [
                'booking_id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'farm_owner_id' => $farmOwner->id,
                'total_amount' => $totalAmount,
                'commission_amount' => $commissionAmount,
                'farm_owner_earning' => $farmOwnerEarning,
                'new_wallet_balance' => $wallet->fresh()->balance,
            ]);

            return [
                'success' => true,
                'total_amount' => $totalAmount,
                'commission_rate' => $commissionRate,
                'commission_amount' => $commissionAmount,
                'farm_owner_earning' => $farmOwnerEarning,
                'new_wallet_balance' => $wallet->fresh()->balance,
                'transaction' => $transaction,
            ];

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to process booking earning', [
                'booking_id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Handle refund for cancelled booking
     */
    public function processBookingRefund(FarmBooking $booking): array
    {
        if (!$booking->earnings_processed) {
            throw new \InvalidArgumentException('Cannot refund booking that has no processed earnings');
        }

        if ($booking->booking_status !== FarmBooking::BOOKING_STATUS_CANCELLED) {
            throw new \InvalidArgumentException('Only cancelled bookings can be refunded');
        }

        try {
            DB::beginTransaction();

            $farmOwner = $booking->farm->user;
            $wallet = $this->getOrCreateWallet($farmOwner->id);
            $refundAmount = $booking->farm_owner_earning;

            // Check if wallet has sufficient balance
            if ($wallet->balance < $refundAmount) {
                throw new \InvalidArgumentException('Insufficient wallet balance for refund');
            }

            // Deduct refund from wallet
            $transaction = $wallet->deductFunds(
                $refundAmount,
                'refund',
                "Refund for cancelled booking #{$booking->booking_reference}",
                [
                    'booking_id' => $booking->id,
                    'booking_reference' => $booking->booking_reference,
                    'original_earning' => $refundAmount,
                    'customer_name' => $booking->customer_name,
                ]
            );

            // Mark booking as refund processed
            $booking->update([
                'farm_owner_earning' => 0, // Reset earning to 0 after refund
            ]);

            DB::commit();

            Log::info('Booking refund processed successfully', [
                'booking_id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'farm_owner_id' => $farmOwner->id,
                'refund_amount' => $refundAmount,
                'new_wallet_balance' => $wallet->fresh()->balance,
            ]);

            return [
                'success' => true,
                'refund_amount' => $refundAmount,
                'new_wallet_balance' => $wallet->fresh()->balance,
                'transaction' => $transaction,
            ];

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to process booking refund', [
                'booking_id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Create manual payment record when admin processes payment (NEW)
     */
    public function createManualPayment(
        int $userId,
        float $amount,
        string $paymentMethod,
        array $bankAccountDetails,
        int $adminId,
        ?string $notes = null
    ): ManualPayment {
        try {
            DB::beginTransaction();

            $wallet = $this->getOrCreateWallet($userId);

            // Check wallet balance
            if ($wallet->balance < $amount) {
                throw new \InvalidArgumentException('Insufficient wallet balance for payment');
            }

            // Deduct from wallet
            $wallet->deductFunds(
                $amount,
                'manual_payment',
                "Manual payment processed - {$paymentMethod}",
                [
                    'payment_method' => $paymentMethod,
                    'processed_by' => $adminId,
                    'bank_details' => $bankAccountDetails,
                ]
            );

            // Create payment record
            $payment = ManualPayment::createPaymentRecord(
                $userId,
                $amount,
                $paymentMethod,
                $bankAccountDetails,
                $adminId,
                $notes
            );

            // Update wallet totals
            $wallet->increment('total_paid_out', $amount);
            $wallet->update(['last_payment_at' => now()]);

            DB::commit();

            Log::info('Manual payment processed successfully', [
                'user_id' => $userId,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'admin_id' => $adminId,
                'new_balance' => $wallet->fresh()->balance,
            ]);

            return $payment;

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to process manual payment', [
                'user_id' => $userId,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Get wallets eligible for payment (NEW)
     */
    public function getPaymentEligibleWallets(): array
    {
        $minimumAmount = PlatformSetting::getMinimumTransferAmount();
        $transferFrequencyDays = PlatformSetting::getTransferFrequencyDays();

        $wallets = FarmOwnerWallet::with(['user', 'user.farmOwnerBankAccount'])
            ->where('balance', '>=', $minimumAmount)
            ->where('is_active', true)
            ->get();

        return $wallets->map(function ($wallet) use ($transferFrequencyDays) {
            $daysSinceLastPayment = $wallet->getDaysSinceLastPayment();
            $isReady = $daysSinceLastPayment >= $transferFrequencyDays;

            return [
                'wallet_id' => $wallet->id,
                'user' => [
                    'id' => $wallet->user->id,
                    'name' => $wallet->user->name,
                    'email' => $wallet->user->email,
                ],
                'balance' => $wallet->balance,
                'total_earned' => $wallet->total_earned,
                'total_paid_out' => $wallet->total_paid_out,
                'last_payment_at' => $wallet->last_payment_at,
                'days_since_last_payment' => $daysSinceLastPayment,
                'is_ready_for_payment' => $isReady,
                'is_eligible' => $wallet->isEligibleForPayment(),
                'has_bank_account' => $wallet->user->hasBankAccount(),
                'bank_account' => $wallet->user->farmOwnerBankAccount ? [
                    'account_type' => $wallet->user->farmOwnerBankAccount->account_type,
                    'account_type_label' => $wallet->user->farmOwnerBankAccount->getAccountTypeLabel(),
                    'primary_identifier' => $wallet->user->farmOwnerBankAccount->primary_identifier,
                    'formatted_details' => $wallet->user->farmOwnerBankAccount->formatted_account_details,
                ] : null,
            ];
        })->toArray();
    }

    /**
     * Get pending payments dashboard data (NEW)
     */
    public function getPendingPayments(): array
    {
        $eligibleWallets = $this->getPaymentEligibleWallets();
        
        $readyForPayment = collect($eligibleWallets)->where('is_ready_for_payment', true);
        $eligibleButNotReady = collect($eligibleWallets)->where('is_ready_for_payment', false);
        
        return [
            'summary' => [
                'total_eligible_wallets' => count($eligibleWallets),
                'ready_for_payment' => $readyForPayment->count(),
                'total_ready_amount' => $readyForPayment->sum('balance'),
                'eligible_but_not_ready' => $eligibleButNotReady->count(),
                'total_eligible_amount' => collect($eligibleWallets)->sum('balance'),
                'missing_bank_accounts' => collect($eligibleWallets)->where('has_bank_account', false)->count(),
            ],
            'ready_for_payment' => $readyForPayment->values()->toArray(),
            'eligible_but_not_ready' => $eligibleButNotReady->values()->toArray(),
            'settings' => [
                'transfer_frequency_days' => PlatformSetting::getTransferFrequencyDays(),
                'minimum_transfer_amount' => PlatformSetting::getMinimumTransferAmount(),
            ],
        ];
    }

    /**
     * Get wallet dashboard data (UPDATED - removed withdrawal references)
     */
    public function getWalletDashboard(int $userId): array
    {
        $wallet = $this->getOrCreateWallet($userId);
        $user = User::find($userId);
        
        return [
            'wallet' => [
                'balance' => $wallet->balance,
                'pending_balance' => $wallet->pending_balance,
                'total_earned' => $wallet->total_earned,
                'total_paid_out' => $wallet->total_paid_out, // UPDATED: renamed from total_withdrawn
                'commission_rate' => $wallet->platform_commission_rate,
                'last_transaction_at' => $wallet->last_transaction_at,
                'last_payment_at' => $wallet->last_payment_at, // NEW
                'is_eligible_for_payment' => $wallet->isEligibleForPayment(), // NEW
                'days_since_last_payment' => $wallet->getDaysSinceLastPayment(), // NEW
            ],
            'statistics' => $this->getWalletStatistics($wallet),
            'recent_transactions' => $wallet->recentTransactions(10)->get(),
            'recent_payments' => $user->manualPayments() // NEW: replaced pending_withdrawals
                                    ->with('processedBy:id,name')
                                    ->orderBy('payment_date', 'desc')
                                    ->limit(5)
                                    ->get(),
            'bank_account' => $user->farmOwnerBankAccount, // NEW
            'payment_settings' => [ // NEW
                'transfer_frequency_days' => PlatformSetting::getTransferFrequencyDays(),
                'minimum_transfer_amount' => PlatformSetting::getMinimumTransferAmount(),
            ],
        ];
    }

    /**
     * Get wallet statistics (UPDATED - removed withdrawal references)
     */
    public function getWalletStatistics(FarmOwnerWallet $wallet): array
    {
        $thisMonth = now();
        $lastMonth = now()->subMonth();

        return [
            // This month stats
            'this_month' => [
                'earnings' => $wallet->transactions()
                                   ->earnings()
                                   ->thisMonth()
                                   ->sum('amount'),
                'payments' => abs($wallet->transactions() // UPDATED: was withdrawals
                                      ->where('type', 'manual_payment')
                                      ->thisMonth()
                                      ->sum('amount')),
                'transactions_count' => $wallet->transactions()
                                              ->thisMonth()
                                              ->count(),
            ],
            
            // Last month stats
            'last_month' => [
                'earnings' => $wallet->transactions()
                                   ->earnings()
                                   ->lastMonth()
                                   ->sum('amount'),
                'payments' => abs($wallet->transactions() // UPDATED: was withdrawals
                                      ->where('type', 'manual_payment')
                                      ->lastMonth()
                                      ->sum('amount')),
                'transactions_count' => $wallet->transactions()
                                              ->lastMonth()
                                              ->count(),
            ],

            // All time stats
            'all_time' => [
                'total_transactions' => $wallet->transactions()->count(),
                'total_earnings_transactions' => $wallet->transactions()->earnings()->count(),
                'total_payment_transactions' => $wallet->transactions()->where('type', 'manual_payment')->count(), // UPDATED
                'average_earning_per_transaction' => $wallet->transactions()
                                                           ->earnings()
                                                           ->avg('amount') ?: 0,
            ],

            // Payment stats (NEW - replaced withdrawal stats)
            'payments' => [
                'total_payments' => $wallet->manualPayments()->count(),
                'total_amount_paid' => $wallet->manualPayments()->sum('amount'),
                'last_payment' => $wallet->last_payment_at,
                'days_since_last_payment' => $wallet->getDaysSinceLastPayment(),
                'is_eligible_for_payment' => $wallet->isEligibleForPayment(),
                'is_ready_for_payment' => $wallet->isReadyForPayment(),
            ],
        ];
    }

    /**
     * Get bookings with earnings for farm owner
     */
    public function getFarmOwnerBookingsWithEarnings(int $userId, array $filters = []): array
    {
        $query = FarmBooking::with(['farm', 'farm.mainImage'])
                           ->whereHas('farm', function ($q) use ($userId) {
                               $q->where('user_id', $userId);
                           })
                           ->where('earnings_processed', true);

        // Apply filters
        if (isset($filters['status'])) {
            $query->where('booking_status', $filters['status']);
        }

        if (isset($filters['from_date'])) {
            $query->where('start_date', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date'])) {
            $query->where('end_date', '<=', $filters['to_date']);
        }

        return $query->orderBy('created_at', 'desc')
                    ->paginate($filters['per_page'] ?? 15)
                    ->toArray();
    }

    /**
     * Auto-process earnings for completed bookings
     */
    public function autoProcessEarnings(int $limit = 100): array
    {
        $processed = [];
        $failed = [];

        // Get confirmed bookings that haven't had their earnings processed yet
        $bookings = FarmBooking::where('booking_status', FarmBooking::BOOKING_STATUS_CONFIRMED)
                              ->where('earnings_processed', false)
                              ->whereNotNull('stripe_payment_intent_id') // Ensure payment completed
                              ->limit($limit)
                              ->get();

        foreach ($bookings as $booking) {
            try {
                $result = $this->processBookingEarning($booking);
                $processed[] = [
                    'booking_reference' => $booking->booking_reference,
                    'farm_owner_earning' => $result['farm_owner_earning'],
                ];
            } catch (Exception $e) {
                $failed[] = [
                    'booking_reference' => $booking->booking_reference,
                    'error' => $e->getMessage(),
                ];
                
                Log::error('Auto-process earnings failed', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [
            'processed' => $processed,
            'failed' => $failed,
            'total_processed' => count($processed),
            'total_failed' => count($failed),
        ];
    }

    /**
     * Update commission rate for farm owner
     */
    public function updateCommissionRate(int $userId, float $newRate): FarmOwnerWallet
    {
        if ($newRate < 0 || $newRate > 50) {
            throw new \InvalidArgumentException('Commission rate must be between 0% and 50%');
        }

        $wallet = $this->getOrCreateWallet($userId);
        $oldRate = $wallet->platform_commission_rate;
        
        $wallet->update(['platform_commission_rate' => $newRate]);

        Log::info('Commission rate updated', [
            'user_id' => $userId,
            'old_rate' => $oldRate,
            'new_rate' => $newRate,
        ]);

        return $wallet->fresh();
    }

    /**
     * Get payment statistics for dashboard (NEW)
     */
    public function getPaymentStatistics(array $filters = []): array
    {
        $query = ManualPayment::query();

        // Apply date filters
        if (isset($filters['from_date'])) {
            $query->where('payment_date', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date'])) {
            $query->where('payment_date', '<=', $filters['to_date']);
        }

        $payments = $query->get();

        return [
            'total_payments' => $payments->count(),
            'total_amount' => $payments->sum('amount'),
            'iban_payments' => $payments->where('payment_method', 'iban')->count(),
            'cliq_payments' => $payments->where('payment_method', 'cliq')->count(),
            'iban_amount' => $payments->where('payment_method', 'iban')->sum('amount'),
            'cliq_amount' => $payments->where('payment_method', 'cliq')->sum('amount'),
            'average_payment' => $payments->count() > 0 ? $payments->avg('amount') : 0,
            'this_month_payments' => $payments->where('payment_date', '>=', now()->startOfMonth())->count(),
            'this_month_amount' => $payments->where('payment_date', '>=', now()->startOfMonth())->sum('amount'),
        ];
    }
}