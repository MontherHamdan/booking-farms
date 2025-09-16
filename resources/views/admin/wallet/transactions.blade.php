@extends('admin.layout')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.wallet.index') }}">Wallet Management</a></li>
                    <li class="breadcrumb-item active">Transactions</li>
                </ol>
            </div>
            <h4 class="page-title">Wallet Transactions</h4>
        </div>
    </div>
</div>

<!-- Enhanced Summary Cards with New Transaction Types -->
<div class="row">
    <div class="col-md-3">
        <div class="card border-0 bg-success-subtle">
            <div class="card-body text-center">
                <i class="mdi mdi-trending-up text-success" style="font-size: 24px;"></i>
                <h4 class="text-success mt-2 mb-1">AED {{ number_format($totalEarnings, 2) }}</h4>
                <p class="text-muted mb-0 small">Total Earnings</p>
                <div class="mt-1">
                    <small class="text-warning">Pending: AED {{ number_format($pendingEarnings, 2) }}</small>
                    <br><small class="text-success">Confirmed: AED {{ number_format($confirmedEarnings, 2) }}</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 bg-warning-subtle">
            <div class="card-body text-center">
                <i class="mdi mdi-bank-transfer-out text-warning" style="font-size: 24px;"></i>
                <h4 class="text-warning mt-2 mb-1">AED {{ number_format($totalManualPayments, 2) }}</h4>
                <p class="text-muted mb-0 small">Manual Payments</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 bg-info-subtle">
            <div class="card-body text-center">
                <i class="mdi mdi-percent text-info" style="font-size: 24px;"></i>
                <h4 class="text-info mt-2 mb-1">AED {{ number_format($totalCommissions, 2) }}</h4>
                <p class="text-muted mb-0 small">Platform Commission</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 bg-danger-subtle">
            <div class="card-body text-center">
                <i class="mdi mdi-cash-refund text-danger" style="font-size: 24px;"></i>
                <h4 class="text-danger mt-2 mb-1">AED {{ number_format($totalRefunds, 2) }}</h4>
                <p class="text-muted mb-0 small">Refunds</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-secondary-subtle">
            <div class="card-body text-center">
                <i class="mdi mdi-plus-minus-variant text-secondary" style="font-size: 24px;"></i>
                <h4 class="text-secondary mt-2 mb-1">AED {{ number_format($totalAdjustments, 2) }}</h4>
                <p class="text-muted mb-0 small">Adjustments & Bonuses</p>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Filters -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" 
                               value="{{ request('search') }}" placeholder="Reference, name, email...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            @foreach($filterOptions as $value => $label)
                                <option value="{{ $value }}" {{ request('type') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">From Date</label>
                        <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">To Date</label>
                        <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
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

<!-- Enhanced Transactions Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">
                        Transactions
                        <span class="badge bg-info">{{ $transactions->total() }}</span>
                    </h5>
                    <div class="d-flex gap-2">
                        @if(request()->hasAny(['search', 'type', 'status', 'from_date', 'to_date']))
                        <a href="{{ route('dashboard.wallet.transactions') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="mdi mdi-refresh me-1"></i> Clear
                        </a>
                        @endif
                        <a href="{{ route('dashboard.wallet.export.wallets') }}" class="btn btn-sm btn-outline-primary">
                            <i class="mdi mdi-export me-1"></i> Export
                        </a>
                    </div>
                </div>

                @if($transactions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Farm Owner</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Balance Impact</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $transaction)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $transaction->created_at->format('M d, Y') }}</div>
                                    <small class="text-muted">{{ $transaction->created_at->format('H:i') }}</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-2">
                                            <div class="avatar-sm bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center">
                                                <span class="text-primary fw-bold">{{ strtoupper(substr($transaction->wallet->user->name, 0, 1)) }}</span>
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $transaction->wallet->user->name }}</h6>
                                            <small class="text-muted">{{ $transaction->wallet->user->email }}</small>
                                        </div>
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
                                    
                                    {{-- Balance type indicator --}}
                                    @if(in_array($transaction->type, ['pending_earning', 'earning_confirmed']))
                                    <br><small class="text-muted mt-1">
                                        @if($transaction->type === 'pending_earning')
                                        <i class="mdi mdi-clock-outline text-warning"></i> → Pending
                                        @else
                                        <i class="mdi mdi-check-circle text-success"></i> → Confirmed
                                        @endif
                                    </small>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold {{ $transaction->amount >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $transaction->amount >= 0 ? '+' : '' }}AED {{ number_format($transaction->amount, 2) }}
                                    </div>
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
                                    <small class="text-muted">
                                        Balance: AED {{ number_format($transaction->balance_after, 2) }}
                                    </small>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-muted">{{ Str::limit($transaction->description, 40) }}</div>
                                    @if($transaction->booking)
                                    <small class="text-primary">
                                        <i class="mdi mdi-calendar me-1"></i>{{ $transaction->booking->booking_reference }}
                                    </small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $transaction->status === 'completed' ? 'success' : ($transaction->status === 'pending' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-sm btn-outline-info view-details-btn"
                                                data-transaction="{{ json_encode([
                                                    'id' => $transaction->id,
                                                    'reference' => $transaction->reference,
                                                    'type' => $transaction->type,
                                                    'amount' => $transaction->amount,
                                                    'description' => $transaction->description,
                                                    'status' => $transaction->status,
                                                    'created_at' => $transaction->created_at->format('Y-m-d H:i:s'),
                                                    'balance_before' => $transaction->balance_before,
                                                    'balance_after' => $transaction->balance_after,
                                                    'pending_balance_before' => $transaction->pending_balance_before,
                                                    'pending_balance_after' => $transaction->pending_balance_after,
                                                    'user_name' => $transaction->wallet->user->name,
                                                    'user_email' => $transaction->wallet->user->email,
                                                    'booking_reference' => $transaction->booking?->booking_reference ?? null,
                                                    'wallet_id' => $transaction->wallet_id
                                                ]) }}"
                                                title="View Details">
                                            <i class="mdi mdi-eye"></i>
                                        </button>
                                        <a href="{{ route('dashboard.wallet.wallets.show', $transaction->wallet_id) }}" 
                                           class="btn btn-sm btn-outline-primary" title="View Wallet">
                                            <i class="mdi mdi-wallet"></i>
                                        </a>
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
                        Showing {{ $transactions->firstItem() ?? 0 }} to {{ $transactions->lastItem() ?? 0 }} 
                        of {{ $transactions->total() }} transactions
                    </div>
                    {{ $transactions->appends(request()->query())->links() }}
                </div>
                @else
                <div class="text-center py-5">
                    <i class="mdi mdi-format-list-bulleted text-muted" style="font-size: 48px;"></i>
                    <p class="text-muted mt-2">No transactions found</p>
                    @if(request()->hasAny(['search', 'type', 'status', 'from_date', 'to_date']))
                    <a href="{{ route('dashboard.wallet.transactions') }}" class="btn btn-sm btn-outline-primary mt-2">
                        Clear filters and show all
                    </a>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Transaction Details Modal -->
<div class="modal fade" id="transactionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Transaction Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted">Transaction Info</h6>
                        <table class="table table-sm">
                            <tr>
                                <th class="text-muted">Reference:</th>
                                <td><code id="modalReference"></code></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Type:</th>
                                <td><span id="modalType" class="badge"></span></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Amount:</th>
                                <td><span id="modalAmount" class="fw-bold"></span></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Status:</th>
                                <td><span id="modalStatus" class="badge"></span></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Date:</th>
                                <td id="modalDate"></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Balance Changes</h6>
                        <table class="table table-sm">
                            <tr>
                                <th class="text-muted">Confirmed Before:</th>
                                <td id="modalBalanceBefore" class="fw-bold"></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Confirmed After:</th>
                                <td id="modalBalanceAfter" class="fw-bold"></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Pending Before:</th>
                                <td id="modalPendingBefore" class="fw-bold text-warning"></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Pending After:</th>
                                <td id="modalPendingAfter" class="fw-bold text-warning"></td>
                            </tr>
                        </table>

                        <h6 class="text-muted mt-3">Farm Owner</h6>
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 me-2">
                                <div class="avatar-sm bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center">
                                    <span id="modalUserAvatar" class="text-primary fw-bold"></span>
                                </div>
                            </div>
                            <div>
                                <div id="modalUserName" class="fw-bold"></div>
                                <small id="modalUserEmail" class="text-muted"></small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-12">
                        <h6 class="text-muted">Description</h6>
                        <div id="modalDescription" class="p-2 bg-light rounded"></div>
                    </div>
                </div>
                
                <div class="row mt-3" id="modalBookingSection" style="display: none;">
                    <div class="col-12">
                        <h6 class="text-muted">Related Booking</h6>
                        <div id="modalBooking" class="p-2 bg-light rounded"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="modalViewWallet" class="btn btn-primary">
                    <i class="mdi mdi-wallet me-1"></i> View Wallet
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('transactionModal');
    const detailButtons = document.querySelectorAll('.view-details-btn');
    
    detailButtons.forEach(button => {
        button.addEventListener('click', function() {
            const data = JSON.parse(this.dataset.transaction);
            
            // Basic Information
            document.getElementById('modalReference').textContent = data.reference;
            document.getElementById('modalDescription').textContent = data.description;
            document.getElementById('modalDate').textContent = new Date(data.created_at).toLocaleString();
            
            // Amount with color
            const amountEl = document.getElementById('modalAmount');
            amountEl.textContent = (data.amount >= 0 ? '+' : '') + 'AED ' + parseFloat(data.amount).toLocaleString();
            amountEl.className = 'fw-bold ' + (data.amount >= 0 ? 'text-success' : 'text-danger');
            
            // Type badge with our configuration
            const typeConfig = {
                'pending_earning': 'bg-warning',
                'earning_confirmed': 'bg-success',
                'manual_payment': 'bg-info',
                'commission': 'bg-secondary',
                'refund': 'bg-danger',
                'adjustment': 'bg-dark',
                'bonus': 'bg-primary'
            };
            const typeEl = document.getElementById('modalType');
            typeEl.className = 'badge ' + (typeConfig[data.type] || 'bg-light');
            typeEl.textContent = data.type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
            
            // Status badge
            const statusColors = {
                'completed': 'bg-success',
                'pending': 'bg-warning',
                'failed': 'bg-danger'
            };
            const statusEl = document.getElementById('modalStatus');
            statusEl.className = 'badge ' + (statusColors[data.status] || 'bg-secondary');
            statusEl.textContent = data.status.charAt(0).toUpperCase() + data.status.slice(1);
            
            // Balance Information
            document.getElementById('modalBalanceBefore').textContent = 'AED ' + parseFloat(data.balance_before || 0).toLocaleString();
            document.getElementById('modalBalanceAfter').textContent = 'AED ' + parseFloat(data.balance_after || 0).toLocaleString();
            document.getElementById('modalPendingBefore').textContent = 'AED ' + parseFloat(data.pending_balance_before || 0).toLocaleString();
            document.getElementById('modalPendingAfter').textContent = 'AED ' + parseFloat(data.pending_balance_after || 0).toLocaleString();
            
            // User Information  
            document.getElementById('modalUserName').textContent = data.user_name;
            document.getElementById('modalUserEmail').textContent = data.user_email;
            document.getElementById('modalUserAvatar').textContent = data.user_name.charAt(0).toUpperCase();
            
            // Booking Information (if available)
            if (data.booking_reference) {
                document.getElementById('modalBookingSection').style.display = 'block';
                document.getElementById('modalBooking').innerHTML = `
                    <div><strong>Booking Reference:</strong> ${data.booking_reference}</div>
                `;
            } else {
                document.getElementById('modalBookingSection').style.display = 'none';
            }
            
            // Set wallet link
            document.getElementById('modalViewWallet').href = `{{ route('dashboard.wallet.wallets.show', '') }}/${data.wallet_id || ''}`;
            
            // Show modal
            new bootstrap.Modal(modal).show();
        });
    });
});
</script>
@endpush