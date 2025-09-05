@extends('admin.layout')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.wallet.index') }}">Wallet Management</a></li>
                    <li class="breadcrumb-item active">Farm Owner Wallets</li>
                </ol>
            </div>
            <h4 class="page-title">Farm Owner Wallets</h4>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="row">
    <div class="col-md-3">
        <div class="card border-0 bg-primary-subtle">
            <div class="card-body text-center">
                <i class="mdi mdi-wallet text-primary" style="font-size: 24px;"></i>
                <h4 class="text-primary mt-2 mb-1">{{ $wallets->total() }}</h4>
                <p class="text-muted mb-0 small">Total Wallets</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-success-subtle">
            <div class="card-body text-center">
                <i class="mdi mdi-cash-multiple text-success" style="font-size: 24px;"></i>
                <h4 class="text-success mt-2 mb-1">AED {{ number_format($wallets->sum('balance'), 2) }}</h4>
                <p class="text-muted mb-0 small">Total Balance</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-info-subtle">
            <div class="card-body text-center">
                <i class="mdi mdi-trending-up text-info" style="font-size: 24px;"></i>
                <h4 class="text-info mt-2 mb-1">AED {{ number_format($wallets->sum('total_earned'), 2) }}</h4>
                <p class="text-muted mb-0 small">Total Earned</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-warning-subtle">
            <div class="card-body text-center">
                <i class="mdi mdi-bank-transfer-out text-warning" style="font-size: 24px;"></i>
                <h4 class="text-warning mt-2 mb-1">AED {{ number_format($wallets->sum('total_paid_out'), 2) }}</h4>
                <p class="text-muted mb-0 small">Total Paid Out</p>
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
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" placeholder="Name or email...">
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="min_balance" class="form-label">Min Balance</label>
                        <input type="number" class="form-control" id="min_balance" name="min_balance" 
                               value="{{ request('min_balance') }}" step="0.01" min="0" placeholder="0.00">
                    </div>
                    <div class="col-md-2">
                        <label for="sort" class="form-label">Sort By</label>
                        <select class="form-select" id="sort" name="sort">
                            <option value="balance" {{ request('sort', 'balance') === 'balance' ? 'selected' : '' }}>Balance</option>
                            <option value="total_earned" {{ request('sort') === 'total_earned' ? 'selected' : '' }}>Total Earned</option>
                            <option value="total_paid_out" {{ request('sort') === 'total_paid_out' ? 'selected' : '' }}>Total Paid Out</option>
                            <option value="user_name" {{ request('sort') === 'user_name' ? 'selected' : '' }}>User Name</option>
                            <option value="created_at" {{ request('sort') === 'created_at' ? 'selected' : '' }}>Created Date</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="direction" class="form-label">Direction</label>
                        <select class="form-select" id="direction" name="direction">
                            <option value="desc" {{ request('direction', 'desc') === 'desc' ? 'selected' : '' }}>High to Low</option>
                            <option value="asc" {{ request('direction') === 'asc' ? 'selected' : '' }}>Low to High</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="mdi mdi-filter-variant"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Wallets Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">
                        Farm Owner Wallets 
                        <span class="badge bg-info">{{ $wallets->total() }}</span>
                    </h5>
                    <div class="d-flex gap-2">
                        @if(request()->hasAny(['search', 'status', 'min_balance', 'sort', 'direction']))
                        <a href="{{ route('dashboard.wallet.wallets') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="mdi mdi-refresh me-1"></i> Clear Filters
                        </a>
                        @endif
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="mdi mdi-export me-1"></i> Export
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('dashboard.wallet.export.wallets') }}">Export Wallets</a></li>
                                <li><a class="dropdown-item" href="{{ route('dashboard.wallet.export.payments') }}">Export Payments</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                @if($wallets->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Farm Owner</th>
                                <th>Balance</th>
                                <th>Total Earned</th>
                                <th>Total Paid Out</th>
                                <th>Commission Rate</th>
                                <th>Bank Account</th>
                                <th>Status</th>
                                <th>Last Activity</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($wallets as $wallet)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-3">
                                            @php
                                                $initials = collect(explode(' ', $wallet->user->name))
                                                    ->take(2)->map(fn($part) => strtoupper(substr($part, 0, 1)))->implode('');
                                            @endphp
                                            <div class="avatar-sm bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center">
                                                <span class="text-primary fw-bold">{{ $initials ?: 'U' }}</span>
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $wallet->user->name }}</h6>
                                            <small class="text-muted">{{ $wallet->user->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold text-primary">AED {{ number_format($wallet->balance, 2) }}</span>
                                    @if($wallet->balance >= 50)
                                    <br><small class="text-success">
                                        <i class="mdi mdi-check-circle me-1"></i>Eligible for payment
                                    </small>
                                    @else
                                    <br><small class="text-muted">Below minimum</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-success fw-bold">AED {{ number_format($wallet->total_earned, 2) }}</span>
                                </td>
                                <td>
                                    <span class="text-info fw-bold">AED {{ number_format($wallet->total_paid_out ?? 0, 2) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $wallet->platform_commission_rate }}%</span>
                                </td>
                                <td>
                                    @if($wallet->user->farmOwnerBankAccount)
                                        <span class="badge bg-success">
                                            <i class="mdi mdi-bank me-1"></i>{{ $wallet->user->farmOwnerBankAccount->getAccountTypeLabel() }}
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="mdi mdi-bank-off me-1"></i>Not Setup
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($wallet->is_active)
                                    <span class="badge bg-success">Active</span>
                                    @else
                                    <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    @if($wallet->updated_at->diffInDays() < 7)
                                    <small class="text-success">{{ $wallet->updated_at->diffForHumans() }}</small>
                                    @elseif($wallet->updated_at->diffInDays() < 30)
                                    <small class="text-warning">{{ $wallet->updated_at->diffForHumans() }}</small>
                                    @else
                                    <small class="text-muted">{{ $wallet->updated_at->diffForHumans() }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('dashboard.wallet.wallets.show', $wallet->id) }}" 
                                           class="btn btn-sm btn-outline-primary" title="View Details">
                                            <i class="mdi mdi-eye"></i>
                                        </a>
                                        @if($wallet->balance >= 50 && $wallet->user->farmOwnerBankAccount)
                                        <button type="button" class="btn btn-sm btn-warning process-payment-btn"
                                                data-user-id="{{ $wallet->user_id }}" 
                                                data-user-name="{{ $wallet->user->name }}"
                                                data-balance="{{ $wallet->balance }}"
                                                title="Process Payment">
                                            <i class="mdi mdi-bank-transfer-out"></i>
                                        </button>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-outline-secondary adjustment-btn"
                                                data-wallet-id="{{ $wallet->id }}" 
                                                data-user-name="{{ $wallet->user->name }}"
                                                data-balance="{{ $wallet->balance }}"
                                                title="Add Adjustment">
                                            <i class="mdi mdi-plus-minus-variant"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-info commission-btn"
                                                data-wallet-id="{{ $wallet->id }}" 
                                                data-user-name="{{ $wallet->user->name }}"
                                                data-current-rate="{{ $wallet->platform_commission_rate }}"
                                                title="Update Commission">
                                            <i class="mdi mdi-percent"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        Showing {{ $wallets->firstItem() ?? 0 }} to {{ $wallets->lastItem() ?? 0 }} 
                        of {{ $wallets->total() }} wallets
                    </div>
                    {{ $wallets->appends(request()->query())->links() }}
                </div>
                @else
                <div class="text-center py-5">
                    <i class="mdi mdi-wallet text-muted" style="font-size: 48px;"></i>
                    <p class="text-muted mt-2">No wallets found</p>
                    @if(request()->hasAny(['search', 'status', 'min_balance']))
                    <a href="{{ route('dashboard.wallet.wallets') }}" class="btn btn-sm btn-outline-primary mt-2">
                        Clear filters and show all
                    </a>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Process Payment Modal -->
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
                        <br>Available balance: <strong id="paymentBalance"></strong>
                    </div>
                    <div class="mb-3">
                        <label for="paymentAmount" class="form-label">Amount (AED) *</label>
                        <input type="number" class="form-control" id="paymentAmount" name="amount" 
                               step="0.01" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="paymentNotes" class="form-label">Notes</label>
                        <textarea class="form-control" id="paymentNotes" name="notes" rows="3" 
                                  placeholder="Optional notes for this payment..."></textarea>
                    </div>
                    <div class="alert alert-warning">
                        <i class="mdi mdi-alert-triangle me-2"></i>
                        Ensure bank transfer is completed before processing this payment.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="mdi mdi-bank-transfer-out me-1"></i> Process Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Adjustment Modal -->
<div class="modal fade" id="adjustmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="adjustmentForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Wallet Adjustment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="mdi mdi-alert-triangle me-2"></i>
                        Making adjustment for <strong id="adjustmentUserName"></strong>
                        <br>Current balance: <strong id="adjustmentBalance"></strong>
                    </div>
                    <div class="mb-3">
                        <label for="adjustmentType" class="form-label">Type *</label>
                        <select class="form-select" id="adjustmentType" name="type" required>
                            <option value="adjustment">Adjustment</option>
                            <option value="bonus">Bonus</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="adjustmentAmount" class="form-label">Amount (AED) *</label>
                        <input type="number" class="form-control" id="adjustmentAmount" name="amount" 
                               step="0.01" required placeholder="Use negative values to deduct">
                        <div class="form-text">Use positive values to add funds, negative values to deduct funds</div>
                    </div>
                    <div class="mb-3">
                        <label for="adjustmentDescription" class="form-label">Description *</label>
                        <textarea class="form-control" id="adjustmentDescription" name="description" rows="3" 
                                  required placeholder="Reason for this adjustment..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-plus-minus-variant me-1"></i> Apply Adjustment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Commission Rate Modal -->
<div class="modal fade" id="commissionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="commissionForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Update Commission Rate</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="mdi mdi-information me-2"></i>
                        Updating commission rate for <strong id="commissionUserName"></strong>
                        <br>Current rate: <strong id="commissionCurrentRate"></strong>
                    </div>
                    
                    @php
                        $minRate = \App\Models\PlatformSetting::getMinimumCommissionRate();
                        $maxRate = \App\Models\PlatformSetting::getMaximumCommissionRate();
                        $defaultRate = \App\Models\PlatformSetting::getDefaultCommissionRate();
                    @endphp
                    
                    <div class="mb-3">
                        <label for="commissionRate" class="form-label">Commission Rate (%) *</label>
                        <input type="number" class="form-control" id="commissionRate" name="commission_rate" 
                               step="0.01" min="{{ $minRate }}" max="{{ $maxRate }}" required>
                        <div class="form-text" id="commissionRateHelp">
                            Rate must be between {{ $minRate }}% and {{ $maxRate }}%
                            <br><small class="text-muted">Platform default: {{ $defaultRate }}%</small>
                        </div>
                    </div>
                    
                    <!-- Quick Rate Buttons -->
                    @if($minRate != $maxRate)
                    <div class="mb-3">
                        <label class="form-label">Quick Set:</label>
                        <div class="d-flex gap-2 flex-wrap">
                            @if($minRate > 0)
                            <button type="button" class="btn btn-sm btn-outline-secondary quick-rate-btn" data-rate="{{ $minRate }}">
                                {{ $minRate }}% (Min)
                            </button>
                            @endif
                            
                            @if($defaultRate != $minRate && $defaultRate != $maxRate)
                            <button type="button" class="btn btn-sm btn-outline-primary quick-rate-btn" data-rate="{{ $defaultRate }}">
                                {{ $defaultRate }}% (Default)
                            </button>
                            @endif
                            
                            @if($maxRate < 100)
                            <button type="button" class="btn btn-sm btn-outline-secondary quick-rate-btn" data-rate="{{ $maxRate }}">
                                {{ $maxRate }}% (Max)
                            </button>
                            @endif
                        </div>
                    </div>
                    @endif
                    
                    <div class="mb-3">
                        <label for="commissionReason" class="form-label">Reason for Change</label>
                        <textarea class="form-control" id="commissionReason" name="reason" rows="3" 
                                  placeholder="Optional reason for this change..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-percent me-1"></i> Update Commission
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
    // Process Payment Modal Handler
    const processPaymentModal = document.getElementById('processPaymentModal');
    const processPaymentForm = document.getElementById('processPaymentForm');
    const processPaymentButtons = document.querySelectorAll('.process-payment-btn');
    
    processPaymentButtons.forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.dataset.userId;
            const userName = this.dataset.userName;
            const balance = parseFloat(this.dataset.balance);
            
            // Set form action
            processPaymentForm.action = `{{ route('dashboard.wallet.process-payment', '') }}/${userId}`;
            
            // Populate modal
            document.getElementById('paymentUserName').textContent = userName;
            document.getElementById('paymentBalance').textContent = `AED ${balance.toLocaleString()}`;
            document.getElementById('paymentAmount').value = balance;
            document.getElementById('paymentAmount').max = balance;
            
            // Show modal
            new bootstrap.Modal(processPaymentModal).show();
        });
    });
    
    // Process Payment Form validation
    processPaymentForm.addEventListener('submit', function(e) {
        const amount = parseFloat(document.getElementById('paymentAmount').value);
        const maxAmount = parseFloat(document.getElementById('paymentAmount').max);
        
        if (amount > maxAmount) {
            e.preventDefault();
            alert('Amount cannot exceed available balance');
            return false;
        }
        
        return confirm('Are you sure you want to process this payment?');
    });

    // Adjustment Modal Handler
    const adjustmentModal = document.getElementById('adjustmentModal');
    const adjustmentForm = document.getElementById('adjustmentForm');
    const adjustmentButtons = document.querySelectorAll('.adjustment-btn');
    
    adjustmentButtons.forEach(button => {
        button.addEventListener('click', function() {
            const walletId = this.dataset.walletId;
            const userName = this.dataset.userName;
            const balance = parseFloat(this.dataset.balance);
            
            // Set form action
            adjustmentForm.action = `{{ route('dashboard.wallet.wallets.adjustment', '') }}/${walletId}`;
            
            // Populate modal
            document.getElementById('adjustmentUserName').textContent = userName;
            document.getElementById('adjustmentBalance').textContent = `AED ${balance.toLocaleString()}`;
            
            // Show modal
            new bootstrap.Modal(adjustmentModal).show();
        });
    });
    
    // Adjustment Form validation
    adjustmentForm.addEventListener('submit', function(e) {
        const amount = parseFloat(document.getElementById('adjustmentAmount').value);
        
        if (amount === 0) {
            e.preventDefault();
            alert('Amount cannot be zero');
            return false;
        }
        
        const action = amount > 0 ? 'add funds to' : 'deduct funds from';
        return confirm(`Are you sure you want to ${action} this wallet?`);
    });

    // Commission Modal Handler
    const commissionModal = document.getElementById('commissionModal');
    const commissionForm = document.getElementById('commissionForm');
    const commissionButtons = document.querySelectorAll('.commission-btn');
    
    commissionButtons.forEach(button => {
        button.addEventListener('click', function() {
            const walletId = this.dataset.walletId;
            const userName = this.dataset.userName;
            const currentRate = parseFloat(this.dataset.currentRate);
            
            // Set form action
            commissionForm.action = `{{ route('dashboard.wallet.wallets.commission-rate', '') }}/${walletId}`;
            
            // Populate modal
            document.getElementById('commissionUserName').textContent = userName;
            document.getElementById('commissionCurrentRate').textContent = `${currentRate}%`;
            document.getElementById('commissionRate').value = currentRate;
            
            // Update help text if current rate equals default
            const defaultRate = {{ $defaultRate }};
            const helpText = document.getElementById('commissionRateHelp');
            if (currentRate === defaultRate) {
                helpText.innerHTML = 'Rate must be between {{ $minRate }}% and {{ $maxRate }}%<br><small class="text-success">Currently using platform default</small>';
            } else {
                helpText.innerHTML = 'Rate must be between {{ $minRate }}% and {{ $maxRate }}%<br><small class="text-muted">Platform default: {{ $defaultRate }}%</small>';
            }
            
            // Show modal
            new bootstrap.Modal(commissionModal).show();
        });
    });
    
    // Quick rate buttons handler
    const quickRateButtons = document.querySelectorAll('.quick-rate-btn');
    quickRateButtons.forEach(button => {
        button.addEventListener('click', function() {
            const rate = this.dataset.rate;
            document.getElementById('commissionRate').value = rate;
        });
    });
    
    // Commission Form validation
    commissionForm.addEventListener('submit', function(e) {
        const rate = parseFloat(document.getElementById('commissionRate').value);
        const minRate = {{ $minRate }};
        const maxRate = {{ $maxRate }};
        
        if (rate < minRate || rate > maxRate) {
            e.preventDefault();
            alert(`Commission rate must be between ${minRate}% and ${maxRate}%`);
            return false;
        }
        
        return confirm('Are you sure you want to update the commission rate?');
    });
});
</script>
@endpush