<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\FarmOwnerWallet;
use App\Models\ManualPayment; // NEW
use App\Models\WalletTransaction;
use App\Models\FarmBooking;
use App\Models\PlatformSetting; // NEW
use App\Services\FarmOwnerWalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletManagementController extends Controller
{
    protected FarmOwnerWalletService $walletService;

    public function __construct(FarmOwnerWalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Wallet overview dashboard (UPDATED)
     */
    public function index()
    {
        // General wallet statistics
        $totalWallets = FarmOwnerWallet::count();
        $activeWallets = FarmOwnerWallet::active()->count();
        $totalBalance = FarmOwnerWallet::sum('balance');
        $totalEarned = FarmOwnerWallet::sum('total_earned');
        $totalPaidOut = FarmOwnerWallet::sum('total_paid_out'); // UPDATED: renamed from total_withdrawn

        // Manual payment statistics (NEW - replaced withdrawal stats)
        $eligiblePayments = FarmOwnerWallet::where('balance', '>=', PlatformSetting::getMinimumTransferAmount())->count();
        $eligibleAmount = FarmOwnerWallet::where('balance', '>=', PlatformSetting::getMinimumTransferAmount())->sum('balance');
        $thisMonthPayments = ManualPayment::thisMonth()->count();
        $thisMonthPaymentsAmount = ManualPayment::thisMonth()->sum('amount');

        // Recent transactions
        $recentTransactions = WalletTransaction::with(['wallet.user', 'booking'])
            ->latest()
            ->limit(10)
            ->get();

        // Monthly statistics (UPDATED)
        $monthlyStats = WalletTransaction::selectRaw('
                YEAR(created_at) as year,
                MONTH(created_at) as month,
                SUM(CASE WHEN type = "earning" THEN amount ELSE 0 END) as earnings,
                SUM(CASE WHEN type = "manual_payment" THEN ABS(amount) ELSE 0 END) as payments,
                SUM(CASE WHEN type = "commission" THEN ABS(amount) ELSE 0 END) as commissions,
                COUNT(*) as transactions
            ')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return view('admin.wallet.index', compact(
            'totalWallets',
            'activeWallets', 
            'totalBalance',
            'totalEarned',
            'totalPaidOut', // UPDATED
            'eligiblePayments', // NEW
            'eligibleAmount', // NEW
            'thisMonthPayments', // NEW
            'thisMonthPaymentsAmount', // NEW
            'recentTransactions',
            'monthlyStats'
        ));
    }

    /**
     * Farm owner wallets listing
     */
    public function wallets(Request $request)
    {
        $query = FarmOwnerWallet::with(['user', 'user.farmOwnerBankAccount', 'transactions' => function($q) {
            $q->latest()->limit(3);
        }]);

        // Filter by balance
        if ($request->filled('min_balance')) {
            $query->where('balance', '>=', $request->min_balance);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Search by user name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Sort
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
    }

    /**
     * Show specific wallet details (UPDATED)
     */
    public function showWallet($walletId)
    {
        $wallet = FarmOwnerWallet::with([
            'user',
            'user.farmOwnerBankAccount', // NEW
            'transactions' => function($q) { $q->latest(); },
            'manualPayments' => function($q) { $q->latest(); } // NEW: replaced withdrawalRequests
        ])->findOrFail($walletId);

        $statistics = $this->walletService->getWalletStatistics($wallet);
        
        // Get farm owner's bookings with earnings
        $bookingsWithEarnings = FarmBooking::with(['farm'])
            ->whereHas('farm', function($q) use ($wallet) {
                $q->where('user_id', $wallet->user_id);
            })
            ->where('earnings_processed', true)
            ->latest()
            ->paginate(10);

        return view('admin.wallet.show', compact('wallet', 'statistics', 'bookingsWithEarnings'));
    }

    /**
     * Show pending payments dashboard (NEW)
     */
    public function pendingPayments(Request $request)
    {
        $pendingData = $this->walletService->getPendingPayments();
        
        // Filter ready for payment vs eligible but not ready
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
    }

    /**
     * Process manual payment (NEW)
     */
    public function processManualPayment(Request $request, $userId)
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

            if ($wallet->balance < $request->amount) {
                return redirect()->back()->with('error', 'Insufficient wallet balance for this payment.');
            }

            // Create manual payment record
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

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to process payment: ' . $e->getMessage());
        }
    }

    /**
     * Payment settings page (NEW)
     */
    public function paymentSettings()
    {
        $settings = [
            'transfer_frequency_days' => PlatformSetting::getTransferFrequencyDays(),
            'minimum_transfer_amount' => PlatformSetting::getMinimumTransferAmount(),
        ];

        $paymentStats = $this->walletService->getPaymentStatistics();

        return view('admin.wallet.payment-settings', compact('settings', 'paymentStats'));
    }

    /**
     * Update payment settings (NEW)
     */
    public function updatePaymentSettings(Request $request)
    {
        $request->validate([
            'transfer_frequency_days' => 'required|integer|min:1|max:365',
            'minimum_transfer_amount' => 'required|numeric|min:1|max:10000',
        ]);

        try {
            PlatformSetting::setTransferFrequencyDays($request->transfer_frequency_days);
            PlatformSetting::setMinimumTransferAmount($request->minimum_transfer_amount);

            return redirect()->back()->with('success', 'Payment settings updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }

    /**
     * Transactions listing (UPDATED)
     */
    public function transactions(Request $request)
    {
        $query = WalletTransaction::with(['wallet.user', 'booking', 'processedBy']);

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

        // Search
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

        // Calculate summary for current results (UPDATED)
        $totalEarnings = (clone $query)->where('type', 'earning')->sum('amount');
        $totalPayments = abs((clone $query)->where('type', 'manual_payment')->sum('amount')); // UPDATED
        $totalCommissions = abs((clone $query)->where('type', 'commission')->sum('amount'));

        return view('admin.wallet.transactions', compact(
            'transactions', 
            'totalEarnings', 
            'totalPayments', // UPDATED: renamed from totalWithdrawals
            'totalCommissions'
        ));
    }

    /**
     * Update commission rate for farm owner
     */
    public function updateCommissionRate(Request $request, $walletId)
    {
        $request->validate([
            'commission_rate' => 'required|numeric|min:0|max:50',
            'reason' => 'nullable|string|max:500',
        ]);

        $wallet = FarmOwnerWallet::findOrFail($walletId);
        $oldRate = $wallet->platform_commission_rate;
        $newRate = $request->input('commission_rate');

        try {
            $this->walletService->updateCommissionRate($wallet->user_id, $newRate);

            // Log the change
            \Log::info('Commission rate updated by admin', [
                'wallet_id' => $wallet->id,
                'user_id' => $wallet->user_id,
                'old_rate' => $oldRate,
                'new_rate' => $newRate,
                'admin_id' => auth()->id(),
                'reason' => $request->input('reason'),
            ]);

            return redirect()->back()->with('success', "Commission rate updated from {$oldRate}% to {$newRate}%");

        } catch (\Exception $e) {
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

        $wallet = FarmOwnerWallet::findOrFail($walletId);
        $amount = $request->input('amount');
        $description = $request->input('description');
        $type = $request->input('type');

        try {
            DB::transaction(function() use ($wallet, $amount, $description, $type) {
                if ($amount > 0) {
                    $wallet->addFunds($amount, $description, [
                        'admin_adjustment' => true,
                        'admin_id' => auth()->id(),
                        'type' => $type,
                    ]);
                } else {
                    $wallet->deductFunds(abs($amount), $type, $description, [
                        'admin_adjustment' => true,
                        'admin_id' => auth()->id(),
                    ]);
                }
            });

            $actionText = $amount > 0 ? 'added to' : 'deducted from';
            return redirect()->back()->with('success', "AED " . number_format(abs($amount), 2) . " {$actionText} wallet successfully");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to process adjustment: ' . $e->getMessage());
        }
    }

    /**
     * Export data (UPDATED)
     */
    public function export(Request $request)
    {
        $type = $request->input('type', 'wallets'); // wallets, transactions, payments
        
        switch ($type) {
            case 'wallets':
                return $this->exportWallets($request);
            case 'transactions':
                return $this->exportTransactions($request);
            case 'payments': // NEW: replaced withdrawals
                return $this->exportPayments($request);
            default:
                return redirect()->back()->with('error', 'Invalid export type');
        }
    }

    private function exportWallets($request)
    {
        $wallets = FarmOwnerWallet::with('user')->get();
        
        $csvData = "ID,User Name,User Email,Balance,Total Earned,Total Paid Out,Commission Rate,Status,Created At\n"; // UPDATED header
        
        foreach ($wallets as $wallet) {
            $csvData .= implode(',', [
                $wallet->id,
                '"' . $wallet->user->name . '"',
                $wallet->user->email,
                $wallet->balance,
                $wallet->total_earned,
                $wallet->total_paid_out ?? 0, // UPDATED: was total_withdrawn
                $wallet->platform_commission_rate . '%',
                $wallet->is_active ? 'Active' : 'Inactive',
                $wallet->created_at->format('Y-m-d H:i:s'),
            ]) . "\n";
        }

        return response($csvData)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="farm_owner_wallets.csv"');
    }

    private function exportTransactions($request)
    {
        return redirect()->back()->with('info', 'Transaction export feature coming soon');
    }

    private function exportPayments($request) // NEW: replaced exportWithdrawals
    {
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
                '"' . ($payment->notes ?? '') . '"',
                $payment->created_at->format('Y-m-d H:i:s'),
            ]) . "\n";
        }

        return response($csvData)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="manual_payments.csv"');
    }
}