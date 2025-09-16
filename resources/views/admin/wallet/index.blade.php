@extends('admin.layout')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                {{-- Critical alerts in header --}}
                @if($criticalAlerts['pending_earnings_count'] > 0)
                <div class="d-flex gap-2 me-3">
                    @if($criticalAlerts['pending_earnings_count'] > 0)
                    <span class="badge bg-warning">
                        <i class="mdi mdi-clock-alert me-1"></i>
                        {{ $criticalAlerts['pending_earnings_count'] }} pending confirmations
                    </span>
                    @endif
                </div>
                @endif
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Wallet Management</li>
                </ol>
            </div>
            <h4 class="page-title">Wallet Management</h4>
        </div>
    </div>
</div>

{{-- Enhanced Statistics Cards --}}
<div class="row">
    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar-sm bg-primary-subtle rounded">
                            <div class="avatar-title bg-primary text-white rounded">
                                <i class="mdi mdi-wallet font-18"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0">Total Wallets</h6>
                        <h4 class="mt-1 mb-0">{{ number_format($totalWallets) }}</h4>
                        <small class="text-muted">{{ number_format($activeWallets) }} Active</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Split balance display for clarity --}}
    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar-sm bg-success-subtle rounded">
                            <div class="avatar-title bg-success text-white rounded">
                                <i class="mdi mdi-cash-check font-18"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0">Confirmed Balance</h6>
                        <h4 class="mt-1 mb-0 text-success">AED {{ number_format($totalConfirmedBalance, 2) }}</h4>
                        <small class="text-muted">Ready for payment</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- NEW: Pending Balance Card --}}
    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar-sm bg-warning-subtle rounded">
                            <div class="avatar-title bg-warning text-white rounded">
                                <i class="mdi mdi-clock-outline font-18"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0">Pending Balance</h6>
                        <h4 class="mt-1 mb-0 text-warning">AED {{ number_format($totalPendingBalance, 2) }}</h4>
                        <small class="text-muted">{{ $walletsWithPendingBalance }} wallets affected</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar-sm bg-info-subtle rounded">
                            <div class="avatar-title bg-info text-white rounded">
                                <i class="mdi mdi-bank-transfer-out font-18"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0">Ready for Payment</h6>
                        <h4 class="mt-1 mb-0 text-info">{{ $eligiblePayments }}</h4>
                        <small class="text-muted">AED {{ number_format($eligibleAmount, 2) }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Balance Explanation & Critical Actions --}}
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">
                    <i class="mdi mdi-information-outline me-2"></i>Balance System Overview
                </h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <h6 class="text-success">
                                <i class="mdi mdi-cash-check me-1"></i>Confirmed Balance: AED {{ number_format($totalConfirmedBalance, 2) }}
                            </h6>
                            <p class="text-muted small mb-2">
                                Money ready for immediate payment to farm owners. Generated from completed bookings.
                            </p>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="text-warning">
                                <i class="mdi mdi-clock-outline me-1"></i>Pending Balance: AED {{ number_format($totalPendingBalance, 2) }}
                            </h6>
                            <p class="text-muted small mb-2">
                                Earnings from active bookings that will be confirmed when bookings complete.
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <h6 class="text-info">
                                <i class="mdi mdi-calculator me-1"></i>Total Available: AED {{ number_format($totalAvailableBalance, 2) }}
                            </h6>
                            <p class="text-muted small mb-2">
                                Combined confirmed + pending balance across all farm owners.
                            </p>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="text-secondary">
                                <i class="mdi mdi-trending-up me-1"></i>Total Earned: AED {{ number_format($totalEarned, 2) }}
                            </h6>
                            <p class="text-muted small mb-2">
                                All-time earnings by farm owners through the platform.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">
                    <i class="mdi mdi-alert-outline me-2"></i>Attention Required
                </h5>
                
                @if($criticalAlerts['pending_earnings_count'] > 0)
                <div class="alert alert-warning py-2 mb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ $criticalAlerts['pending_earnings_count'] }}</strong> completed bookings
                            <br><small>Need earnings confirmation</small>
                        </div>
                        <form method="POST" action="{{ route('dashboard.wallet.confirm-earnings') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-warning" 
                                    onclick="return confirm('Confirm all pending earnings?')">
                                <i class="mdi mdi-check-circle"></i>
                            </button>
                        </form>
                    </div>
                </div>
                @endif
                
                @if($criticalAlerts['missing_bank_accounts'] > 0)
                <div class="alert alert-info py-2 mb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ $criticalAlerts['missing_bank_accounts'] }}</strong> eligible wallets
                            <br><small>Missing bank accounts</small>
                        </div>
                        <a href="{{ route('dashboard.wallet.wallets', ['filter' => 'no_bank']) }}" class="btn btn-sm btn-info">
                            <i class="mdi mdi-bank-off"></i>
                        </a>
                    </div>
                </div>
                @endif
                
                @if($criticalAlerts['pending_earnings_count'] == 0 && $criticalAlerts['missing_bank_accounts'] == 0)
                <div class="text-center py-3">
                    <i class="mdi mdi-check-all text-success" style="font-size: 32px;"></i>
                    <p class="text-success mt-2 mb-0">All systems normal</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Enhanced Quick Actions --}}
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Quick Actions</h5>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('dashboard.wallet.pending-payments') }}" class="btn btn-warning">
                        <i class="mdi mdi-bank-transfer-out me-1"></i>
                        Process Payments
                        @if($eligiblePayments > 0)
                        <span class="badge bg-light text-dark ms-1">{{ $eligiblePayments }}</span>
                        @endif
                    </a>
                    
                    {{-- Pending confirmations action --}}
                    @if($criticalAlerts['pending_earnings_count'] > 0)
                    <form method="POST" action="{{ route('dashboard.wallet.confirm-earnings') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-info" onclick="return confirm('Confirm all pending earnings?')">
                            <i class="mdi mdi-check-circle-outline me-1"></i>
                            Confirm Earnings
                            <span class="badge bg-light text-dark ms-1">{{ $criticalAlerts['pending_earnings_count'] }}</span>
                        </button>
                    </form>
                    @endif
                    
                    <a href="{{ route('dashboard.wallet.wallets') }}" class="btn btn-primary">
                        <i class="mdi mdi-account-cash me-1"></i>
                        View Wallets
                    </a>
                    
                    <a href="{{ route('dashboard.wallet.transactions') }}" class="btn btn-outline-primary">
                        <i class="mdi mdi-format-list-bulleted me-1"></i>
                        All Transactions
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Enhanced Recent Transactions with Correct Types --}}
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">Recent Transactions</h5>
                    <a href="{{ route('dashboard.wallet.transactions') }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                
                @if($recentTransactions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Farm Owner</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Balance Impact</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentTransactions as $transaction)
                            <tr>
                                <td>
                                    <span class="fw-bold">{{ $transaction->created_at->format('M d, Y') }}</span>
                                    <br><small class="text-muted">{{ $transaction->created_at->format('H:i') }}</small>
                                </td>
                                <td>
                                    <div>
                                        <span class="fw-bold">{{ $transaction->wallet->user->name }}</span>
                                        <br><small class="text-muted">{{ $transaction->wallet->user->email }}</small>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $config = $transactionConfig[$transaction->type] ?? [
                                            'color' => 'secondary', 
                                            'label' => ucfirst(str_replace('_', ' ', $transaction->type)),
                                            'icon' => 'help-circle'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $config['color'] }}">
                                        <i class="mdi mdi-{{ $config['icon'] }} me-1"></i>
                                        {{ $config['label'] }}
                                    </span>
                                    
                                    {{-- Visual indicator for balance type affected --}}
                                    @if(in_array($transaction->type, ['pending_earning', 'earning_confirmed']))
                                    <br><small class="text-muted">
                                        @if($transaction->type === 'pending_earning')
                                        <i class="mdi mdi-clock-outline text-warning"></i> → Pending
                                        @else
                                        <i class="mdi mdi-check-circle text-success"></i> → Confirmed
                                        @endif
                                    </small>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-bold {{ $transaction->amount >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $transaction->amount >= 0 ? '+' : '' }}AED {{ number_format($transaction->amount, 2) }}
                                    </span>
                                </td>
                                <td>
                                    @if($transaction->type === 'pending_earning')
                                    <small class="text-warning">
                                        <i class="mdi mdi-clock-outline me-1"></i>
                                        Pending: +AED {{ number_format($transaction->amount, 2) }}
                                    </small>
                                    @elseif($transaction->type === 'earning_confirmed')
                                    <small class="text-success">
                                        <i class="mdi mdi-check-circle me-1"></i>
                                        Confirmed: +AED {{ number_format($transaction->amount, 2) }}
                                    </small>
                                    @else
                                    <small class="text-muted">Balance: AED {{ number_format($transaction->balance_after, 2) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $transaction->status === 'completed' ? 'success' : 'warning' }}">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="mdi mdi-format-list-bulleted text-muted" style="font-size: 48px;"></i>
                    <p class="text-muted mt-2">No transactions yet</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Enhanced Summary Cards with Health Metrics --}}
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title">This Month Payments</h5>
                <h3 class="text-primary">{{ $thisMonthPayments }}</h3>
                <p class="text-muted">Total: AED {{ number_format($thisMonthPaymentsAmount, 2) }}</p>
                <div class="mt-2">
                    <small class="text-muted">
                        Average: AED {{ $thisMonthPayments > 0 ? number_format($thisMonthPaymentsAmount / $thisMonthPayments, 2) : '0.00' }}
                    </small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title">Total Paid Out</h5>
                <h3 class="text-success">AED {{ number_format($totalPaidOut, 2) }}</h3>
                <p class="text-muted">All time disbursements</p>
                <div class="mt-2">
                    <small class="text-muted">
                        Retention: {{ $totalEarned > 0 ? number_format((($totalEarned - $totalPaidOut) / $totalEarned) * 100, 1) : 0 }}%
                    </small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title">Platform Health</h5>
                @php
                    $healthScore = 100;
                    if($criticalAlerts['pending_earnings_count'] > 10) $healthScore -= 20;
                    if($criticalAlerts['missing_bank_accounts'] > 3) $healthScore -= 15;
                    $healthColor = $healthScore >= 90 ? 'success' : ($healthScore >= 70 ? 'warning' : 'danger');
                @endphp
                <h3 class="text-{{ $healthColor }}">{{ $healthScore }}%</h3>
                <p class="text-muted">System efficiency</p>
                <div class="mt-2">
                    <small class="text-{{ $healthColor }}">
                        @if($healthScore >= 90) Excellent
                        @elseif($healthScore >= 70) Good
                        @else Needs Attention
                        @endif
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection