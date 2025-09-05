@extends('admin.layout')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Wallet Management</li>
                </ol>
            </div>
            <h4 class="page-title">Wallet Management</h4>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
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

    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar-sm bg-success-subtle rounded">
                            <div class="avatar-title bg-success text-white rounded">
                                <i class="mdi mdi-cash-multiple font-18"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0">Total Balance</h6>
                        <h4 class="mt-1 mb-0">AED {{ number_format($totalBalance, 2) }}</h4>
                        <small class="text-muted">Held for farm owners</small>
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
                        <div class="avatar-sm bg-warning-subtle rounded">
                            <div class="avatar-title bg-warning text-white rounded">
                                <i class="mdi mdi-bank-transfer-out font-18"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0">Pending Payments</h6>
                        <h4 class="mt-1 mb-0">{{ $eligiblePayments }}</h4>
                        <small class="text-muted">AED {{ number_format($eligibleAmount, 2) }}</small>
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
                                <i class="mdi mdi-trending-up font-18"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0">Total Earned</h6>
                        <h4 class="mt-1 mb-0">AED {{ number_format($totalEarned, 2) }}</h4>
                        <small class="text-muted">By farm owners</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
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
                    <a href="{{ route('dashboard.wallet.wallets') }}" class="btn btn-primary">
                        <i class="mdi mdi-account-cash me-1"></i>
                        View Wallets
                    </a>
                    <a href="{{ route('dashboard.settings.index') }}" class="btn btn-secondary">
                        <i class="mdi mdi-cog me-1"></i>
                        Settings
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

<!-- Recent Transactions -->
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
                                        $typeColors = [
                                            'earning' => 'success',
                                            'manual_payment' => 'warning',
                                            'commission' => 'info',
                                            'refund' => 'danger',
                                            'adjustment' => 'secondary'
                                        ];
                                        $typeColor = $typeColors[$transaction->type] ?? 'light';
                                    @endphp
                                    <span class="badge bg-{{ $typeColor }}">
                                        {{ ucfirst(str_replace('_', ' ', $transaction->type)) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold {{ $transaction->amount >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $transaction->amount >= 0 ? '+' : '' }}AED {{ number_format($transaction->amount, 2) }}
                                    </span>
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

<!-- This Month Summary -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title">This Month Payments</h5>
                <h3 class="text-primary">{{ $thisMonthPayments }}</h3>
                <p class="text-muted">Total: AED {{ number_format($thisMonthPaymentsAmount, 2) }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title">Total Paid Out</h5>
                <h3 class="text-success">AED {{ number_format($totalPaidOut, 2) }}</h3>
                <p class="text-muted">All time disbursements</p>
            </div>
        </div>
    </div>
</div>
@endsection