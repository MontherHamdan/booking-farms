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

<!-- Filters -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('dashboard.wallet.wallets') }}" class="row g-3">
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" placeholder="Name or email...">
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="statuss" name="status">
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
                        <span class="badge badge-soft-info">{{ $wallets->total() }}</span>
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
                                <li><a class="dropdown-item" href="{{ route('dashboard.wallet.export', ['type' => 'wallets']) }}">Export Wallets</a></li>
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
                                <th>Status</th>
                                <th>Recent Activity</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($wallets as $wallet)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-sm">
                                                <div class="avatar-title rounded-circle bg-primary-subtle text-primary">
                                                    {{ strtoupper(substr($wallet->user->name, 0, 1)) }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-0">{{ $wallet->user->name }}</h6>
                                            <small class="text-muted">{{ $wallet->user->email }}</small>
                                            @if($wallet->user->farmOwnerBankAccount)
                                            <br><small class="text-success">
                                                <i class="mdi mdi-bank"></i> Bank Account Setup
                                            </small>
                                            @else
                                            <br><small class="text-warning">
                                                <i class="mdi mdi-alert-circle"></i> No Bank Account
                                            </small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold text-primary">AED {{ number_format($wallet->balance, 2) }}</span>
                                    @if($wallet->pending_balance > 0)
                                    <br><small class="text-muted">Pending: AED {{ number_format($wallet->pending_balance, 2) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-success">AED {{ number_format($wallet->total_earned, 2) }}</span>
                                </td>
                                <td>
                                    <span class="text-info">AED {{ number_format($wallet->total_paid_out, 2) }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-soft-secondary">{{ $wallet->platform_commission_rate }}%</span>
                                </td>
                                <td>
                                    @if($wallet->is_active)
                                    <span class="badge badge-soft-success">Active</span>
                                    @else
                                    <span class="badge badge-soft-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    @if($wallet->transactions->count() > 0)
                                    <div class="text-muted small">
                                        @foreach($wallet->transactions->take(2) as $transaction)
                                        <div class="mb-1">
                                            <span class="badge badge-soft-{{ $transaction->amount >= 0 ? 'success' : 'warning' }} me-1">
                                                {{ ucfirst(str_replace('_', ' ', $transaction->type)) }}
                                            </span>
                                            <span class="fw-bold {{ $transaction->amount >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $transaction->amount >= 0 ? '+' : '' }}AED {{ number_format($transaction->amount, 2) }}
                                            </span>
                                            <br><small class="text-muted">{{ $transaction->created_at->diffForHumans() }}</small>
                                        </div>
                                        @endforeach
                                        @if($wallet->transactions->count() > 2)
                                        <small class="text-primary">{{ $wallet->transactions->count() - 2 }} more...</small>
                                        @endif
                                    </div>
                                    @else
                                    <span class="text-muted small">No transactions</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('dashboard.wallet.wallets.show', $wallet->id) }}" 
                                           class="btn btn-sm btn-outline-primary" title="View Details">
                                            <i class="mdi mdi-eye"></i>
                                        </a>
                                        @if($wallet->balance >= 50 && $wallet->user->farmOwnerBankAccount)
                                        <button type="button" class="btn btn-sm btn-outline-warning" 
                                                data-bs-toggle="modal" data-bs-target="#processPaymentModal"
                                                data-user-id="{{ $wallet->user_id }}" 
                                                data-user-name="{{ $wallet->user->name }}"
                                                data-balance="{{ $wallet->balance }}"
                                                title="Process Payment">
                                            <i class="mdi mdi-bank-transfer-out"></i>
                                        </button>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                data-bs-toggle="modal" data-bs-target="#adjustmentModal"
                                                data-wallet-id="{{ $wallet->id }}" 
                                                data-user-name="{{ $wallet->user->name }}"
                                                data-balance="{{ $wallet->balance }}"
                                                title="Add Adjustment">
                                            <i class="mdi mdi-plus-minus-variant"></i>
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
                        Showing {{ $wallets->firstItem() ?? 0 }} to {{ $wallets->lastItem() ?? 0 }} of {{ $wallets->total() }} wallets
                    </div>
                    {{ $wallets->appends(request()->query())->links() }}
                </div>
                @else
                <div class="text-center py-5">
                    <i class="mdi mdi-wallet text-muted" style="font-size: 48px;"></i>
                    <p class="text-muted mt-2 mb-0">No wallets found</p>
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
                    <h5 class="modal-title">Process Manual Payment</h5>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Process Payment Modal
    const processPaymentModal = document.getElementById('processPaymentModal');
    if (processPaymentModal) {
        processPaymentModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const userId = button.getAttribute('data-user-id');
            const userName = button.getAttribute('data-user-name');
            const balance = button.getAttribute('data-balance');
            
            document.getElementById('paymentUserName').textContent = userName;
            document.getElementById('paymentBalance').textContent = 'AED ' + parseFloat(balance).toLocaleString();
            document.getElementById('paymentAmount').max = balance;
            document.getElementById('paymentForm').action = `/dashboard/wallet/process-payment/${userId}`;
        });
    }
    
    // Adjustment Modal
    const adjustmentModal = document.getElementById('adjustmentModal');
    if (adjustmentModal) {
        adjustmentModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const walletId = button.getAttribute('data-wallet-id');
            const userName = button.getAttribute('data-user-name');
            const balance = button.getAttribute('data-balance');
            
            document.getElementById('adjustmentUserName').textContent = userName;
            document.getElementById('adjustmentBalance').textContent = 'AED ' + parseFloat(balance).toLocaleString();
            document.getElementById('adjustmentForm').action = `/dashboard/wallet/wallets/${walletId}/adjustment`;
        });
    }
});
</script>

@endsection