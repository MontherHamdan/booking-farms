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

<!-- Enhanced Quick Stats with Pending Balance -->
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
                <i class="mdi mdi-cash-check text-success" style="font-size: 24px;"></i>
                <h4 class="text-success mt-2 mb-1">AED {{ number_format($wallets->sum('balance'), 2) }}</h4>
                <p class="text-muted mb-0 small">Confirmed Balance</p>
                <small class="text-success">Ready for payment</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-warning-subtle">
            <div class="card-body text-center">
                <i class="mdi mdi-clock-outline text-warning" style="font-size: 24px;"></i>
                <h4 class="text-warning mt-2 mb-1">AED {{ number_format($wallets->sum('pending_balance'), 2) }}</h4>
                <p class="text-muted mb-0 small">Pending Balance</p>
                <small class="text-warning">Awaiting completion</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-info-subtle">
            <div class="card-body text-center">
                <i class="mdi mdi-trending-up text-info" style="font-size: 24px;"></i>
                <h4 class="text-info mt-2 mb-1">AED {{ number_format($wallets->sum('total_earned'), 2) }}</h4>
                <p class="text-muted mb-0 small">Total Earned</p>
                <small class="text-info">All time</small>
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
                        <label for="filter" class="form-label">Special Filter</label>
                        <select class="form-select" id="filter" name="filter">
                            <option value="">All Wallets</option>
                            <option value="no_bank" {{ request('filter') === 'no_bank' ? 'selected' : '' }}>Missing Bank Account</option>
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
                            <option value="balance" {{ request('sort', 'balance') === 'balance' ? 'selected' : '' }}>Confirmed Balance</option>
                            <option value="pending_balance" {{ request('sort') === 'pending_balance' ? 'selected' : '' }}>Pending Balance</option>
                            <option value="total_earned" {{ request('sort') === 'total_earned' ? 'selected' : '' }}>Total Earned</option>
                            <option value="user_name" {{ request('sort') === 'user_name' ? 'selected' : '' }}>User Name</option>
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

<!-- Enhanced Wallets Table -->
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
                        @if(request()->hasAny(['search', 'status', 'min_balance', 'sort', 'direction', 'filter']))
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
                                <th>Balance Details</th>
                                <th>Total Activity</th>
                                <th>Commission Rate</th>
                                <th>Bank Account</th>
                                <th>Status</th>
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
                                    {{-- Confirmed Balance --}}
                                    <div class="fw-bold text-success">
                                        <i class="mdi mdi-cash-check me-1"></i>
                                        AED {{ number_format($wallet->balance, 2) }}
                                    </div>
                                    
                                    {{-- Pending Balance --}}
                                    @if($wallet->pending_balance > 0)
                                    <div class="text-warning mt-1">
                                        <i class="mdi mdi-clock-outline me-1"></i>
                                        <small>+AED {{ number_format($wallet->pending_balance, 2) }} pending</small>
                                    </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="mb-1">
                                        <small class="text-muted">Earned:</small>
                                        <span class="fw-bold text-info">AED {{ number_format($wallet->total_earned, 2) }}</span>
                                    </div>
                                    <div>
                                        <small class="text-muted">Paid Out:</small>
                                        <span class="fw-bold text-secondary">AED {{ number_format($wallet->total_paid_out ?? 0, 2) }}</span>
                                    </div>
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
                                    <span class="badge bg-{{ $wallet->is_active ? 'success' : 'danger' }}">
                                        {{ $wallet->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    <br><small class="text-muted mt-1">
                                        {{ $wallet->updated_at->diffForHumans() }}
                                    </small>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('dashboard.wallet.wallets.show', $wallet->id) }}" 
                                           class="btn btn-sm btn-outline-primary" title="View Details">
                                            <i class="mdi mdi-eye"></i>
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
                        Showing {{ $wallets->firstItem() ?? 0 }} to {{ $wallets->lastItem() ?? 0 }} 
                        of {{ $wallets->total() }} wallets
                    </div>
                    {{ $wallets->appends(request()->query())->links() }}
                </div>
                @else
                <div class="text-center py-5">
                    <i class="mdi mdi-wallet text-muted" style="font-size: 48px;"></i>
                    <p class="text-muted mt-2">No wallets found</p>
                    @if(request()->hasAny(['search', 'status', 'min_balance', 'filter']))
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
@endsection

@push('scripts')
<script>

</script>
@endpush