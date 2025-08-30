@extends('admin.layout')
@section('content')

<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.wallet.index') }}">Farm Owner Fund Management</a></li>
                    <li class="breadcrumb-item active">Fund Transactions</li>
                </ol>
            </div>
            <h4 class="page-title">Farm Owner Fund Transactions</h4>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row">
    <div class="col-md-4">
        <div class="card border-0 bg-success-subtle">
            <div class="card-body text-center">
                <i class="mdi mdi-trending-up text-success" style="font-size: 24px;"></i>
                <h4 class="text-success mt-2 mb-1">AED {{ number_format($totalEarnings, 2) }}</h4>
                <p class="text-muted mb-0 small">Farm Owner Earnings</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 bg-warning-subtle">
            <div class="card-body text-center">
                <i class="mdi mdi-bank-transfer-out text-warning" style="font-size: 24px;"></i>
                <h4 class="text-warning mt-2 mb-1">AED {{ number_format($totalPayments, 2) }}</h4>
                <p class="text-muted mb-0 small">Disbursed to Farm Owners</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 bg-info-subtle">
            <div class="card-body text-center">
                <i class="mdi mdi-percent text-info" style="font-size: 24px;"></i>
                <h4 class="text-info mt-2 mb-1">AED {{ number_format($totalCommissions, 2) }}</h4>
                <p class="text-muted mb-0 small">Platform Commission Revenue</p>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('dashboard.wallet.transactions') }}" class="row g-3">
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" placeholder="Reference, user name, email...">
                    </div>
                    <div class="col-md-2">
                        <label for="type" class="form-label">Transaction Type</label>
                        <select class="form-select" id="type" name="type">
                            <option value="">All Types</option>
                            <option value="earning" {{ request('type') === 'earning' ? 'selected' : '' }}>Farm Earning</option>
                            <option value="manual_payment" {{ request('type') === 'manual_payment' ? 'selected' : '' }}>Disbursement</option>
                            <option value="commission" {{ request('type') === 'commission' ? 'selected' : '' }}>Platform Commission</option>
                            <option value="refund" {{ request('type') === 'refund' ? 'selected' : '' }}>Refund</option>
                            <option value="adjustment" {{ request('type') === 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                            <option value="bonus" {{ request('type') === 'bonus' ? 'selected' : '' }}>Bonus</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="statuss" name="status">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="from_date" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="from_date" name="from_date" 
                               value="{{ request('from_date') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="to_date" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="to_date" name="to_date" 
                               value="{{ request('to_date') }}">
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

<!-- Transactions Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">
                        Farm Owner Fund Transactions 
                        <span class="badge badge-soft-info">{{ $transactions->total() }}</span>
                    </h5>
                    <div class="d-flex gap-2">
                        @if(request()->hasAny(['search', 'type', 'status', 'from_date', 'to_date']))
                        <a href="{{ route('dashboard.wallet.transactions') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="mdi mdi-refresh me-1"></i> Clear Filters
                        </a>
                        @endif
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="mdi mdi-export me-1"></i> Export
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('dashboard.wallet.export', array_merge(['type' => 'transactions'], request()->query())) }}">Export Current Results</a></li>
                                <li><a class="dropdown-item" href="{{ route('dashboard.wallet.export', ['type' => 'transactions']) }}">Export All Transactions</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                @if($transactions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date/Time</th>
                                <th>Farm Owner</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Balance Change</th>
                                <th>Description</th>
                                <th>Reference</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $transaction)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $transaction->created_at->format('M d, Y') }}</div>
                                    <small class="text-muted">{{ $transaction->created_at->format('H:i:s') }}</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-sm">
                                                <div class="avatar-title rounded-circle bg-primary-subtle text-primary">
                                                    {{ strtoupper(substr($transaction->wallet->user->name, 0, 1)) }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-2">
                                            <h6 class="mb-0">{{ $transaction->wallet->user->name }}</h6>
                                            <small class="text-muted">{{ $transaction->wallet->user->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $typeColors = [
                                            'earning' => 'success',
                                            'manual_payment' => 'warning',
                                            'commission' => 'info',
                                            'refund' => 'danger',
                                            'adjustment' => 'secondary',
                                            'bonus' => 'primary'
                                        ];
                                        $typeColor = $typeColors[$transaction->type] ?? 'light';
                                        
                                        $typeLabels = [
                                            'earning' => 'Farm Earning',
                                            'manual_payment' => 'Disbursement',
                                            'commission' => 'Platform Commission',
                                            'refund' => 'Refund',
                                            'adjustment' => 'Admin Adjustment',
                                            'bonus' => 'Bonus'
                                        ];
                                        $typeLabel = $typeLabels[$transaction->type] ?? ucfirst(str_replace('_', ' ', $transaction->type));
                                    @endphp
                                    <span class="badge badge-soft-{{ $typeColor }}">
                                        {{ $typeLabel }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold {{ $transaction->amount >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $transaction->amount >= 0 ? '+' : '' }}AED {{ number_format($transaction->amount, 2) }}
                                    </div>
                                    @if($transaction->type === 'earning')
                                    <small class="text-muted">Farm owner earned</small>
                                    @elseif($transaction->type === 'manual_payment')
                                    <small class="text-muted">Disbursed to farm owner</small>
                                    @elseif($transaction->type === 'commission')
                                    <small class="text-success">Platform revenue</small>
                                    @elseif($transaction->type === 'refund')
                                    <small class="text-danger">Deducted from farm owner</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="small text-muted">
                                        <div>Before: AED {{ number_format($transaction->balance_before, 2) }}</div>
                                        <div>After: AED {{ number_format($transaction->balance_after, 2) }}</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-muted">{{ $transaction->description }}</div>
                                    @if($transaction->booking)
                                    <small class="text-primary d-block">
                                        <i class="mdi mdi-calendar me-1"></i>{{ $transaction->booking->booking_reference }}
                                    </small>
                                    @endif
                                    @if($transaction->processedBy)
                                    <small class="text-info d-block">
                                        <i class="mdi mdi-account-cog me-1"></i>By {{ $transaction->processedBy->name }}
                                    </small>
                                    @endif
                                </td>
                                <td>
                                    <code class="text-muted small">{{ $transaction->reference }}</code>
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'completed' => 'success',
                                            'pending' => 'warning',
                                            'failed' => 'danger',
                                            'cancelled' => 'secondary'
                                        ];
                                        $statusColor = $statusColors[$transaction->status] ?? 'light';
                                    @endphp
                                    <span class="badge badge-soft-{{ $statusColor }}">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                    @if($transaction->processed_at)
                                    <br><small class="text-muted">{{ $transaction->processed_at->format('M d, H:i') }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                data-bs-toggle="modal" data-bs-target="#transactionModal"
                                                data-transaction="{{ json_encode($transaction->toArray()) }}"
                                                title="View Details">
                                            <i class="mdi mdi-eye"></i>
                                        </button>
                                        <a href="{{ route('dashboard.wallet.wallets.show', $transaction->wallet_id) }}" 
                                           class="btn btn-sm btn-outline-primary" title="View Wallet">
                                            <i class="mdi mdi-wallet"></i>
                                        </a>
                                        @if($transaction->booking)
                                        {{-- <a href="{{ route('dashboard.bookings.show', $transaction->booking_id) }}"  --}}
                                        <a href="#" 
                                           class="btn btn-sm btn-outline-secondary" title="View Booking">
                                            <i class="mdi mdi-calendar"></i>
                                        </a>
                                        @endif
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
                        Showing {{ $transactions->firstItem() ?? 0 }} to {{ $transactions->lastItem() ?? 0 }} of {{ $transactions->total() }} transactions
                    </div>
                    {{ $transactions->appends(request()->query())->links() }}
                </div>
                @else
                <div class="text-center py-5">
                    <i class="mdi mdi-format-list-bulleted text-muted" style="font-size: 48px;"></i>
                    <p class="text-muted mt-2 mb-0">No transactions found</p>
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

<!-- Transaction Details Modal -->
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
                        <h6 class="text-muted">Basic Information</h6>
                        <table class="table table-sm">
                            <tr>
                                <th class="text-muted" style="width: 40%;">Reference:</th>
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
                                <th class="text-muted">Created:</th>
                                <td id="modalCreated"></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Processed:</th>
                                <td id="modalProcessed"></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Balance Information</h6>
                        <table class="table table-sm">
                            <tr>
                                <th class="text-muted" style="width: 40%;">Before:</th>
                                <td id="modalBalanceBefore" class="fw-bold"></td>
                            </tr>
                            <tr>
                                <th class="text-muted">After:</th>
                                <td id="modalBalanceAfter" class="fw-bold"></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Change:</th>
                                <td id="modalBalanceChange" class="fw-bold"></td>
                            </tr>
                        </table>
                        
                        <div id="modalFarmOwner" class="mt-3" style="display: none;">
                            <h6 class="text-muted">Farm Owner</h6>
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm">
                                        <div class="avatar-title rounded-circle bg-primary-subtle text-primary" id="modalUserAvatar">
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-2">
                                    <div id="modalUserName" class="fw-bold"></div>
                                    <small id="modalUserEmail" class="text-muted"></small>
                                </div>
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
                
                <div class="row mt-3" id="modalMetadataSection" style="display: none;">
                    <div class="col-12">
                        <h6 class="text-muted">Additional Information</h6>
                        <div id="modalMetadata" class="p-2 bg-light rounded"></div>
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
                <a href="#" id="modalViewWallet" class="btn btn-primary" style="display: none;">
                    <i class="mdi mdi-wallet me-1"></i> View Wallet
                </a>
                <a href="#" id="modalViewBooking" class="btn btn-outline-primary" style="display: none;">
                    <i class="mdi mdi-calendar me-1"></i> View Booking
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const transactionModal = document.getElementById('transactionModal');
    if (transactionModal) {
        transactionModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const transactionData = JSON.parse(button.getAttribute('data-transaction'));
            
            // Basic Information
            document.getElementById('modalReference').textContent = transactionData.reference;
            document.getElementById('modalAmount').textContent = (transactionData.amount >= 0 ? '+' : '') + 'AED ' + parseFloat(transactionData.amount).toLocaleString();
            document.getElementById('modalAmount').className = 'fw-bold ' + (transactionData.amount >= 0 ? 'text-success' : 'text-danger');
            document.getElementById('modalDescription').textContent = transactionData.description;
            document.getElementById('modalCreated').textContent = new Date(transactionData.created_at).toLocaleString();
            document.getElementById('modalProcessed').textContent = transactionData.processed_at ? new Date(transactionData.processed_at).toLocaleString() : 'Not processed';
            
            // Type badge
            const typeColors = {
                'earning': 'badge-soft-success',
                'manual_payment': 'badge-soft-warning',
                'commission': 'badge-soft-info',
                'refund': 'badge-soft-danger',
                'adjustment': 'badge-soft-secondary',
                'bonus': 'badge-soft-primary'
            };
            const typeLabels = {
                'earning': 'Farm Earning',
                'manual_payment': 'Disbursement',
                'commission': 'Platform Commission',
                'refund': 'Refund',
                'adjustment': 'Admin Adjustment',
                'bonus': 'Bonus'
            };
            const typeColor = typeColors[transactionData.type] || 'badge-soft-light';
            const typeLabel = typeLabels[transactionData.type] || transactionData.type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
            document.getElementById('modalType').className = 'badge ' + typeColor;
            document.getElementById('modalType').textContent = typeLabel;
            
            // Status badge
            const statusColors = {
                'completed': 'badge-soft-success',
                'pending': 'badge-soft-warning',
                'failed': 'badge-soft-danger',
                'cancelled': 'badge-soft-secondary'
            };
            const statusColor = statusColors[transactionData.status] || 'badge-soft-light';
            document.getElementById('modalStatus').className = 'badge ' + statusColor;
            document.getElementById('modalStatus').textContent = transactionData.status.charAt(0).toUpperCase() + transactionData.status.slice(1);
            
            // Balance Information
            document.getElementById('modalBalanceBefore').textContent = 'AED ' + parseFloat(transactionData.balance_before).toLocaleString();
            document.getElementById('modalBalanceAfter').textContent = 'AED ' + parseFloat(transactionData.balance_after).toLocaleString();
            
            const balanceChange = transactionData.balance_after - transactionData.balance_before;
            document.getElementById('modalBalanceChange').textContent = (balanceChange >= 0 ? '+' : '') + 'AED ' + parseFloat(balanceChange).toLocaleString();
            document.getElementById('modalBalanceChange').className = 'fw-bold ' + (balanceChange >= 0 ? 'text-success' : 'text-danger');
            
            // Farm Owner Information (if available)
            if (transactionData.wallet && transactionData.wallet.user) {
                document.getElementById('modalFarmOwner').style.display = 'block';
                document.getElementById('modalUserName').textContent = transactionData.wallet.user.name;
                document.getElementById('modalUserEmail').textContent = transactionData.wallet.user.email;
                document.getElementById('modalUserAvatar').textContent = transactionData.wallet.user.name.charAt(0).toUpperCase();
                
                document.getElementById('modalViewWallet').style.display = 'inline-block';
                document.getElementById('modalViewWallet').href = '/dashboard/wallet/wallets/' + transactionData.wallet_id;
            }
            
            // Metadata (if available)
            if (transactionData.metadata && Object.keys(transactionData.metadata).length > 0) {
                document.getElementById('modalMetadataSection').style.display = 'block';
                let metadataHtml = '';
                for (const [key, value] of Object.entries(transactionData.metadata)) {
                    metadataHtml += `<div><strong>${key.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}:</strong> ${value}</div>`;
                }
                document.getElementById('modalMetadata').innerHTML = metadataHtml;
            } else {
                document.getElementById('modalMetadataSection').style.display = 'none';
            }
            
            // Booking Information (if available)
            if (transactionData.booking) {
                document.getElementById('modalBookingSection').style.display = 'block';
                document.getElementById('modalBooking').innerHTML = `
                    <div><strong>Booking Reference:</strong> ${transactionData.booking.booking_reference}</div>
                    <div><strong>Customer:</strong> ${transactionData.booking.customer_name}</div>
                    <div><strong>Status:</strong> ${transactionData.booking.booking_status}</div>
                `;
                
                document.getElementById('modalViewBooking').style.display = 'inline-block';
                document.getElementById('modalViewBooking').href = '/dashboard/bookings/show/' + transactionData.booking_id;
            } else {
                document.getElementById('modalBookingSection').style.display = 'none';
                document.getElementById('modalViewBooking').style.display = 'none';
            }
        });
    }
});
</script>

@endsection