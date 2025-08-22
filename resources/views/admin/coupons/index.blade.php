@extends('admin.layout')

@section('content')
<div class="container-fluid">
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h4 class="font-weight-bold text-primary">Coupons Management</h4>
        </div>
        <div class="col-auto">
            <a href="{{ route('dashboard.coupons.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus-circle mr-1"></i> Add New Coupon
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard.coupons.index') }}" id="filterForm">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label for="search" class="form-label font-weight-bold">Search Coupon</label>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               placeholder="Search by name or code..."
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="status_filter" class="form-label font-weight-bold">Status</label>
                        <select class="form-control" id="status_filter" name="status_filter">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status_filter') == 'active' ? 'selected' : '' }}>
                                Active
                            </option>
                            <option value="inactive" {{ request('status_filter') == 'inactive' ? 'selected' : '' }}>
                                Inactive
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="platform_filter" class="form-label font-weight-bold">Platform</label>
                        <select class="form-control" id="platform_filter" name="platform_filter">
                            <option value="">All Platforms</option>
                            <option value="{{ \App\Models\Coupon::PLATFORM_WEB }}" 
                                    {{ request('platform_filter') == \App\Models\Coupon::PLATFORM_WEB ? 'selected' : '' }}>
                                Web Only
                            </option>
                            <option value="{{ \App\Models\Coupon::PLATFORM_MOBILE }}" 
                                    {{ request('platform_filter') == \App\Models\Coupon::PLATFORM_MOBILE ? 'selected' : '' }}>
                                Mobile Only
                            </option>
                            <option value="{{ \App\Models\Coupon::PLATFORM_BOTH }}" 
                                    {{ request('platform_filter') == \App\Models\Coupon::PLATFORM_BOTH ? 'selected' : '' }}>
                                Web & Mobile
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="discount_type_filter" class="form-label font-weight-bold">Type</label>
                        <select class="form-control" id="discount_type_filter" name="discount_type_filter">
                            <option value="">All Types</option>
                            <option value="{{ \App\Models\Coupon::DISCOUNT_TYPE_PERCENTAGE }}" 
                                    {{ request('discount_type_filter') == \App\Models\Coupon::DISCOUNT_TYPE_PERCENTAGE ? 'selected' : '' }}>
                                Percentage
                            </option>
                            <option value="{{ \App\Models\Coupon::DISCOUNT_TYPE_FIXED_AMOUNT }}" 
                                    {{ request('discount_type_filter') == \App\Models\Coupon::DISCOUNT_TYPE_FIXED_AMOUNT ? 'selected' : '' }}>
                                Fixed Amount
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="btn-group w-100">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-search mr-1"></i> Filter
                            </button>
                            <a href="{{ route('dashboard.coupons.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times mr-1"></i> Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($coupons->isEmpty())
        <div class="text-center py-4 text-muted">
            <i class="fas fa-ticket-alt fa-2x mb-2"></i>
            <p>
                @if(request()->hasAny(['search', 'status_filter', 'platform_filter', 'discount_type_filter']))
                    No coupons found matching your filters. 
                    <a href="{{ route('dashboard.coupons.index') }}" class="text-primary">Clear filters</a> to see all coupons.
                @else
                    No coupons yet. Click "Add New Coupon" to create one.
                @endif
            </p>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive py-4 px-4">
                    <table class="table table-bordered table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-center">ID</th>
                                <th class="text-center">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-dark text-decoration-none">
                                        Name <i class="fas fa-sort text-muted"></i>
                                    </a>
                                </th>
                                <th class="text-center">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'code', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-dark text-decoration-none">
                                        Code <i class="fas fa-sort text-muted"></i>
                                    </a>
                                </th>
                                <th class="text-center">Discount</th>
                                <th class="text-center">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'start_date', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-dark text-decoration-none">
                                        Period <i class="fas fa-sort text-muted"></i>
                                    </a>
                                </th>
                                <th class="text-center">Platform</th>
                                <th class="text-center">Usage</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($coupons as $coupon)
                            <tr>
                                <td class="text-center align-middle">{{ $coupon->id }}</td>
                                <td class="align-middle">
                                    <strong>{{ $coupon->name }}</strong>
                                    @if($coupon->cities)
                                        <br><small class="text-muted">Cities: {{ implode(', ', $coupon->city_names) }}</small>
                                    @else
                                        <br><small class="text-success">All Cities</small>
                                    @endif
                                </td>
                                <td class="text-center align-middle">
                                    <code class="bg-light p-1 rounded" style="cursor: pointer;" 
                                          onclick="copyCouponCode('{{ $coupon->code }}')" 
                                          title="Click to copy">{{ $coupon->code }}</code>
                                </td>
                                <td class="text-center align-middle">
                                    <span class="badge {{ $coupon->discount_type === \App\Models\Coupon::DISCOUNT_TYPE_PERCENTAGE ? 'bg-info' : 'bg-success' }} text-white">
                                        {{ $coupon->discount_description }}
                                    </span>
                                </td>
                                <td class="text-center align-middle">
                                    <div class="small">
                                        <strong>Start:</strong> {{ $coupon->start_date->format('M d, Y') }}<br>
                                        <strong>End:</strong> {{ $coupon->end_date->format('M d, Y') }}
                                        @if($coupon->is_expired)
                                            <span class="badge bg-danger text-white ml-1">Expired</span>
                                        @elseif(!$coupon->is_started)
                                            <span class="badge bg-warning text-dark ml-1">Not Started</span>
                                        @else
                                            <span class="badge bg-success text-white ml-1">Active Period</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center align-middle">
                                    <span class="badge {{ $coupon->platform === \App\Models\Coupon::PLATFORM_BOTH ? 'bg-primary' : 'bg-secondary' }} text-white">
                                        {{ $coupon->platform_label }}
                                    </span>
                                </td>
                                <td class="text-center align-middle">
                                    <div class="small">
                                        @if($coupon->usages_count > 0)
                                            <a href="{{ route('dashboard.coupons.usages', $coupon->id) }}" 
                                               class="badge bg-info text-white text-decoration-none">
                                                <i class="fas fa-chart-line mr-1"></i>{{ $coupon->usages_count }} used
                                            </a>
                                        @else
                                            <span class="text-muted">
                                                <i class="fas fa-minus"></i> No usage
                                            </span>
                                        @endif
                                        <br>
                                        <small class="text-muted">{{ $coupon->usage_limit_description }}</small>
                                    </div>
                                </td>
                                <td class="text-center align-middle">
                                    <div>
                                        <span class="badge rounded-pill {{ $coupon->is_active ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $coupon->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                        <br>
                                        <small class="text-muted">{{ ucfirst($coupon->status) }}</small>
                                    </div>
                                </td>
                                <td class="text-center align-middle">
                                    <!-- Actions Dropdown -->
                                    <div class="dropdown d-inline-block">
                                        <a class="dropdown-toggle text-dark" id="dropdownMenuButton{{ $coupon->id }}"
                                           data-bs-toggle="dropdown" style="cursor: pointer;" aria-expanded="false" title="Actions">
                                            <i class="fas fa-cog"></i>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton{{ $coupon->id }}">
                                            <li>
                                                <a href="{{ route('dashboard.coupons.edit', $coupon->id) }}" class="dropdown-item" title="Edit Coupon">
                                                    <i class="fas fa-edit me-2"></i>Edit
                                                </a>
                                            </li>
                                            <li>
                                                <form action="{{ route('dashboard.coupons.toggle-status', $coupon->id) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="dropdown-item {{ $coupon->is_active ? 'text-warning' : 'text-success' }}">
                                                        <i class="fas fa-{{ $coupon->is_active ? 'pause' : 'play' }} me-2"></i>
                                                        {{ $coupon->is_active ? 'Deactivate' : 'Activate' }}
                                                    </button>
                                                </form>
                                            </li>
                                            @if($coupon->usages_count > 0)
                                            <li>
                                                <a href="{{ route('dashboard.coupons.usages', $coupon->id) }}" class="dropdown-item text-info">
                                                    <i class="fas fa-chart-line me-2"></i>View Usage ({{ $coupon->usages_count }})
                                                </a>
                                            </li>
                                            @endif
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button type="button" 
                                                        class="dropdown-item {{ $coupon->usages_count > 0 ? 'text-muted' : 'text-danger' }}" 
                                                        onclick="{{ $coupon->usages_count > 0 ? 'alert(\'Cannot delete coupon that has been used!\')' : 'confirmDelete(\'delete-coupon-' . $coupon->id . '\')' }}"
                                                        {{ $coupon->usages_count > 0 ? 'disabled' : '' }}
                                                        title="{{ $coupon->usages_count > 0 ? 'Cannot delete: used ' . $coupon->usages_count . ' time(s)' : 'Delete coupon' }}">
                                                    <i class="fas fa-trash-alt me-2"></i>Delete
                                                    @if($coupon->usages_count > 0)
                                                        <small class="text-muted">({{ $coupon->usages_count }} uses)</small>
                                                    @endif
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                    @if($coupon->usages_count == 0)
                                        <form id="delete-coupon-{{ $coupon->id }}" action="{{ route('dashboard.coupons.destroy', $coupon->id) }}" method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-top-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Showing {{ $coupons->firstItem() }} to {{ $coupons->lastItem() }} of {{ $coupons->total() }} coupons
                        @if($coupons->sum('usages_count') > 0)
                            | Total usage: {{ $coupons->sum('usages_count') }}
                        @endif
                        @if($coupons->where('is_active', true)->count() > 0)
                            | {{ $coupons->where('is_active', true)->count() }} active
                        @endif
                    </div>
                    <div>
                        {{ $coupons->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
    <script>
    // Auto-submit form on filter change
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('search');
        const statusFilter = document.getElementById('status_filter');
        const platformFilter = document.getElementById('platform_filter');
        const discountTypeFilter = document.getElementById('discount_type_filter');
        
        let debounceTimer;
        
        // Debounced search for text input
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function() {
                document.getElementById('filterForm').submit();
            }, 500);
        });
        
        // Immediate submit for dropdowns
        statusFilter.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
        
        platformFilter.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
        
        discountTypeFilter.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });

    // Copy coupon code to clipboard
    function copyCouponCode(code) {
        navigator.clipboard.writeText(code).then(() => {
            Swal.fire({
                icon: 'success',
                title: 'Copied!',
                text: 'Coupon code copied to clipboard',
                timer: 1500,
                showConfirmButton: false
            });
        }).catch(() => {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = code;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            
            Swal.fire({
                icon: 'success',
                title: 'Copied!',
                text: 'Coupon code copied to clipboard',
                timer: 1500,
                showConfirmButton: false
            });
        });
    }
    </script>
@endpush
@endsection