@extends('admin.layout')
@section('content')

<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.wallet.index') }}">Wallet Management</a></li>
                    <li class="breadcrumb-item active">Pending Payments</li>
                </ol>
            </div>
            <h4 class="page-title">Manual Payments Management</h4>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body text-center">
                <i class="mdi mdi-clock-outline text-warning" style="font-size: 24px;"></i>
                <h3 class="mt-2 mb-1 text-warning">{{ $pendingData['summary']['ready_for_payment'] }}</h3>
                <p class="text-muted mb-0">Ready for Payment</p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body text-center">
                <i class="mdi mdi-cash-usd text-success" style="font-size: 24px;"></i>
                <h3 class="mt-2 mb-1 text-success">AED {{ number_format($pendingData['summary']['total_ready_amount'], 2) }}</h3>
                <p class="text-muted mb-0">Ready Amount</p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body text-center">
                <i class="mdi mdi-account-multiple text-info" style="font-size: 24px;"></i>
                <h3 class="mt-2 mb-1 text-info">{{ $pendingData['summary']['eligible_but_not_ready'] }}</h3>
                <p class="text-muted mb-0">Not Ready Yet</p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body text-center">
                <i class="mdi mdi-bank-off text-danger" style="font-size: 24px;"></i>
                <h3 class="mt-2 mb-1 text-danger">{{ $pendingData['summary']['missing_bank_accounts'] }}</h3>
                <p class="text-muted mb-0">Missing Bank Info</p>
            </div>
        </div>
    </div>
</div>

<!-- Settings Info -->
<div class="row">
    <div class="col-12">
        <div class="alert alert-info">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="mdi mdi-information me-2"></i>
                    <strong>Payment Settings:</strong> 
                    Transfers every {{ $pendingData['settings']['transfer_frequency_days'] }} days | 
                    Minimum amount: AED {{ number_format($pendingData['settings']['minimum_transfer_amount'], 2) }}
                </div>
                <a href="{{ route('dashboard.wallet.payment-settings') }}" class="btn btn-sm btn-outline-primary">
                    <i class="mdi mdi-cog me-1"></i> Settings
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('dashboard.wallet.pending-payments') }}" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Filter</label>
                        <select name="filter" class="form-select">
                            <option value="">All Eligible</option>
                            <option value="ready" {{ request('filter') === 'ready' ? 'selected' : '' }}>Ready for Payment</option>
                            <option value="not_ready" {{ request('filter') === 'not_ready' ? 'selected' : '' }}>Not Ready Yet</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search by name or email..." 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Ready for Payment -->
@if($readyForPayment->count() > 0)
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0 text-success">
                        <i class="mdi mdi-check-circle me-2"></i>Ready for Payment 
                        <span class="badge bg-success">{{ $readyForPayment->count() }}</span>
                    </h5>
                    <div>
                        <span class="text-muted">Total: </span>
                        <span class="fw-bold text-success">AED {{ number_format($readyForPayment->sum('balance'), 2) }}</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Farm Owner</th>
                                <th>Balance</th>
                                <th>Bank Account</th>
                                <th>Last Payment</th>
                                <th>Days Since</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($readyForPayment as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-3">
                                            @php
                                                $nameParts = explode(' ', $item['user']['name']);
                                                $initials = collect($nameParts)->take(2)->map(fn($part) => strtoupper(substr($part, 0, 1)))->implode('');
                                            @endphp
                                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-primary text-white" 
                                                 style="width: 40px; height: 40px; font-weight: 500;">
                                                {{ $initials ?: 'U' }}
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $item['user']['name'] }}</h6>
                                            <small class="text-muted">{{ $item['user']['email'] }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold text-success">AED {{ number_format($item['balance'], 2) }}</span>
                                    <br><small class="text-muted">Total earned: AED {{ number_format($item['total_earned'], 2) }}</small>
                                </td>
                                <td>
                                    @if($item['has_bank_account'])
                                        <span class="badge bg-light text-dark">{{ $item['bank_account']['account_type_label'] ?? 'Unknown' }}</span>
                                        <br><small class="text-muted">{{ $item['bank_account']['primary_identifier'] ?? 'N/A' }}</small>
                                    @else
                                        <span class="badge bg-danger">No Bank Account</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item['last_payment_at'])
                                        <small>{{ \Carbon\Carbon::parse($item['last_payment_at'])->format('M d, Y') }}</small>
                                    @else
                                        <small class="text-muted">Never</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-warning">{{ $item['days_since_last_payment'] }} days</span>
                                </td>
                                <td>
                                    @if($item['has_bank_account'])
                                        <button class="btn btn-success btn-sm" 
                                                onclick="processPayment({{ $item['user']['id'] }}, '{{ $item['user']['name'] }}', {{ $item['balance'] }})">
                                            <i class="mdi mdi-bank-transfer me-1"></i>Process Payment
                                        </button>
                                    @else
                                        <button class="btn btn-outline-danger btn-sm" disabled>
                                            <i class="mdi mdi-alert me-1"></i>No Bank Account
                                        </button>
                                    @endif
                                    <a href="{{ route('dashboard.wallet.wallets.show', $item['wallet_id']) }}" 
                                       class="btn btn-outline-primary btn-sm ms-1">
                                        <i class="mdi mdi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Eligible but Not Ready -->
@if($eligibleButNotReady->count() > 0)
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0 text-info">
                        <i class="mdi mdi-clock-outline me-2"></i>Eligible but Not Ready 
                        <span class="badge bg-info">{{ $eligibleButNotReady->count() }}</span>
                    </h5>
                    <div>
                        <span class="text-muted">Total: </span>
                        <span class="fw-bold text-info">AED {{ number_format($eligibleButNotReady->sum('balance'), 2) }}</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Farm Owner</th>
                                <th>Balance</th>
                                <th>Bank Account</th>
                                <th>Last Payment</th>
                                <th>Days Until Ready</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($eligibleButNotReady as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-3">
                                            @php
                                                $nameParts = explode(' ', $item['user']['name']);
                                                $initials = collect($nameParts)->take(2)->map(fn($part) => strtoupper(substr($part, 0, 1)))->implode('');
                                            @endphp
                                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-secondary text-white" 
                                                 style="width: 40px; height: 40px; font-weight: 500;">
                                                {{ $initials ?: 'U' }}
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $item['user']['name'] }}</h6>
                                            <small class="text-muted">{{ $item['user']['email'] }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold">AED {{ number_format($item['balance'], 2) }}</span>
                                    <br><small class="text-muted">Total earned: AED {{ number_format($item['total_earned'], 2) }}</small>
                                </td>
                                <td>
                                    @if($item['has_bank_account'])
                                        <span class="badge bg-light text-dark">{{ $item['bank_account']['account_type_label'] ?? 'Unknown' }}</span>
                                        <br><small class="text-muted">{{ $item['bank_account']['primary_identifier'] ?? 'N/A' }}</small>
                                    @else
                                        <span class="badge bg-danger">No Bank Account</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item['last_payment_at'])
                                        <small>{{ \Carbon\Carbon::parse($item['last_payment_at'])->format('M d, Y') }}</small>
                                    @else
                                        <small class="text-muted">Never</small>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $daysUntilReady = max(0, $pendingData['settings']['transfer_frequency_days'] - $item['days_since_last_payment']);
                                    @endphp
                                    <span class="badge bg-secondary">{{ $daysUntilReady }} days</span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        @if($item['has_bank_account'])
                                            <button class="btn btn-outline-success" 
                                                    onclick="processPayment({{ $item['user']['id'] }}, '{{ $item['user']['name'] }}', {{ $item['balance'] }})"
                                                    title="Process Early Payment">
                                                <i class="mdi mdi-bank-transfer"></i>
                                            </button>
                                        @else
                                            <button class="btn btn-outline-danger" disabled title="No Bank Account">
                                                <i class="mdi mdi-alert"></i>
                                            </button>
                                        @endif
                                        <a href="{{ route('dashboard.wallet.wallets.show', $item['wallet_id']) }}" 
                                           class="btn btn-outline-primary" title="View Wallet">
                                            <i class="mdi mdi-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@if($readyForPayment->count() === 0 && $eligibleButNotReady->count() === 0)
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="mdi mdi-cash-check text-muted" style="font-size: 48px;"></i>
                <h5 class="mt-3">No Eligible Payments</h5>
                <p class="text-muted">No farm owners are currently eligible for payment.</p>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Process Payment Modal -->
<div class="modal fade" id="processPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Process Manual Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="processPaymentForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Farm Owner</label>
                        <input type="text" class="form-control" id="paymentFarmOwner" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Available Balance</label>
                        <input type="text" class="form-control" id="paymentBalance" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Amount (AED) *</label>
                        <input type="number" name="amount" class="form-control" required 
                               min="1" step="0.01" id="paymentAmount">
                        <small class="form-text text-muted">Maximum available balance</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="3" 
                                  placeholder="Add any notes about this payment..."></textarea>
                    </div>
                    <div class="alert alert-warning">
                        <i class="mdi mdi-alert me-2"></i>
                        <strong>Important:</strong> This will deduct the amount from the farm owner's wallet. 
                        Make sure you have processed the actual bank transfer before confirming.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="mdi mdi-bank-transfer me-1"></i>Process Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function processPayment(userId, farmOwnerName, balance) {
    const modal = new bootstrap.Modal(document.getElementById('processPaymentModal'));
    const form = document.getElementById('processPaymentForm');
    
    // Set form action
    form.action = `{{ route('dashboard.wallet.process-payment', '') }}/${userId}`;
    
    // Populate modal
    document.getElementById('paymentFarmOwner').value = farmOwnerName;
    document.getElementById('paymentBalance').value = `AED ${balance.toLocaleString()}`;
    document.getElementById('paymentAmount').value = balance;
    document.getElementById('paymentAmount').max = balance;
    
    modal.show();
}
</script>

@endsection