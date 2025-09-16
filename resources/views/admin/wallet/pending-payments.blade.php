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
            <h4 class="page-title">Pending Payments</h4>
        </div>
    </div>
</div>

<!-- Enhanced Summary Cards -->
<div class="row">
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 bg-success-subtle">
            <div class="card-body text-center">
                <i class="mdi mdi-cash-check text-success" style="font-size: 24px;"></i>
                <h3 class="mt-2 mb-1 text-success">{{ $pendingData['summary']['ready_for_payment'] }}</h3>
                <p class="text-muted mb-0">Ready for Payment</p>
                <small class="text-success">
                    <i class="mdi mdi-check-circle me-1"></i>All conditions met
                </small>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 bg-info-subtle">
            <div class="card-body text-center">
                <i class="mdi mdi-cash-usd text-info" style="font-size: 24px;"></i>
                <h3 class="mt-2 mb-1 text-info">AED {{ number_format($pendingData['summary']['total_ready_amount'], 2) }}</h3>
                <p class="text-muted mb-0">Ready Amount</p>
                <small class="text-info">Confirmed balance only</small>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 bg-warning-subtle">
            <div class="card-body text-center">
                <i class="mdi mdi-clock-outline text-warning" style="font-size: 24px;"></i>
                <h3 class="mt-2 mb-1 text-warning">{{ $pendingData['summary']['eligible_but_not_ready'] }}</h3>
                <p class="text-muted mb-0">Not Ready Yet</p>
                <small class="text-warning">Waiting for frequency</small>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 bg-danger-subtle">
            <div class="card-body text-center">
                <i class="mdi mdi-bank-off text-danger" style="font-size: 24px;"></i>
                <h3 class="mt-2 mb-1 text-danger">{{ $pendingData['summary']['missing_bank_accounts'] }}</h3>
                <p class="text-muted mb-0">Missing Bank Info</p>
                <small class="text-danger">Action required</small>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Settings Info -->
<div class="row">
    <div class="col-12">
        <div class="alert alert-info">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="mdi mdi-information me-2"></i>
                    <strong>Payment Settings:</strong> 
                    Transfers every <span class="badge bg-primary">{{ $pendingData['settings']['transfer_frequency_days'] }} days</span> | 
                    Minimum amount: <span class="badge bg-success">AED {{ number_format($pendingData['settings']['minimum_transfer_amount'], 2) }}</span> |
                    Only <span class="badge bg-warning">confirmed balance</span> can be paid out
                </div>
                <a href="{{ route('dashboard.settings.index') }}" class="btn btn-sm btn-outline-primary">
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
                <form method="GET" class="row g-3">
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
                               placeholder="Name or email..." value="{{ request('search') }}">
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
                        <span class="text-muted">Total Confirmed: </span>
                        <span class="fw-bold text-success">AED {{ number_format($readyForPayment->sum('balance'), 2) }}</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Farm Owner</th>
                                <th>Balance Details</th>
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
                                                $initials = collect(explode(' ', $item['user']['name']))
                                                    ->take(2)->map(fn($part) => strtoupper(substr($part, 0, 1)))->implode('');
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
                                    {{-- Confirmed Balance --}}
                                    <div class="fw-bold text-success">
                                        <i class="mdi mdi-cash-check me-1"></i>
                                        AED {{ number_format($item['balance'], 2) }}
                                    </div>
                                    <small class="text-success">Confirmed & ready</small>
                                    
                                    {{-- Pending Balance (if exists) --}}
                                    @if(isset($item['pending_balance']) && $item['pending_balance'] > 0)
                                    <br><small class="text-warning">
                                        <i class="mdi mdi-clock-outline me-1"></i>
                                        +AED {{ number_format($item['pending_balance'], 2) }} pending
                                    </small>
                                    @endif
                                    
                                    {{-- Total Earned Reference --}}
                                    <br><small class="text-muted">
                                        Total earned: AED {{ number_format($item['total_earned'], 2) }}
                                    </small>
                                </td>
                                <td>
                                    @if($item['has_bank_account'])
                                        <span class="badge bg-success">
                                            <i class="mdi mdi-bank me-1"></i>
                                            {{ $item['bank_account']['account_type_label'] ?? 'Bank Account' }}
                                        </span>
                                        <br><small class="text-success mt-1">Verified & ready</small>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="mdi mdi-bank-off me-1"></i>
                                            No Bank Account
                                        </span>
                                        <br><small class="text-danger mt-1">Setup required</small>
                                    @endif
                                </td>
                                <td>
                                    @if($item['last_payment_at'])
                                        <div class="fw-bold">{{ \Carbon\Carbon::parse($item['last_payment_at'])->format('M d, Y') }}</div>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($item['last_payment_at'])->format('H:i') }}</small>
                                    @else
                                        <span class="text-muted fw-bold">Never</span>
                                        <br><small class="text-info">First payment</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-warning">{{ $item['days_since_last_payment'] }} days</span>
                                    <br><small class="text-success">Ready to process</small>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        @if($item['has_bank_account'])
                                            <button class="btn btn-success btn-sm process-payment-btn"
                                                    data-user-id="{{ $item['user']['id'] }}" 
                                                    data-user-name="{{ $item['user']['name'] }}" 
                                                    data-balance="{{ $item['balance'] }}"
                                                    title="Process Payment">
                                                <i class="mdi mdi-bank-transfer me-1"></i>Process
                                            </button>
                                        @else
                                            <button class="btn btn-outline-danger btn-sm" disabled title="No bank account">
                                                <i class="mdi mdi-alert me-1"></i>No Bank
                                            </button>
                                        @endif
                                        <a href="{{ route('dashboard.wallet.wallets.show', $item['wallet_id']) }}" 
                                           class="btn btn-outline-primary btn-sm" title="View Wallet">
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

<!-- Eligible but Not Ready -->
@if($eligibleButNotReady->count() > 0)
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0 text-warning">
                        <i class="mdi mdi-clock-outline me-2"></i>Not Ready Yet
                        <span class="badge bg-warning">{{ $eligibleButNotReady->count() }}</span>
                    </h5>
                    <div>
                        <span class="text-muted">Total Confirmed: </span>
                        <span class="fw-bold text-warning">AED {{ number_format($eligibleButNotReady->sum('balance'), 2) }}</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Farm Owner</th>
                                <th>Balance Details</th>
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
                                                $initials = collect(explode(' ', $item['user']['name']))
                                                    ->take(2)->map(fn($part) => strtoupper(substr($part, 0, 1)))->implode('');
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
                                    {{-- Confirmed Balance --}}
                                    <div class="fw-bold text-info">
                                        <i class="mdi mdi-cash-check me-1"></i>
                                        AED {{ number_format($item['balance'], 2) }}
                                    </div>
                                    <small class="text-warning">Waiting for frequency</small>
                                    
                                    {{-- Pending Balance (if exists) --}}
                                    @if(isset($item['pending_balance']) && $item['pending_balance'] > 0)
                                    <br><small class="text-warning">
                                        <i class="mdi mdi-clock-outline me-1"></i>
                                        +AED {{ number_format($item['pending_balance'], 2) }} pending
                                    </small>
                                    @endif
                                    
                                    {{-- Total Earned Reference --}}
                                    <br><small class="text-muted">
                                        Total earned: AED {{ number_format($item['total_earned'], 2) }}
                                    </small>
                                </td>
                                <td>
                                    @if($item['has_bank_account'])
                                        <span class="badge bg-success">
                                            <i class="mdi mdi-bank me-1"></i>
                                            {{ $item['bank_account']['account_type_label'] ?? 'Bank Account' }}
                                        </span>
                                        <br><small class="text-success mt-1">Verified & ready</small>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="mdi mdi-bank-off me-1"></i>
                                            No Bank Account
                                        </span>
                                        <br><small class="text-danger mt-1">Setup required</small>
                                    @endif
                                </td>
                                <td>
                                    @if($item['last_payment_at'])
                                        <div class="fw-bold">{{ \Carbon\Carbon::parse($item['last_payment_at'])->format('M d, Y') }}</div>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($item['last_payment_at'])->format('H:i') }}</small>
                                    @else
                                        <span class="text-muted fw-bold">Never</span>
                                        <br><small class="text-info">First payment</small>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $daysUntilReady = max(0, $pendingData['settings']['transfer_frequency_days'] - $item['days_since_last_payment']);
                                    @endphp
                                    <span class="badge bg-secondary">{{ $daysUntilReady }} days</span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        @if($item['has_bank_account'])
                                            <button class="btn btn-outline-success btn-sm process-payment-btn" 
                                                    data-user-id="{{ $item['user']['id'] }}"
                                                    data-user-name="{{ $item['user']['name'] }}"
                                                    data-balance="{{ $item['balance'] }}"
                                                    title="Process Early Payment">
                                                <i class="mdi mdi-bank-transfer"></i>
                                            </button>
                                        @else
                                            <button class="btn btn-outline-danger btn-sm" disabled title="No bank account">
                                                <i class="mdi mdi-alert"></i>
                                            </button>
                                        @endif
                                        <a href="{{ route('dashboard.wallet.wallets.show', $item['wallet_id']) }}" 
                                           class="btn btn-outline-primary btn-sm" title="View Wallet">
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
                <div class="mt-3">
                    <small class="text-muted">
                        Farm owners need at least AED {{ number_format($pendingData['settings']['minimum_transfer_amount'], 2) }} 
                        in confirmed balance and a verified bank account to be eligible.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Enhanced Process Payment Modal -->
<div class="modal fade" id="processPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="processPaymentForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Process Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="mdi mdi-information me-2"></i>
                        Processing payment for <strong id="paymentUserName"></strong>
                        <br>Available confirmed balance: <strong id="paymentBalance"></strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount (AED) *</label>
                        <input type="number" name="amount" class="form-control" id="paymentAmount" 
                               step="0.01" min="1" required>
                        <div class="form-text">Only confirmed balance can be paid out (pending balance is not available)</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3" 
                                  placeholder="Optional notes for this payment..."></textarea>
                    </div>
                    <div class="alert alert-warning">
                        <i class="mdi mdi-alert me-2"></i>
                        <strong>Important:</strong> Complete the actual bank transfer before processing this payment in the system.
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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('processPaymentModal');
    const form = document.getElementById('processPaymentForm');
    const buttons = document.querySelectorAll('.process-payment-btn');
    
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.dataset.userId;
            const userName = this.dataset.userName;
            const balance = parseFloat(this.dataset.balance);
            
            // Set form action
            form.action = `{{ route('dashboard.wallet.process-payment', '') }}/${userId}`;
            
            // Populate modal
            document.getElementById('paymentUserName').textContent = userName;
            document.getElementById('paymentBalance').textContent = `AED ${balance.toLocaleString()}`;
            document.getElementById('paymentAmount').value = balance;
            document.getElementById('paymentAmount').max = balance;
            
            // Show modal
            new bootstrap.Modal(modal).show();
        });
    });
    
    // Form validation
    form.addEventListener('submit', function(e) {
        const amount = parseFloat(document.getElementById('paymentAmount').value);
        const maxAmount = parseFloat(document.getElementById('paymentAmount').max);
        
        if (amount > maxAmount) {
            e.preventDefault();
            alert('Amount cannot exceed available confirmed balance');
            return false;
        }
        
        if (amount < 1) {
            e.preventDefault();
            alert('Amount must be at least AED 1.00');
            return false;
        }
        
        return confirm('Are you sure you want to process this payment?');
    });
});
</script>
@endpush