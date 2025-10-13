@extends('admin.layout')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h4 class="font-weight-bold text-primary">
                <i class="mdi mdi-account-check mr-2"></i>Farm Owner Applications
            </h4>
            <p class="text-muted mb-0">Manage and verify farm owner ID images</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-left-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Applications</h6>
                            <h3 class="mb-0">{{ $stats['total'] }}</h3>
                        </div>
                        <div class="text-primary">
                            <i class="mdi mdi-file-document mdi-36px"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-left-warning">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Pending Verification</h6>
                            <h3 class="mb-0 text-warning">{{ $stats['pending'] }}</h3>
                        </div>
                        <div class="text-warning">
                            <i class="mdi mdi-clock-outline mdi-36px"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-left-success">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Verified</h6>
                            <h3 class="mb-0 text-success">{{ $stats['verified'] }}</h3>
                        </div>
                        <div class="text-success">
                            <i class="mdi mdi-check-circle mdi-36px"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-left-secondary">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Without ID Image</h6>
                            <h3 class="mb-0 text-secondary">{{ $stats['without_id'] }}</h3>
                        </div>
                        <div class="text-secondary">
                            <i class="mdi mdi-image-off mdi-36px"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard.farm-owner-applications.index') }}" id="filterForm">
                <div class="row align-items-end">
                    <div class="col-md-5">
                        <label for="search" class="form-label font-weight-bold">Search Farm Owner</label>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               placeholder="Search by name, email, or phone..."
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="status_filter" class="form-label font-weight-bold">Verification Status</label>
                        <select class="form-control" id="status_filter" name="status_filter">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status_filter') == 'pending' ? 'selected' : '' }}>
                                Pending
                            </option>
                            <option value="verified" {{ request('status_filter') == 'verified' ? 'selected' : '' }}>
                                Verified
                            </option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <div class="btn-group w-100">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-search mr-1"></i> Filter
                            </button>
                            <a href="{{ route('dashboard.farm-owner-applications.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times mr-1"></i> Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($applications->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="mdi mdi-file-document-outline mdi-48px mb-3"></i>
            <p class="h5">
                @if(request()->hasAny(['search', 'status_filter']))
                    No applications found matching your filters.
                    <a href="{{ route('dashboard.farm-owner-applications.index') }}" class="text-primary">Clear filters</a>
                @else
                    No farm owner applications with ID images yet.
                @endif
            </p>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive py-4 px-4">
                    <table class="table table-bordered table-striped table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-center">ID</th>
                                <th>Farm Owner</th>
                                <th class="text-center">Contact</th>
                                <th class="text-center">City</th>
                                <th class="text-center">ID Image</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Applied Date</th>
                                <th class="text-center">Verified Date</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($applications as $application)
                            <tr>
                                <td class="text-center align-middle">{{ $application->id }}</td>
                                <td class="align-middle">
                                    <div class="d-flex align-items-center">
                                        @if($application->user->avatar)
                                            <img src="{{ $application->user->avatar }}" 
                                                 alt="{{ $application->user->name }}" 
                                                 class="rounded-circle mr-2" 
                                                 width="40" height="40">
                                        @else
                                            @php
                                                $nameParts = explode(' ', $application->user->name);
                                                $initials = collect($nameParts)
                                                    ->filter(fn($part) => strlen($part) > 0)
                                                    ->take(2)
                                                    ->map(fn($part) => strtoupper(substr($part, 0, 1)))
                                                    ->implode('');
                                            @endphp
                                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-secondary text-white mr-2" 
                                                 style="width: 40px; height: 40px; font-size: 0.9rem;">
                                                {{ $initials }}
                                            </div>
                                        @endif
                                        <div>
                                            <strong>{{ $application->user->name }}</strong>
                                            <br>
                                            <small class="text-muted">ID: {{ $application->user->id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center align-middle">
                                    @if($application->user->email)
                                        <small class="d-block">
                                            <i class="mdi mdi-email mr-1"></i>{{ $application->user->email }}
                                        </small>
                                    @endif
                                    @if($application->user->phone)
                                        <small class="d-block">
                                            <i class="mdi mdi-phone mr-1"></i>{{ $application->user->phone }}
                                        </small>
                                    @endif
                                </td>
                                <td class="text-center align-middle">
                                    @if($application->user->city)
                                        <span class="badge bg-info text-white">
                                            {{ $application->user->city->name_en }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center align-middle">
                                    @if($application->hasIdImage())
                                        <a href="{{ $application->id_image }}" 
                                           target="_blank" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="mdi mdi-image mr-1"></i>View Image
                                        </a>
                                    @else
                                        <span class="text-muted">
                                            <i class="mdi mdi-image-off"></i> No Image
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center align-middle">
                                    @if($application->isVerified())
                                        <span class="badge rounded-pill bg-success">
                                            <i class="mdi mdi-check-circle mr-1"></i>Verified
                                        </span>
                                    @else
                                        <span class="badge rounded-pill bg-warning">
                                            <i class="mdi mdi-clock-outline mr-1"></i>Pending
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center align-middle">
                                    <small>{{ $application->applied_at->format('Y-m-d') }}</small>
                                    <br>
                                    <small class="text-muted">{{ $application->applied_at->diffForHumans() }}</small>
                                </td>
                                <td class="text-center align-middle">
                                    @if($application->verified_at)
                                        <small>{{ $application->verified_at->format('Y-m-d') }}</small>
                                        <br>
                                        <small class="text-muted">{{ $application->verified_at->diffForHumans() }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center align-middle">
                                    <div class="dropdown d-inline-block">
                                        <a class="dropdown-toggle text-dark" 
                                           id="dropdownMenuButton{{ $application->id }}"
                                           data-bs-toggle="dropdown" 
                                           style="cursor: pointer;" 
                                           aria-expanded="false" 
                                           title="Actions">
                                            <i class="fas fa-cog"></i>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton{{ $application->id }}">
                                            <li>
                                                <a href="{{ route('dashboard.farm-owner-applications.show', $application->id) }}" 
                                                   class="dropdown-item">
                                                    <i class="mdi mdi-eye me-2"></i>View Details
                                                </a>
                                            </li>
                                            @if($application->hasIdImage() && !$application->isVerified())
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button type="button" 
                                                        class="dropdown-item text-success" 
                                                        onclick="confirmVerify('verify-form-{{ $application->id }}')">
                                                    <i class="mdi mdi-check-circle me-2"></i>Verify ID Image
                                                </button>
                                            </li>
                                            @endif
                                        </ul>
                                    </div>

                                    @if($application->hasIdImage() && !$application->isVerified())
                                        <form id="verify-form-{{ $application->id }}" 
                                              action="{{ route('dashboard.farm-owner-applications.verify', $application->id) }}" 
                                              method="POST" 
                                              style="display: none;">
                                            @csrf
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
                        Showing {{ $applications->firstItem() }} to {{ $applications->lastItem() }} of {{ $applications->total() }} applications
                    </div>
                    <div>
                        {{ $applications->appends(request()->query())->links() }}
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
    
    let debounceTimer;
    
    // Debounced search
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() {
            document.getElementById('filterForm').submit();
        }, 500);
    });
    
    // Immediate submit for dropdown
    statusFilter.addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
});

// Confirm verification
function confirmVerify(formId) {
    if (confirm('Are you sure you want to verify this ID image?')) {
        document.getElementById(formId).submit();
    }
}
</script>

<style>
.border-left-primary { border-left: 4px solid #007bff; }
.border-left-warning { border-left: 4px solid #ffc107; }
.border-left-success { border-left: 4px solid #28a745; }
.border-left-secondary { border-left: 4px solid #6c757d; }
</style>
@endpush
@endsection