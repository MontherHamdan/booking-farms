<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\FarmOwnerWallet;
use App\Models\ManualPayment;
use App\Models\WalletTransaction;
use App\Models\FarmBooking;
use App\Models\PlatformSetting;
use App\Services\FarmOwnerWalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class WalletController extends Controller
{
    protected FarmOwnerWalletService $walletService;

    public function __construct(FarmOwnerWalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Wallet overview dashboard
     */
    public function index()
    {
        try {
            // ENHANCED: Better statistics with pending balance awareness
            $totalWallets = FarmOwnerWallet::count();
            $activeWallets = FarmOwnerWallet::active()->count();
            $totalConfirmedBalance = FarmOwnerWallet::sum('balance');
            $totalPendingBalance = FarmOwnerWallet::sum('pending_balance');
            $totalAvailableBalance = $totalConfirmedBalance + $totalPendingBalance;
            $totalEarned = FarmOwnerWallet::sum('total_earned');
            $totalPaidOut = FarmOwnerWallet::sum('total_paid_out');

            // ENHANCED: Payment statistics with more context
            $minimumAmount = PlatformSetting::getMinimumTransferAmount();
            $eligiblePayments = FarmOwnerWallet::where('balance', '>=', $minimumAmount)->count();
            $eligibleAmount = FarmOwnerWallet::where('balance', '>=', $minimumAmount)->sum('balance');
            $walletsWithPendingBalance = FarmOwnerWallet::where('pending_balance', '>', 0)->count();
            $totalPendingToConfirm = FarmOwnerWallet::where('pending_balance', '>', 0)->sum('pending_balance');
            
            // Monthly statistics
            $thisMonthPayments = ManualPayment::thisMonth()->count();
            $thisMonthPaymentsAmount = ManualPayment::thisMonth()->sum('amount');

            // ENHANCED: Recent transactions with proper types
            $recentTransactions = WalletTransaction::with(['wallet.user', 'booking'])
                ->latest()
                ->limit(10)
                ->get();

            // NEW: Critical alerts for admin attention
            $criticalAlerts = [
                'pending_earnings_count' => FarmBooking::needsEarningsConfirmation()->count(),
                'missing_bank_accounts' => FarmOwnerWallet::whereHas('user', function($q) {
                    $q->whereDoesntHave('farmOwnerBankAccount');
                })->where('balance', '>=', $minimumAmount)->count(),
            ];

            // Pass transaction type configuration
            $transactionConfig = $this->getTransactionTypeConfig();

            return view('admin.wallet.index', compact(
                'totalWallets', 'activeWallets', 'totalConfirmedBalance', 'totalPendingBalance', 
                'totalAvailableBalance', 'totalEarned', 'totalPaidOut',
                'eligiblePayments', 'eligibleAmount', 'walletsWithPendingBalance', 'totalPendingToConfirm',
                'thisMonthPayments', 'thisMonthPaymentsAmount', 'recentTransactions', 'criticalAlerts',
                'transactionConfig'
            ));

        } catch (Exception $e) {
            \Log::error('Dashboard loading failed', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to load dashboard data.');
        }
    }

    /**
     * Farm owner wallets listing
     */
    public function wallets(Request $request)
    {
        try {
            $query = FarmOwnerWallet::with(['user', 'user.farmOwnerBankAccount']);

            // Filters
            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('user', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            if ($request->filled('status')) {
                if ($request->status === 'active') {
                    $query->where('is_active', true);
                } elseif ($request->status === 'inactive') {
                    $query->where('is_active', false);
                }
            }

            if ($request->filled('min_balance')) {
                $query->where('balance', '>=', $request->min_balance);
            }

            // NEW: Filter for wallets missing bank accounts
            if ($request->filled('filter') && $request->filter === 'no_bank') {
                $query->whereHas('user', function($q) {
                    $q->whereDoesntHave('farmOwnerBankAccount');
                });
            }

            // Sorting
            $sortBy = $request->input('sort', 'balance');
            $sortDirection = $request->input('direction', 'desc');
            
            if ($sortBy === 'user_name') {
                $query->join('users', 'users.id', '=', 'farm_owner_wallets.user_id')
                      ->orderBy('users.name', $sortDirection)
                      ->select('farm_owner_wallets.*');
            } else {
                $query->orderBy($sortBy, $sortDirection);
            }

            $wallets = $query->paginate(20);

            return view('admin.wallet.wallets', compact('wallets'));

        } catch (Exception $e) {
            \Log::error('Wallets listing failed', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to load wallets data.');
        }
    }

    /**
     * Show specific wallet
     */
    public function show($walletId)
    {
        try {
            $wallet = FarmOwnerWallet::with([
                'user',
                'user.farmOwnerBankAccount',
                'transactions' => function($q) { $q->latest(); },
                'manualPayments' => function($q) { $q->latest(); }
            ])->findOrFail($walletId);

            $statistics = $this->walletService->getWalletStatistics($wallet);
            
            $bookingsWithEarnings = FarmBooking::with(['farm'])
                ->whereHas('farm', function($q) use ($wallet) {
                    $q->where('user_id', $wallet->user_id);
                })
                ->where('earnings_processed', true)
                ->latest()
                ->paginate(10);

            // Pass transaction configuration
            $transactionConfig = $this->getTransactionTypeConfig();

            return view('admin.wallet.show', compact('wallet', 'statistics', 'bookingsWithEarnings', 'transactionConfig'));

        } catch (Exception $e) {
            \Log::error('Wallet show failed', ['wallet_id' => $walletId, 'error' => $e->getMessage()]);
            return redirect()->route('dashboard.wallet.wallets')->with('error', 'Wallet not found.');
        }
    }

    /**
     * Process manual payment
     */
    public function processPayment(Request $request, $userId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $user = \App\Models\User::with('farmOwnerBankAccount')->findOrFail($userId);
            $wallet = $this->walletService->getOrCreateWallet($userId);

            if (!$user->farmOwnerBankAccount) {
                return redirect()->back()->with('error', 'Farm owner has no bank account setup.');
            }

            // Only allow payment from confirmed balance
            if ($wallet->balance < $request->amount) {
                return redirect()->back()->with('error', 
                    'Insufficient confirmed balance. Available: AED ' . number_format($wallet->balance, 2) . 
                    ' (Pending balance of AED ' . number_format($wallet->pending_balance, 2) . ' is not available for payment)');
            }

            $payment = $this->walletService->createManualPayment(
                $userId,
                $request->amount,
                $user->farmOwnerBankAccount->account_type,
                $user->farmOwnerBankAccount->formatted_account_details,
                auth()->id(),
                $request->notes
            );

            return redirect()->back()->with('success', 
                "Payment of AED " . number_format($request->amount, 2) . " processed successfully for {$user->name}");

        } catch (Exception $e) {
            \Log::error('Manual payment failed', [
                'user_id' => $userId,
                'amount' => $request->amount,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Failed to process payment: ' . $e->getMessage());
        }
    }

    /**
     * Pending payments dashboard
     */
    public function pendingPayments(Request $request)
    {
        try {
            $pendingData = $this->walletService->getPendingPayments();
            
            $readyForPayment = collect($pendingData['ready_for_payment']);
            $eligibleButNotReady = collect($pendingData['eligible_but_not_ready']);
            
            // Apply filters
            if ($request->filled('filter')) {
                if ($request->filter === 'ready') {
                    $eligibleButNotReady = collect([]);
                } elseif ($request->filter === 'not_ready') {
                    $readyForPayment = collect([]);
                }
            }

            if ($request->filled('search')) {
                $search = strtolower($request->search);
                $readyForPayment = $readyForPayment->filter(function($item) use ($search) {
                    return str_contains(strtolower($item['user']['name']), $search) ||
                           str_contains(strtolower($item['user']['email']), $search);
                });
                $eligibleButNotReady = $eligibleButNotReady->filter(function($item) use ($search) {
                    return str_contains(strtolower($item['user']['name']), $search) ||
                           str_contains(strtolower($item['user']['email']), $search);
                });
            }

            return view('admin.wallet.pending-payments', compact(
                'readyForPayment',
                'eligibleButNotReady', 
                'pendingData'
            ));

        } catch (Exception $e) {
            \Log::error('Pending payments loading failed', ['error' => $e->getMessage()]);
            return redirect()->route('dashboard.wallet.index')->with('error', 'Failed to load pending payments.');
        }
    }

    /**
     * All transactions - UPDATED with proper transaction types
     */
    public function transactions(Request $request)
    {
        try {
            $query = WalletTransaction::with(['wallet.user', 'booking', 'processedBy']);

            // Apply filters
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('from_date')) {
                $query->whereDate('created_at', '>=', $request->from_date);
            }

            if ($request->filled('to_date')) {
                $query->whereDate('created_at', '<=', $request->to_date);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('reference', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhereHas('wallet.user', function($userQuery) use ($search) {
                          $userQuery->where('name', 'like', "%{$search}%")
                                   ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            }

            $transactions = $query->latest()->paginate(20);

            // FIXED: Updated statistics with correct transaction types
            $totalEarnings = (clone $query)->whereIn('type', ['pending_earning', 'earning_confirmed'])->sum('amount');
            $totalManualPayments = abs((clone $query)->where('type', 'manual_payment')->sum('amount'));
            $totalCommissions = abs((clone $query)->where('type', 'commission')->sum('amount'));
            
            // NEW: Enhanced statistics
            $pendingEarnings = (clone $query)->where('type', 'pending_earning')->sum('amount');
            $confirmedEarnings = (clone $query)->where('type', 'earning_confirmed')->sum('amount');
            $totalRefunds = abs((clone $query)->where('type', 'refund')->sum('amount'));
            $totalAdjustments = (clone $query)->whereIn('type', ['adjustment', 'bonus'])->sum('amount');

            // Pass configurations
            $transactionConfig = $this->getTransactionTypeConfig();
            $filterOptions = $this->getTransactionFilterOptions();

            return view('admin.wallet.transactions', compact(
                'transactions', 
                'totalEarnings', 
                'totalManualPayments', 
                'totalCommissions',
                'pendingEarnings',
                'confirmedEarnings',
                'totalRefunds',
                'totalAdjustments',
                'transactionConfig',
                'filterOptions'
            ));

        } catch (Exception $e) {
            \Log::error('Transactions listing failed', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to load transactions.');
        }
    }

    /**
     * Update commission rate for specific wallet
     */
    public function updateCommissionRate(Request $request, $walletId)
    {
        $minRate = PlatformSetting::getMinimumCommissionRate();
        $maxRate = PlatformSetting::getMaximumCommissionRate();
        
        $request->validate([
            'commission_rate' => "required|numeric|min:{$minRate}|max:{$maxRate}",
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $wallet = FarmOwnerWallet::findOrFail($walletId);
            $oldRate = $wallet->platform_commission_rate;
            $newRate = $request->input('commission_rate');

            $this->walletService->updateCommissionRate($wallet->user_id, $newRate);

            \Log::info('Commission rate updated by admin', [
                'wallet_id' => $wallet->id,
                'user_id' => $wallet->user_id,
                'old_rate' => $oldRate,
                'new_rate' => $newRate,
                'admin_id' => auth()->id(),
                'reason' => $request->input('reason'),
            ]);

            return redirect()->back()->with('success', "Commission rate updated from {$oldRate}% to {$newRate}%");

        } catch (Exception $e) {
            \Log::error('Commission rate update failed', [
                'wallet_id' => $walletId,
                'new_rate' => $request->commission_rate,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Failed to update commission rate: ' . $e->getMessage());
        }
    }

    /**
     * Add manual adjustment to wallet
     */
    public function addAdjustment(Request $request, $walletId)
    {
        $request->validate([
            'amount' => 'required|numeric|not_in:0',
            'description' => 'required|string|max:500',
            'type' => 'required|in:adjustment,bonus',
        ]);

        try {
            $wallet = FarmOwnerWallet::findOrFail($walletId);
            $amount = $request->input('amount');
            $description = $request->input('description');
            $type = $request->input('type');

            DB::transaction(function() use ($wallet, $amount, $description, $type) {
                if ($amount > 0) {
                    // Add funds to confirmed balance (not pending)
                    $wallet->addFunds($amount, $description, [
                        'admin_adjustment' => true,
                        'admin_id' => auth()->id(),
                        'type' => $type,
                    ], false); // false = add to confirmed balance
                } else {
                    $wallet->deductFunds(abs($amount), $type, $description, [
                        'admin_adjustment' => true,
                        'admin_id' => auth()->id(),
                    ]);
                }
            });

            \Log::info('Manual adjustment processed', [
                'wallet_id' => $walletId,
                'amount' => $amount,
                'type' => $type,
                'admin_id' => auth()->id(),
                'description' => $description
            ]);

            $actionText = $amount > 0 ? 'added to' : 'deducted from';
            return redirect()->back()->with('success', 
                "AED " . number_format(abs($amount), 2) . " {$actionText} wallet successfully");

        } catch (Exception $e) {
            \Log::error('Manual adjustment failed', [
                'wallet_id' => $walletId,
                'amount' => $request->amount,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Failed to process adjustment: ' . $e->getMessage());
        }
    }

    /**
     * NEW: Confirm pending earnings for completed bookings
     */
    public function confirmPendingEarnings(Request $request)
    {
        try {
            $result = $this->walletService->autoConfirmEarnings($request->input('limit', 50));

            if ($result['total_confirmed'] > 0) {
                return redirect()->back()->with('success', 
                    "Successfully confirmed {$result['total_confirmed']} pending earnings. " .
                    "Total amount: AED " . number_format(collect($result['confirmed'])->sum('farm_owner_earning'), 2));
            } else {
                return redirect()->back()->with('info', 'No pending earnings found to confirm.');
            }

        } catch (Exception $e) {
            \Log::error('Confirm pending earnings failed', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to confirm pending earnings: ' . $e->getMessage());
        }
    }

    /**
     * NEW: Process expired bookings
     */
    public function processExpiredBookings(Request $request)
    {
        try {
            $bookingService = app(\App\Services\FarmBookingService::class);
            $result = $bookingService->expirePendingBookings($request->input('limit', 100));

            if (count($result['expired']) > 0) {
                return redirect()->back()->with('success', 
                    "Successfully processed " . count($result['expired']) . " expired bookings.");
            } else {
                return redirect()->back()->with('info', 'No expired bookings found to process.');
            }

        } catch (Exception $e) {
            \Log::error('Process expired bookings failed', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to process expired bookings: ' . $e->getMessage());
        }
    }

    /**
     * Export wallet data
     */
    public function exportWallets()
    {
        try {
            $wallets = FarmOwnerWallet::with('user')->get();
            
            $csvData = "ID,User Name,User Email,Confirmed Balance,Pending Balance,Total Available,Total Earned,Total Paid Out,Commission Rate,Status,Created At\n";
            
            foreach ($wallets as $wallet) {
                $csvData .= implode(',', [
                    $wallet->id,
                    '"' . $wallet->user->name . '"',
                    $wallet->user->email,
                    $wallet->balance,
                    $wallet->pending_balance,
                    $wallet->getTotalAvailableBalance(),
                    $wallet->total_earned,
                    $wallet->total_paid_out ?? 0,
                    $wallet->platform_commission_rate . '%',
                    $wallet->is_active ? 'Active' : 'Inactive',
                    $wallet->created_at->format('Y-m-d H:i:s'),
                ]) . "\n";
            }

            return response($csvData)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="farm_owner_wallets_' . now()->format('Y-m-d') . '.csv"');

        } catch (Exception $e) {
            \Log::error('Wallet export failed', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to export wallet data.');
        }
    }

    /**
     * Export payment data
     */
    public function exportPayments()
    {
        try {
            $payments = ManualPayment::with(['user', 'processedBy'])->get();
            
            $csvData = "ID,Farm Owner,Amount,Payment Method,Payment Date,Processed By,Notes,Created At\n";
            
            foreach ($payments as $payment) {
                $csvData .= implode(',', [
                    $payment->id,
                    '"' . $payment->user->name . '"',
                    $payment->amount,
                    $payment->getPaymentMethodLabel(),
                    $payment->payment_date->format('Y-m-d'),
                    '"' . $payment->processedBy->name . '"',
                    '"' . str_replace('"', '""', $payment->notes ?? '') . '"', // Escape quotes
                    $payment->created_at->format('Y-m-d H:i:s'),
                ]) . "\n";
            }

            return response($csvData)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="manual_payments_' . now()->format('Y-m-d') . '.csv"');

        } catch (Exception $e) {
            \Log::error('Payment export failed', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to export payment data.');
        }
    }

    /**
     * Helper method for transaction type configuration
     */
    private function getTransactionTypeConfig(): array
    {
        return [
            'pending_earning' => [
                'label' => 'Pending Earning',
                'color' => 'warning',
                'icon' => 'clock-outline',
                'description' => 'Earnings awaiting booking completion'
            ],
            'earning_confirmed' => [
                'label' => 'Confirmed Earning', 
                'color' => 'success',
                'icon' => 'check-circle',
                'description' => 'Earnings moved to available balance'
            ],
            'manual_payment' => [
                'label' => 'Manual Payment',
                'color' => 'info',
                'icon' => 'bank-transfer-out',
                'description' => 'Payment processed by admin'
            ],
            'commission' => [
                'label' => 'Platform Commission',
                'color' => 'secondary',
                'icon' => 'percent',
                'description' => 'Platform fee deduction'
            ],
            'refund' => [
                'label' => 'Refund',
                'color' => 'danger', 
                'icon' => 'arrow-left-circle',
                'description' => 'Refund for cancelled booking'
            ],
            'adjustment' => [
                'label' => 'Adjustment',
                'color' => 'dark',
                'icon' => 'plus-minus-variant',
                'description' => 'Admin balance adjustment'
            ],
            'bonus' => [
                'label' => 'Bonus',
                'color' => 'primary',
                'icon' => 'gift',
                'description' => 'Admin bonus payment'
            ]
        ];
    }

    /**
     * Helper method for transaction filter options
     */
    private function getTransactionFilterOptions(): array
    {
        return [
            '' => 'All Types',
            'pending_earning' => 'Pending Earnings',
            'earning_confirmed' => 'Confirmed Earnings', 
            'manual_payment' => 'Manual Payments',
            'commission' => 'Platform Commission',
            'refund' => 'Refunds',
            'adjustment' => 'Adjustments',
            'bonus' => 'Bonuses'
        ];
    }

    /**
     * NEW: Get system health metrics
     */
    public function getSystemHealth(): array
    {
        try {
            $criticalAlerts = [
                'pending_earnings_count' => FarmBooking::needsEarningsConfirmation()->count(),
                'expired_bookings_count' => FarmBooking::shouldBeExpired()->count(),
                'missing_bank_accounts' => FarmOwnerWallet::whereHas('user', function($q) {
                    $q->whereDoesntHave('farmOwnerBankAccount');
                })->where('balance', '>=', PlatformSetting::getMinimumTransferAmount())->count(),
            ];

            $healthScore = 100;
            if ($criticalAlerts['pending_earnings_count'] > 10) $healthScore -= 20;
            if ($criticalAlerts['expired_bookings_count'] > 5) $healthScore -= 30;
            if ($criticalAlerts['missing_bank_accounts'] > 3) $healthScore -= 15;

            return [
                'health_score' => $healthScore,
                'status' => $healthScore >= 90 ? 'excellent' : ($healthScore >= 70 ? 'good' : 'needs_attention'),
                'alerts' => $criticalAlerts
            ];

        } catch (Exception $e) {
            \Log::error('System health check failed', ['error' => $e->getMessage()]);
            return [
                'health_score' => 0,
                'status' => 'error',
                'alerts' => []
            ];
        }
    }
}