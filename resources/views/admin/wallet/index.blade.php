@extends('admin.layout')
@section('content')

<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Farm Owner Fund Management</li>
                </ol>
            </div>
            <h4 class="page-title">Farm Owner Fund Management</h4>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar-sm rounded-circle bg-primary">
                            <div class="avatar-title rounded-circle bg-primary">
                                <i class="mdi mdi-wallet text-white font-22"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0 font-13">Total Wallets</h6>
                        <h4 class="mt-1 mb-0">{{ number_format($totalWallets) }}</h4>
                        <p class="mb-0 font-11 text-muted">
                            <span class="text-success">{{ number_format($activeWallets) }}</span> Active
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar-sm rounded-circle bg-info">
                            <div class="avatar-title rounded-circle bg-info">
                                <i class="mdi mdi-account-cash text-white font-22"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0 font-13">Farm Owner Funds</h6>
                        <h4 class="mt-1 mb-0">AED {{ number_format($totalBalance, 2) }}</h4>
                        <p class="mb-0 font-11 text-muted">
                            Held for farm owners
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar-sm rounded-circle bg-warning">
                            <div class="avatar-title rounded-circle bg-warning">
                                <i class="mdi mdi-bank-transfer-out text-white font-22"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0 font-13">Pending Disbursements</h6>
                        <h4 class="mt-1 mb-0">{{ $eligiblePayments }}</h4>
                        <p class="mb-0 font-11 text-muted">
                            <span class="text-warning">AED {{ number_format($eligibleAmount, 2) }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar-sm rounded-circle bg-success">
                            <div class="avatar-title rounded-circle bg-success">
                                <i class="mdi mdi-percent text-white font-22"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0 font-13">Platform Commission</h6>
                        <h4 class="mt-1 mb-0">AED {{ number_format($totalEarned * 0.15, 2) }}</h4>
                        <p class="mb-0 font-11 text-muted">
                            <span class="text-success">Our Revenue</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fund Management Summary -->
<div class="row">
    <div class="col-xl-8">
        <div class="card border-left-primary">
            <div class="card-body">
                <h5 class="card-title text-primary">Fund Management Overview</h5>
                <div class="row text-center">
                    <div class="col-md-4">
                        <h4 class="text-primary">AED {{ number_format($totalEarned, 2) }}</h4>
                        <p class="text-muted mb-0">Total Farm Owner Earnings</p>
                        <small class="text-muted">From all bookings</small>
                    </div>
                    <div class="col-md-4">
                        <h4 class="text-success">AED {{ number_format($totalPaidOut, 2) }}</h4>
                        <p class="text-muted mb-0">Already Disbursed</p>
                        <small class="text-muted">Paid to farm owners</small>
                    </div>
                    <div class="col-md-4">
                        <h4 class="text-info">AED {{ number_format($totalBalance, 2) }}</h4>
                        <p class="text-muted mb-0">Currently Held</p>
                        <small class="text-muted">Awaiting disbursement</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">This Month Activity</h5>
                <div class="row">
                    <div class="col-6">
                        <div class="text-center">
                            <h3 class="text-success">{{ $thisMonthPayments }}</h3>
                            <p class="text-muted mb-0">Disbursements</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <h3 class="text-primary">AED {{ number_format($thisMonthPaymentsAmount, 2) }}</h3>
                            <p class="text-muted mb-0">Amount Disbursed</p>
                        </div>
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
                        Process Disbursements
                        @if($eligiblePayments > 0)
                            <span class="badge bg-danger ms-1">{{ $eligiblePayments }}</span>
                        @endif
                    </a>
                    <a href="{{ route('dashboard.wallet.wallets') }}" class="btn btn-primary">
                        <i class="mdi mdi-account-cash me-1"></i> View Farm Owner Wallets
                    </a>
                    <a href="{{ route('dashboard.wallet.payment-settings') }}" class="btn btn-secondary">
                        <i class="mdi mdi-cog me-1"></i> Disbursement Settings
                    </a>
                    <a href="{{ route('dashboard.wallet.transactions') }}" class="btn btn-outline-primary">
                        <i class="mdi mdi-format-list-bulleted me-1"></i> All Transactions
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
                    <h5 class="card-title mb-0">Recent Fund Transactions</h5>
                    <a href="{{ route('dashboard.wallet.transactions') }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                
                @if($recentTransactions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Farm Owner</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Reference</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentTransactions as $transaction)
                            <tr>
                                <td>
                                    <small class="text-muted">{{ $transaction->created_at->format('M d, Y H:i') }}</small>
                                </td>
                                <td>
                                    <strong>{{ $transaction->wallet->user->name }}</strong><br>
                                    <small class="text-muted">{{ $transaction->wallet->user->email }}</small>
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
                                        
                                        $typeLabels = [
                                            'earning' => 'Farm Earning',
                                            'manual_payment' => 'Disbursement',
                                            'commission' => 'Platform Commission',
                                            'refund' => 'Refund Deduction',
                                            'adjustment' => 'Admin Adjustment'
                                        ];
                                        $typeLabel = $typeLabels[$transaction->type] ?? ucfirst(str_replace('_', ' ', $transaction->type));
                                    @endphp
                                    <span class="badge badge-soft-{{ $typeColor }}">
                                        {{ $typeLabel }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold {{ $transaction->amount >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $transaction->amount >= 0 ? '+' : '' }}AED {{ number_format($transaction->amount, 2) }}
                                    </span>
                                    @if($transaction->type === 'earning')
                                    <br><small class="text-muted">Farm Owner Earned</small>
                                    @elseif($transaction->type === 'manual_payment')
                                    <br><small class="text-muted">Disbursed to Farm Owner</small>
                                    @elseif($transaction->type === 'commission')
                                    <br><small class="text-success">Platform Revenue</small>
                                    @endif
                                </td>
                                <td>
                                    <code class="text-muted">{{ $transaction->reference }}</code>
                                    @if($transaction->booking)
                                    <br><small class="text-muted">{{ $transaction->booking->booking_reference }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-soft-{{ $transaction->status === 'completed' ? 'success' : 'warning' }}">
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

<!-- Monthly Statistics Chart -->
@if($monthlyStats->count() > 0)
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Monthly Fund Flow</h5>
                <p class="text-muted">Track farm owner earnings, disbursements, and platform commission over time</p>
                <canvas id="monthlyStatsChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('monthlyStatsChart').getContext('2d');
    
    const monthlyData = @json($monthlyStats);
    const labels = monthlyData.map(item => `${item.year}-${String(item.month).padStart(2, '0')}`);
    const earnings = monthlyData.map(item => item.earnings);
    const payments = monthlyData.map(item => item.payments);
    const commissions = monthlyData.map(item => item.commissions);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Farm Owner Earnings',
                data: earnings,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.1
            }, {
                label: 'Disbursements to Farm Owners', 
                data: payments,
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                tension: 0.1
            }, {
                label: 'Platform Commission Revenue',
                data: commissions,
                borderColor: 'rgb(255, 205, 86)',
                backgroundColor: 'rgba(255, 205, 86, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'AED ' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': AED ' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            }
        }
    });
});
</script>
@endif

@endsection