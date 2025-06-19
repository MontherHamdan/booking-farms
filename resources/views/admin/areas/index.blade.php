@extends('admin.layout')

@section('content')
<div class="container-fluid">
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h4 class="font-weight-bold text-primary">Areas Management</h4>
        </div>
        <div class="col-auto">
            <a href="{{ route('dashboard.areas.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus-circle mr-1"></i> Add New Area
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard.areas.index') }}" id="filterForm">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <label for="search" class="form-label font-weight-bold">Search Area</label>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               placeholder="Search by area name (Arabic or English)..."
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="city_filter" class="form-label font-weight-bold">Filter by City</label>
                        <select class="form-control" id="city_filter" name="city_filter">
                            <option value="">All Cities</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" 
                                        {{ request('city_filter') == $city->id ? 'selected' : '' }}>
                                    {{ $city->name_en }} ({{ $city->name_ar }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status_filter" class="form-label font-weight-bold">Status</label>
                        <select class="form-control" id="status_filter" name="status_filter">
                            <option value="">All Status</option>
                            <option value="{{ \App\Models\Area::STATUS_PUBLISHED }}" 
                                    {{ request('status_filter') == \App\Models\Area::STATUS_PUBLISHED ? 'selected' : '' }}>
                                Published
                            </option>
                            <option value="{{ \App\Models\Area::STATUS_UNPUBLISHED }}" 
                                    {{ request('status_filter') == \App\Models\Area::STATUS_UNPUBLISHED ? 'selected' : '' }}>
                                Unpublished
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="btn-group w-100">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-search mr-1"></i> Filter
                            </button>
                            <a href="{{ route('dashboard.areas.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times mr-1"></i> Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($areas->isEmpty())
        <div class="text-center py-4 text-muted">
            <i class="fas fa-info-circle fa-2x mb-2"></i>
            <p>
                @if(request()->hasAny(['search', 'city_filter', 'status_filter']))
                    No areas found matching your filters. 
                    <a href="{{ route('dashboard.areas.index') }}" class="text-primary">Clear filters</a> to see all areas.
                @else
                    No areas yet. Click "Add New Area" to create one.
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
                                <th class="text-center">Area Name (EN)</th>
                                <th class="text-center">Area Name (AR)</th>
                                <th class="text-center">City (EN)</th>
                                <th class="text-center">City (AR)</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Order</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($areas as $area)
                            <tr>
                                <td class="text-center align-middle">{{ $area->id }}</td>
                                <td class="text-center align-middle">{{ $area->name_en }}</td>
                                <td class="text-center align-middle" dir="rtl">{{ $area->name_ar }}</td>
                                <td class="text-center align-middle">{{ $area->city->name_en ?? 'N/A' }}</td>
                                <td class="text-center align-middle" dir="rtl">{{ $area->city->name_ar ?? 'N/A' }}</td>
                                <td class="text-center align-middle">
                                    <span class="badge rounded-pill {{ $area->status == \App\Models\Area::STATUS_PUBLISHED ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $area->status == \App\Models\Area::STATUS_PUBLISHED ? 'Published' : 'Unpublished' }}
                                    </span>
                                </td>
                                <td class="text-center align-middle">{{ $area->order }}</td>
                                <td class="text-center align-middle">
                                    <!-- Actions Dropdown -->
                                    <div class="dropdown d-inline-block">
                                        <a class="dropdown-toggle text-dark" id="dropdownMenuButton{{ $area->id }}"
                                           data-bs-toggle="dropdown" style="cursor: pointer;" aria-expanded="false" title="Actions">
                                            <i class="fas fa-cog"></i>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton{{ $area->id }}">
                                            <li>
                                                <a href="{{ route('dashboard.areas.edit', $area->id) }}" class="dropdown-item" title="Edit Area">
                                                    <i class="fas fa-edit me-2"></i>Edit
                                                </a>
                                            </li>
                                            <li>
                                                <button type="button" class="dropdown-item text-danger" onclick="confirmDelete('delete-area-{{ $area->id }}')">
                                                    <i class="fas fa-trash-alt me-2"></i>Delete
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                    <form id="delete-area-{{ $area->id }}" action="{{ route('dashboard.areas.destroy', $area->id) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-top-0">
                <div class="d-flex justify-content-end mb-0">
                    {{ $areas->appends(request()->query())->links() }}
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
    const cityFilter = document.getElementById('city_filter');
    const statusFilter = document.getElementById('status_filter');
    
    let debounceTimer;
    
    // Debounced search for text input
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() {
            document.getElementById('filterForm').submit();
        }, 500);
    });
    
    // Immediate submit for dropdowns
    cityFilter.addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
    
    statusFilter.addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
});
</script>
@endpush
@endsection