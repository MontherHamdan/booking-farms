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
        // Basic statistics
        $totalWallets = FarmOwnerWallet::count();
        $activeWallets = FarmOwnerWallet::active()->count();
        $totalBalance = FarmOwnerWallet::sum('balance');
        $totalEarned = FarmOwnerWallet::sum('total_earned');
        $totalPaidOut = FarmOwnerWallet::sum('total_paid_out');

        // Payment statistics
        $eligiblePayments = FarmOwnerWallet::where('balance', '>=', PlatformSetting::getMinimumTransferAmount())->count();
        $eligibleAmount = FarmOwnerWallet::where('balance', '>=', PlatformSetting::getMinimumTransferAmount())->sum('balance');
        $thisMonthPayments = ManualPayment::thisMonth()->count();
        $thisMonthPaymentsAmount = ManualPayment::thisMonth()->sum('amount');

        // Recent transactions
        $recentTransactions = WalletTransaction::with(['wallet.user', 'booking'])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.wallet.index', compact(
            'totalWallets', 'activeWallets', 'totalBalance', 'totalEarned', 'totalPaidOut',
            'eligiblePayments', 'eligibleAmount', 'thisMonthPayments', 'thisMonthPaymentsAmount',
            'recentTransactions'
        ));
    }

    /**
     * Farm owner wallets listing
     */
    public function wallets(Request $request)
    {
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
    }

    /**
     * Show specific wallet
     */
    public function show($walletId)
    {
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

        return view('admin.wallet.show', compact('wallet', 'statistics', 'bookingsWithEarnings'));
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

            if ($wallet->balance < $request->amount) {
                return redirect()->back()->with('error', 'Insufficient wallet balance for this payment.');
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

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to process payment: ' . $e->getMessage());
        }
    }

    /**
     * Pending payments dashboard
     */
    public function pendingPayments(Request $request)
    {
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
    }

    /**
     * All transactions
     */
    public function transactions(Request $request)
    {
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

        // Summary stats for filtered results
        $totalEarnings = (clone $query)->where('type', 'earning')->sum('amount');
        $totalPayments = abs((clone $query)->where('type', 'manual_payment')->sum('amount'));
        $totalCommissions = abs((clone $query)->where('type', 'commission')->sum('amount'));

        return view('admin.wallet.transactions', compact(
            'transactions', 'totalEarnings', 'totalPayments', 'totalCommissions'
        ));
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
    
        $wallet = FarmOwnerWallet::findOrFail($walletId);
        $oldRate = $wallet->platform_commission_rate;
        $newRate = $request->input('commission_rate');
    
        try {
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
     * Export wallet data
     */
    public function exportWallets()
    {
        $wallets = FarmOwnerWallet::with('user')->get();
        
        $csvData = "ID,User Name,User Email,Balance,Total Earned,Total Paid Out,Commission Rate,Status,Created At\n";
        
        foreach ($wallets as $wallet) {
            $csvData .= implode(',', [
                $wallet->id,
                '"' . $wallet->user->name . '"',
                $wallet->user->email,
                $wallet->balance,
                $wallet->total_earned,
                $wallet->total_paid_out ?? 0,
                $wallet->platform_commission_rate . '%',
                $wallet->is_active ? 'Active' : 'Inactive',
                $wallet->created_at->format('Y-m-d H:i:s'),
            ]) . "\n";
        }

        return response($csvData)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="farm_owner_wallets.csv"');
    }

    /**
     * Export payment data
     */
    public function exportPayments()
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