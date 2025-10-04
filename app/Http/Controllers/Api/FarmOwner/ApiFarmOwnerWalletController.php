<?php

namespace App\Http\Controllers\Api\FarmOwner;

use App\Http\Controllers\Controller;
use App\Http\Resources\WalletTransactionCollection;
use App\Http\Resources\PaymentHistoryCollection;
use App\Services\FarmOwnerWalletService;
use App\Models\ManualPayment;
use App\Models\PlatformSetting;
use App\Traits\JsonResponseTrait;
use App\Traits\ExceptionLoggerTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class ApiFarmOwnerWalletController extends Controller
{
    use JsonResponseTrait, ExceptionLoggerTrait;

    protected FarmOwnerWalletService $walletService;

    public function __construct(FarmOwnerWalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Get wallet dashboard data
     */
    public function dashboard(): JsonResponse
    {
        try {
            $userId = Auth::id();
            $dashboardData = $this->walletService->getWalletDashboard($userId);

            // Add localized labels to dashboard data
            $dashboardData['wallet']['commission_rate_label'] = __('wallet.labels.commission_rate');
            $dashboardData['wallet']['balance_label'] = __('wallet.labels.balance');
            $dashboardData['wallet']['pending_balance_label'] = __('wallet.labels.pending_balance');
            $dashboardData['wallet']['total_earned_label'] = __('wallet.labels.total_earned');
            $dashboardData['wallet']['total_paid_out_label'] = __('wallet.labels.total_paid_out');

            return $this->successResponse(true, $dashboardData, null, 200);

        } catch (Exception $e) {
            $this->logException($e, [
                'action' => 'get wallet dashboard',
                'user_id' => Auth::id()
            ]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    /**
     * Get wallet balance and basic info
     */
    public function balance(): JsonResponse
    {
        try {
            $userId = Auth::id();
            $user = Auth::user();
            $wallet = $this->walletService->getOrCreateWallet($userId);

            $balanceData = [
                'balance' => $wallet->balance,
                'balance_label' => __('wallet.labels.balance'),
                'pending_balance' => $wallet->pending_balance,
                'pending_balance_label' => __('wallet.labels.pending_balance'),
                'total_earned' => $wallet->total_earned,
                'total_earned_label' => __('wallet.labels.total_earned'),
                'total_paid_out' => $wallet->total_paid_out,
                'total_paid_out_label' => __('wallet.labels.total_paid_out'),
                'commission_rate' => $wallet->platform_commission_rate,
                'commission_rate_label' => __('wallet.labels.commission_rate'),
                'last_transaction_at' => $wallet->last_transaction_at,
                'last_payment_at' => $wallet->last_payment_at,
                'is_eligible_for_payment' => $wallet->isEligibleForPayment(),
                'minimum_payment_amount' => PlatformSetting::getMinimumTransferAmount(),
                'minimum_payment_label' => __('wallet.labels.minimum_transfer'),
                'has_bank_account' => $user->hasBankAccount(),
                'days_since_last_payment' => $wallet->getDaysSinceLastPayment(),
                'transfer_frequency_days' => PlatformSetting::getTransferFrequencyDays(),
                'transfer_frequency_label' => __('wallet.labels.transfer_frequency'),
            ];

            return $this->successResponse(true, $balanceData, null, 200);

        } catch (Exception $e) {
            $this->logException($e, [
                'action' => 'get wallet balance',
                'user_id' => Auth::id()
            ]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    /**
     * Get wallet transactions
     */
    public function transactions(Request $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            $wallet = $this->walletService->getOrCreateWallet($userId);

            $query = $wallet->transactions()->with(['booking:id,booking_reference', 'processedBy:id,name']);

            // Filter by transaction type
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Filter by date range
            if ($request->filled('from_date')) {
                $query->whereDate('created_at', '>=', $request->from_date);
            }

            if ($request->filled('to_date')) {
                $query->whereDate('created_at', '<=', $request->to_date);
            }

            // Search by reference or description
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('reference', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $transactions = $query->orderBy('created_at', 'desc')
                                 ->paginate($request->per_page ?? 15);

            return $this->successResponse(true, new WalletTransactionCollection($transactions), null, 200);

        } catch (Exception $e) {
            $this->logException($e, [
                'action' => 'get wallet transactions',
                'user_id' => Auth::id(),
                'filters' => $request->only(['type', 'status', 'from_date', 'to_date', 'search'])
            ]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    /**
     * Get payment history for farm owner
     */
    public function paymentHistory(Request $request): JsonResponse
    {
        try {
            $userId = Auth::id();

            $query = ManualPayment::where('user_id', $userId)
                                  ->with(['processedBy:id,name']);

            // Filter by date range
            if ($request->filled('from_date')) {
                $query->whereDate('payment_date', '>=', $request->from_date);
            }

            if ($request->filled('to_date')) {
                $query->whereDate('payment_date', '<=', $request->to_date);
            }

            // Filter by payment method
            if ($request->filled('payment_method')) {
                $query->where('payment_method', $request->payment_method);
            }

            // Search by notes or payment details
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('notes', 'like', "%{$search}%")
                      ->orWhereJsonContains('payment_details', $search);
                });
            }

            $payments = $query->orderBy('payment_date', 'desc')
                             ->paginate($request->per_page ?? 15);

            return $this->successResponse(true, new PaymentHistoryCollection($payments), null, 200);

        } catch (Exception $e) {
            $this->logException($e, [
                'action' => 'get payment history',
                'user_id' => Auth::id(),
                'filters' => $request->only(['from_date', 'to_date', 'payment_method', 'search'])
            ]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    /**
     * Get wallet statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            $wallet = $this->walletService->getOrCreateWallet($userId);
            $statistics = $this->walletService->getWalletStatistics($wallet);
    
            // Add additional period statistics if requested
            $fromDate = $request->input('from_date');
            $toDate = $request->input('to_date');
    
            if ($fromDate && $toDate) {
                $customPeriodStats = $wallet->transactions()
                                          ->whereBetween('created_at', [$fromDate, $toDate])
                                          ->selectRaw('
                                              SUM(CASE WHEN type IN ("pending_earning", "earning_confirmed") THEN amount ELSE 0 END) as earnings,
                                              SUM(CASE WHEN type = "manual_payment" THEN ABS(amount) ELSE 0 END) as payments,
                                              SUM(CASE WHEN type = "pending_earning" THEN amount ELSE 0 END) as pending_earnings,
                                              SUM(CASE WHEN type = "earning_confirmed" THEN amount ELSE 0 END) as confirmed_earnings,
                                              COUNT(*) as transactions_count
                                          ')
                                          ->first();
    
                $statistics['custom_period'] = [
                    'from_date' => $fromDate,
                    'to_date' => $toDate,
                    'earnings' => $customPeriodStats->earnings ?: 0,
                    'pending_earnings' => $customPeriodStats->pending_earnings ?: 0, // NEW
                    'confirmed_earnings' => $customPeriodStats->confirmed_earnings ?: 0, // NEW
                    'payments' => $customPeriodStats->payments ?: 0,
                    'transactions_count' => $customPeriodStats->transactions_count ?: 0,
                ];
            }
    
            // Add localized labels - UPDATED
            $statistics['labels'] = [
                'pending_earning' => __('wallet.transaction_types.pending_earning'),
                'earning_confirmed' => __('wallet.transaction_types.earning_confirmed'),
                'manual_payment' => __('wallet.transaction_types.manual_payment'),
                'commission' => __('wallet.transaction_types.commission'),
                'refund' => __('wallet.transaction_types.refund'),
            ];
    
            return $this->successResponse(true, $statistics, null, 200);
    
        } catch (Exception $e) {
            $this->logException($e, [
                'action' => 'get wallet statistics',
                'user_id' => Auth::id()
            ]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    /**
     * Get transaction types with labels
     */
    public function transactionTypes(): JsonResponse
    {
        try {
            $transactionTypes = [
                [
                    'key' => 'pending_earning',
                    'label' => __('wallet.transaction_types.pending_earning'),
                    'description' => __('wallet.transaction_descriptions.pending_earning'),
                ],
                [
                    'key' => 'earning_confirmed',
                    'label' => __('wallet.transaction_types.earning_confirmed'),
                    'description' => __('wallet.transaction_descriptions.earning_confirmed'),
                ],
                [
                    'key' => 'manual_payment',
                    'label' => __('wallet.transaction_types.manual_payment'),
                    'description' => __('wallet.transaction_descriptions.manual_payment'),
                ],
                [
                    'key' => 'commission',
                    'label' => __('wallet.transaction_types.commission'),
                    'description' => __('wallet.transaction_descriptions.commission'),
                ],
                [
                    'key' => 'refund',
                    'label' => __('wallet.transaction_types.refund'),
                    'description' => __('wallet.transaction_descriptions.refund'),
                ],
                [
                    'key' => 'adjustment',
                    'label' => __('wallet.transaction_types.adjustment'),
                    'description' => __('wallet.transaction_descriptions.adjustment'),
                ],
                [
                    'key' => 'bonus',
                    'label' => __('wallet.transaction_types.bonus'),
                    'description' => __('wallet.transaction_descriptions.bonus'),
                ],
            ];
    
            return $this->successResponse(true, $transactionTypes, null, 200);
    
        } catch (Exception $e) {
            $this->logException($e, [
                'action' => 'get transaction types',
                'user_id' => Auth::id()
            ]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }
}