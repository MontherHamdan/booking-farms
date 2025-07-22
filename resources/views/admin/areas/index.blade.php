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
                                    {{ $city->name_en }} ({{ $city->name_ar }}) - {{ $city->areas_count }} areas
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
                                <th class="text-center">Coordinates</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Order</th>
                                <th class="text-center">
                                    <i class="fas fa-seedling mr-1"></i>Farms
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'farms_count', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-muted ml-1" title="Sort by farm count">
                                        <i class="fas fa-sort"></i>
                                    </a>
                                </th>
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
                                    @if($area->hasCoordinates())
                                        <div>
                                            <small class="text-muted d-block" title="Coordinates: {{ $area->coordinates }}">
                                                <i class="fas fa-map-pin mr-1"></i>
                                                {{ $area->coordinates }}
                                            </small>
                                            <a href="https://www.google.com/maps?q={{ $area->latitude }},{{ $area->longitude }}" 
                                               target="_blank" 
                                               class="btn btn-outline-secondary btn-sm py-0 px-1 mt-1"
                                               style="font-size: 0.7rem;"
                                               title="View on Google Maps">
                                                <i class="fas fa-external-link-alt"></i> Maps
                                            </a>
                                        </div>
                                    @else
                                        <small class="text-muted">
                                            <i class="fas fa-map-pin mr-1"></i>
                                            <em>No coordinates</em>
                                        </small>
                                    @endif
                                </td>
                                <td class="text-center align-middle">
                                    <span class="badge rounded-pill {{ $area->status == \App\Models\Area::STATUS_PUBLISHED ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $area->status == \App\Models\Area::STATUS_PUBLISHED ? 'Published' : 'Unpublished' }}
                                    </span>
                                </td>
                                <td class="text-center align-middle">{{ $area->order }}</td>
                                <td class="text-center align-middle">
                                    @if($area->farms_count > 0)
                                        <span class="badge bg-info text-white">
                                            <i class="fas fa-seedling mr-1"></i>{{ $area->farms_count }}
                                        </span>
                                    @else
                                        <span class="text-muted small">
                                            <i class="fas fa-minus"></i> No farms
                                        </span>
                                    @endif
                                </td>
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
                                            @if($area->hasCoordinates())
                                            <li>
                                                <a href="https://www.google.com/maps?q={{ $area->latitude }},{{ $area->longitude }}" 
                                                   target="_blank" class="dropdown-item text-info">
                                                    <i class="fas fa-map-marker-alt me-2"></i>View on Maps
                                                </a>
                                            </li>
                                            @endif
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button type="button" 
                                                        class="dropdown-item {{ $area->farms_count > 0 ? 'text-muted' : 'text-danger' }}" 
                                                        onclick="{{ $area->farms_count > 0 ? 'alert(\'Cannot delete area that contains farms!\')' : 'confirmDelete(\'delete-area-' . $area->id . '\')' }}"
                                                        {{ $area->farms_count > 0 ? 'disabled' : '' }}
                                                        title="{{ $area->farms_count > 0 ? 'Cannot delete: contains ' . $area->farms_count . ' farm(s)' : 'Delete area' }}">
                                                    <i class="fas fa-trash-alt me-2"></i>Delete
                                                    @if($area->farms_count > 0)
                                                        <small class="text-muted">({{ $area->farms_count }} farms)</small>
                                                    @endif
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                    @if($area->farms_count == 0)
                                        <form id="delete-area-{{ $area->id }}" action="{{ route('dashboard.areas.destroy', $area->id) }}" method="POST" style="display: none;">
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
                        Showing {{ $areas->firstItem() }} to {{ $areas->lastItem() }} of {{ $areas->total() }} areas
                        @if($areas->sum('farms_count') > 0)
                            | Total farms: {{ $areas->sum('farms_count') }}
                        @endif
                        @if($areas->where('latitude', '!=', null)->count() > 0)
                            | {{ $areas->where('latitude', '!=', null)->count() }} have coordinates
                        @endif
                    </div>
                    <div>
                        {{ $areas->appends(request()->query())->links() }}
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