@extends('admin.layout')

@section('content')
<div class="container-fluid">
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h4 class="font-weight-bold text-primary">Farm Management</h4>
        </div>
        <div class="col-auto">
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-download mr-1"></i> Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#"><i class="fas fa-file-excel mr-2"></i>Export Excel</a></li>
                    <li><a class="dropdown-item" href="#"><i class="fas fa-file-pdf mr-2"></i>Export PDF</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    @php
        $totalFarms = \App\Models\Farm::count();
        $activeFarms = \App\Models\Farm::where('status', 'active')->count();
        $pendingFarms = \App\Models\Farm::where('status', 'pending')->count();
        $rejectedFarms = \App\Models\Farm::where('status', 'rejected')->count();
    @endphp
    
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Total Farms</h5>
                            <h3 class="mb-0">{{ $totalFarms }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-home-lg-alt fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Active Farms</h5>
                            <h3 class="mb-0">{{ $activeFarms }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Pending Approval</h5>
                            <h3 class="mb-0">{{ $pendingFarms }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Rejected</h5>
                            <h3 class="mb-0">{{ $rejectedFarms }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-times-circle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard.farms.index') }}" id="filterForm">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label for="search" class="form-label font-weight-bold">Search</label>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               placeholder="Farm name, owner name or email..."
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label font-weight-bold">Status</label>
                        <select class="form-control" id="statuss" name="status">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="disabled" {{ request('status') == 'disabled' ? 'selected' : '' }}>Disabled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="city_id" class="form-label font-weight-bold">City</label>
                        <select class="form-control" id="city_id" name="city_id">
                            <option value="">All Cities</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" {{ request('city_id') == $city->id ? 'selected' : '' }}>
                                    {{ $city->name_en }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="owner_id" class="form-label font-weight-bold">Owner</label>
                        <select class="form-control" id="owner_id" name="owner_id">
                            <option value="">All Owners</option>
                            @foreach($owners as $owner)
                                <option value="{{ $owner->id }}" {{ request('owner_id') == $owner->id ? 'selected' : '' }}>
                                    {{ $owner->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="btn-group w-100">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-search mr-1"></i> Filter
                            </button>
                            <a href="{{ route('dashboard.farms.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times mr-1"></i> Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($farms->isEmpty())
        <div class="text-center py-4 text-muted">
            <i class="fas fa-info-circle fa-2x mb-2"></i>
            <p>
                @if(request()->hasAny(['search', 'status', 'city_id', 'owner_id']))
                    No farms found matching your filters. 
                    <a href="{{ route('dashboard.farms.index') }}" class="text-primary">Clear filters</a> to see all farms.
                @else
                    No farms yet.
                @endif
            </p>
        </div>
    @else
        <!-- Bulk Actions -->
        <div class="card mb-3 shadow-sm">
            <div class="card-body py-2">
                <form id="bulkActionForm" method="POST" action="{{ route('dashboard.farms.bulk-status') }}">
                    @csrf
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                                <label class="form-check-label" for="selectAll">
                                    Select All
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" name="status" required>
                                <option value="">Choose Status</option>
                                <option value="active">Approve Selected</option>
                                <option value="rejected">Reject Selected</option>
                                <option value="disabled">Disable Selected</option>
                                <option value="pending">Set as Pending</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-outline-primary" id="bulkActionBtn" disabled>
                                <i class="fas fa-edit mr-1"></i> Update Status
                            </button>
                        </div>
                        <div class="col-md-3 text-right">
                            <small class="text-muted">
                                <span id="selectedCount">0</span> farms selected
                            </small>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-center" width="50">
                                    <input type="checkbox" id="masterCheckbox">
                                </th>
                                <th class="text-center">Farm</th>
                                <th class="text-center">Owner</th>
                                <th class="text-center">Location</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Stats</th>
                                <th class="text-center">Created</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($farms as $farm)
                            <tr>
                                <td class="text-center align-middle">
                                    <input type="checkbox" name="farm_ids[]" value="{{ $farm->id }}" class="farm-checkbox">
                                </td>
                                <td class="align-middle">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            @if($farm->images->where('is_main', true)->first())
                                                <img src="{{ $farm->images->where('is_main', true)->first()->image_path }}" 
                                                     alt="Farm Image" 
                                                     class="rounded" 
                                                     style="width: 50px; height: 50px; object-fit: cover;">
                                            @else
                                                <div class="bg-secondary rounded d-flex align-items-center justify-content-center text-white" 
                                                     style="width: 50px; height: 50px;">
                                                    <i class="fas fa-home"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <h6 class="mb-1">{{ $farm->name_en ?: $farm->name_ar }}</h6>
                                            <small class="text-muted">ID: {{ $farm->id }}</small>
                                            @if($farm->name_ar && $farm->name_en)
                                                <div class="small text-muted" dir="rtl">{{ $farm->name_ar }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center align-middle">
                                    <div>
                                        <strong>{{ $farm->user->name }}</strong>
                                        <div class="small text-muted">{{ $farm->user->email }}</div>
                                        @if($farm->user->phone)
                                            <div class="small text-muted">{{ $farm->user->phone }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center align-middle">
                                    <div>
                                        <strong>{{ $farm->city->name_en ?? 'N/A' }}</strong>
                                        @if($farm->area)
                                            <div class="small text-muted">{{ $farm->area->name_en }}</div>
                                        @endif
                                        @if($farm->hasCoordinates())
                                            <div class="small text-success">
                                                <i class="fas fa-map-pin"></i> Has coordinates
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center align-middle">
                                    @php
                                        $statusConfig = [
                                            'pending' => ['class' => 'bg-warning text-dark', 'icon' => 'fas fa-clock'],
                                            'active' => ['class' => 'bg-success', 'icon' => 'fas fa-check-circle'],
                                            'rejected' => ['class' => 'bg-danger', 'icon' => 'fas fa-times-circle'],
                                            'disabled' => ['class' => 'bg-secondary', 'icon' => 'fas fa-ban']
                                        ];
                                        $config = $statusConfig[$farm->status] ?? ['class' => 'bg-dark', 'icon' => 'fas fa-question'];
                                    @endphp
                                    <span class="badge rounded-pill {{ $config['class'] }}">
                                        <i class="{{ $config['icon'] }} me-1"></i>
                                        {{ ucfirst($farm->status) }}
                                    </span>
                                </td>
                                <td class="text-center align-middle">
                                    <div class="small">
                                        <div class="text-primary">
                                            <i class="fas fa-calendar-check"></i> {{ $farm->bookings_count }} bookings
                                        </div>
                                        <div class="text-info">
                                            <i class="fas fa-star"></i> {{ $farm->ratings_count }} ratings
                                        </div>
                                        @if($farm->ratings_count >= 3)
                                            <div class="text-warning">
                                                ⭐ {{ number_format($farm->ratings->avg('rating'), 1) }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center align-middle">
                                    <div class="small">
                                        {{ $farm->created_at->format('M d, Y') }}
                                        <div class="text-muted">{{ $farm->created_at->diffForHumans() }}</div>
                                    </div>
                                </td>
                                <td class="text-center align-middle">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" 
                                                type="button" 
                                                data-bs-toggle="dropdown">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('dashboard.farms.show', $farm->id) }}">
                                                    <i class="fas fa-eye me-2"></i>View Details
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('dashboard.farms.edit', $farm->id) }}">
                                                    <i class="fas fa-edit me-2"></i>Edit Farm
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            @if($farm->status !== 'active')
                                            <li>
                                                <button class="dropdown-item text-success" 
                                                        onclick="updateFarmStatus({{ $farm->id }}, 'active')">
                                                    <i class="fas fa-check-circle me-2"></i>Approve
                                                </button>
                                            </li>
                                            @endif
                                            @if($farm->status !== 'rejected')
                                            <li>
                                                <button class="dropdown-item text-danger" 
                                                        onclick="updateFarmStatus({{ $farm->id }}, 'rejected')">
                                                    <i class="fas fa-times-circle me-2"></i>Reject
                                                </button>
                                            </li>
                                            @endif
                                            @if($farm->status !== 'disabled')
                                            <li>
                                                <button class="dropdown-item text-warning" 
                                                        onclick="updateFarmStatus({{ $farm->id }}, 'disabled')">
                                                    <i class="fas fa-ban me-2"></i>Disable
                                                </button>
                                            </li>
                                            @endif
                                        </ul>
                                    </div>
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
                        Showing {{ $farms->firstItem() }} to {{ $farms->lastItem() }} of {{ $farms->total() }} farms
                    </div>
                    <div>
                        {{ $farms->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusUpdateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Farm Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="statusUpdateForm" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="status" id="modalStatus">
                    <div id="rejectionReasonDiv" style="display: none;">
                        <label for="rejection_reason" class="form-label">Rejection Reason</label>
                        <textarea class="form-control" 
                                  id="rejection_reason" 
                                  name="rejection_reason" 
                                  rows="3" 
                                  placeholder="Please provide a reason for rejection..."></textarea>
                    </div>
                    <p id="statusMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Confirm</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-submit form on filter change
    const searchInput = document.getElementById('search');
    const statusFilter = document.getElementById('status');
    const cityFilter = document.getElementById('city_id');
    const ownerFilter = document.getElementById('owner_id');
    
    let debounceTimer;
    
    // Debounced search for text input
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() {
            document.getElementById('filterForm').submit();
        }, 500);
    });
    
    // Immediate submit for dropdowns
    [statusFilter, cityFilter, ownerFilter].forEach(element => {
        element.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });
    
    // Bulk actions
    $('#selectAll').change(function() {
        $('.farm-checkbox').prop('checked', this.checked);
        updateBulkActionButton();
    });
    
    $(document).on('change', '.farm-checkbox', function() {
        updateBulkActionButton();
        updateSelectAllCheckbox();
    });
    
    function updateBulkActionButton() {
        const selectedCount = $('.farm-checkbox:checked').length;
        $('#selectedCount').text(selectedCount);
        $('#bulkActionBtn').prop('disabled', selectedCount === 0);
    }
    
    function updateSelectAllCheckbox() {
        const totalCheckboxes = $('.farm-checkbox').length;
        const checkedCheckboxes = $('.farm-checkbox:checked').length;
        $('#selectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
    }
    
    // Bulk action form submission
    $('#bulkActionForm').on('submit', function(e) {
        e.preventDefault();
        const selectedIds = $('.farm-checkbox:checked').map(function() {
            return this.value;
        }).get();
        
        if (selectedIds.length === 0) {
            alert('Please select at least one farm');
            return;
        }
        
        if (confirm(`Are you sure you want to update ${selectedIds.length} farm(s)?`)) {
            // Add hidden inputs for selected farm IDs
            selectedIds.forEach(id => {
                $(this).append(`<input type="hidden" name="farm_ids[]" value="${id}">`);
            });
            this.submit();
        }
    });
});

function updateFarmStatus(farmId, status) {
    const modal = new bootstrap.Modal(document.getElementById('statusUpdateModal'));
    const form = document.getElementById('statusUpdateForm');
    const modalStatus = document.getElementById('modalStatus');
    const rejectionDiv = document.getElementById('rejectionReasonDiv');
    const statusMessage = document.getElementById('statusMessage');
    
    // Set form action
    form.action = `/dashboard/farms/${farmId}/status`;
    modalStatus.value = status;
    
    // Show/hide rejection reason field
    if (status === 'rejected') {
        rejectionDiv.style.display = 'block';
        document.getElementById('rejection_reason').required = true;
        statusMessage.textContent = 'Please provide a reason for rejecting this farm:';
    } else {
        rejectionDiv.style.display = 'none';
        document.getElementById('rejection_reason').required = false;
        statusMessage.textContent = `Are you sure you want to ${status} this farm?`;
    }
    
    modal.show();
}
</script>
@endpush
@endsection