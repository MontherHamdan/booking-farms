@extends('admin.layout')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h4 class="font-weight-bold text-primary">
                Edit Farm: {{ $farm->name_en ?: $farm->name_ar }}
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.farms.index') }}">Farms</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.farms.show', $farm->id) }}">{{ $farm->name_en ?: $farm->name_ar }}</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
        <div class="col-auto">
            <a href="{{ route('dashboard.farms.show', $farm->id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back to Farm
            </a>
        </div>
    </div>

    <form action="{{ route('dashboard.farms.update', $farm->id) }}" method="POST" id="farmEditForm">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- Main Content -->
            <div class="col-md-8">
                <!-- Basic Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Basic Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="name_en" class="form-label font-weight-bold">
                                        English Name
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('name_en') is-invalid @enderror" 
                                           id="name_en" 
                                           name="name_en" 
                                           value="{{ old('name_en', $farm->name_en) }}"
                                           placeholder="Farm name in English">
                                    @error('name_en')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="name_ar" class="form-label font-weight-bold">
                                        Arabic Name
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('name_ar') is-invalid @enderror" 
                                           id="name_ar" 
                                           name="name_ar" 
                                           value="{{ old('name_ar', $farm->name_ar) }}"
                                           placeholder="اسم المزرعة بالعربية"
                                           dir="rtl">
                                    @error('name_ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="guest_count" class="form-label font-weight-bold">
                                        Guest Capacity <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('guest_count') is-invalid @enderror" 
                                           id="guest_count" 
                                           name="guest_count" 
                                           value="{{ old('guest_count', $farm->guest_count) }}"
                                           min="1" 
                                           required>
                                    @error('guest_count')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="deposit_rate" class="form-label font-weight-bold">
                                        Deposit Rate (%)
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('deposit_rate') is-invalid @enderror" 
                                           id="deposit_rate" 
                                           name="deposit_rate" 
                                           value="{{ old('deposit_rate', $farm->deposit_rate) }}"
                                           min="0" 
                                           max="100"
                                           step="0.01">
                                    @error('deposit_rate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Percentage of total booking amount required as deposit
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="description_en" class="form-label font-weight-bold">
                                English Description
                            </label>
                            <textarea class="form-control @error('description_en') is-invalid @enderror" 
                                      id="description_en" 
                                      name="description_en" 
                                      rows="4"
                                      placeholder="Describe the farm in English">{{ old('description_en', $farm->description_en) }}</textarea>
                            @error('description_en')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="description_ar" class="form-label font-weight-bold">
                                Arabic Description
                            </label>
                            <textarea class="form-control @error('description_ar') is-invalid @enderror" 
                                      id="description_ar" 
                                      name="description_ar" 
                                      rows="4"
                                      placeholder="وصف المزرعة بالعربية"
                                      dir="rtl">{{ old('description_ar', $farm->description_ar) }}</textarea>
                            @error('description_ar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Location Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Location</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="city_id" class="form-label font-weight-bold">
                                        City <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control @error('city_id') is-invalid @enderror" 
                                            id="city_id" 
                                            name="city_id" 
                                            required>
                                        <option value="">Select a City</option>
                                        @foreach($cities as $city)
                                            <option value="{{ $city->id }}" 
                                                    {{ old('city_id', $farm->city_id) == $city->id ? 'selected' : '' }}>
                                                {{ $city->name_en }} ({{ $city->name_ar }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('city_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="area_id" class="form-label font-weight-bold">
                                        Area <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control @error('area_id') is-invalid @enderror" 
                                            id="area_id" 
                                            name="area_id" 
                                            required>
                                        <option value="">Select an Area</option>
                                        @foreach($areas as $area)
                                            <option value="{{ $area->id }}" 
                                                    {{ old('area_id', $farm->area_id) == $area->id ? 'selected' : '' }}>
                                                {{ $area->name_en }} ({{ $area->name_ar }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('area_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="latitude" class="form-label font-weight-bold">
                                        Latitude
                                    </label>
                                    <input type="number" 
                                           step="any" 
                                           class="form-control @error('latitude') is-invalid @enderror" 
                                           id="latitude" 
                                           name="latitude" 
                                           value="{{ old('latitude', $farm->latitude) }}"
                                           placeholder="31.9500">
                                    @error('latitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="longitude" class="form-label font-weight-bold">
                                        Longitude
                                    </label>
                                    <input type="number" 
                                           step="any" 
                                           class="form-control @error('longitude') is-invalid @enderror" 
                                           id="longitude" 
                                           name="longitude" 
                                           value="{{ old('longitude', $farm->longitude) }}"
                                           placeholder="35.9333">
                                    @error('longitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        @if($farm->hasCoordinates())
                        <div class="alert alert-info">
                            <i class="fas fa-map-pin me-2"></i>
                            <strong>Current Coordinates:</strong> {{ $farm->coordinates }}
                            <a href="https://www.google.com/maps?q={{ $farm->latitude }},{{ $farm->longitude }}" 
                               target="_blank" class="btn btn-sm btn-outline-primary ms-2">
                                <i class="fas fa-external-link-alt"></i> View on Maps
                            </a>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Features -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-star me-2"></i>Features</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($features as $feature)
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="features[]" 
                                           value="{{ $feature->id }}" 
                                           id="feature_{{ $feature->id }}"
                                           {{ in_array($feature->id, old('features', $farm->features->pluck('id')->toArray())) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="feature_{{ $feature->id }}">
                                        @if($feature->icon)
                                            <i class="{{ $feature->icon }} me-1"></i>
                                        @endif
                                        {{ $feature->name_en }}
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @error('features')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Status Management -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Status Management</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="status" class="form-label font-weight-bold">
                                Farm Status <span class="text-danger">*</span>
                            </label>
                            <select class="form-control @error('status') is-invalid @enderror" 
                                    id="status" 
                                    name="status" 
                                    required>
                                <option value="pending" {{ old('status', $farm->status) == 'pending' ? 'selected' : '' }}>
                                    Pending Approval
                                </option>
                                <option value="active" {{ old('status', $farm->status) == 'active' ? 'selected' : '' }}>
                                    Active
                                </option>
                                <option value="rejected" {{ old('status', $farm->status) == 'rejected' ? 'selected' : '' }}>
                                    Rejected
                                </option>
                                <option value="disabled" {{ old('status', $farm->status) == 'disabled' ? 'selected' : '' }}>
                                    Disabled
                                </option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @php
                            $statusConfig = [
                                'pending' => ['class' => 'alert-warning', 'icon' => 'fas fa-clock', 'message' => 'Farm is waiting for approval'],
                                'active' => ['class' => 'alert-success', 'icon' => 'fas fa-check-circle', 'message' => 'Farm is active and visible to users'],
                                'rejected' => ['class' => 'alert-danger', 'icon' => 'fas fa-times-circle', 'message' => 'Farm has been rejected'],
                                'disabled' => ['class' => 'alert-secondary', 'icon' => 'fas fa-ban', 'message' => 'Farm is disabled']
                            ];
                            $currentConfig = $statusConfig[$farm->status] ?? ['class' => 'alert-dark', 'icon' => 'fas fa-question', 'message' => 'Unknown status'];
                        @endphp

                        <div class="alert {{ $currentConfig['class'] }} small">
                            <i class="{{ $currentConfig['icon'] }} me-1"></i>
                            <strong>Current:</strong> {{ $currentConfig['message'] }}
                        </div>
                    </div>
                </div>

                <!-- Farm Statistics -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Statistics</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="border rounded p-2">
                                    <h6 class="text-primary mb-1">{{ $farm->bookings()->count() }}</h6>
                                    <small class="text-muted">Total Bookings</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="border rounded p-2">
                                    <h6 class="text-success mb-1">{{ $farm->bookings()->where('booking_status', 'confirmed')->count() }}</h6>
                                    <small class="text-muted">Confirmed</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="border rounded p-2">
                                    <h6 class="text-info mb-1">${{ number_format($farm->bookings()->where('booking_status', 'confirmed')->sum('total_amount'), 0) }}</h6>
                                    <small class="text-muted">Revenue</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="border rounded p-2">
                                    <h6 class="text-warning mb-1">{{ $farm->ratings()->count() }}</h6>
                                    <small class="text-muted">Reviews</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-link me-2"></i>Quick Links</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('dashboard.farms.show', $farm->id) }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-eye me-2"></i>View Farm Details
                            </a>
                            <button type="button" class="btn btn-outline-info btn-sm" onclick="viewBookings()">
                                <i class="fas fa-calendar-check me-2"></i>View Bookings
                            </button>
                            <button type="button" class="btn btn-outline-warning btn-sm" onclick="viewRatings()">
                                <i class="fas fa-star me-2"></i>View Ratings
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Save Actions -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                            <a href="{{ route('dashboard.farms.show', $farm->id) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                        
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Last updated: {{ $farm->updated_at->format('M d, Y \a\t g:i A') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Load areas when city changes
    $('#city_id').on('change', function() {
        const cityId = $(this).val();
        const areaSelect = $('#area_id');
        
        // Clear current options
        areaSelect.html('<option value="">Loading areas...</option>');
        
        if (cityId) {
            fetch(`/dashboard/farms/cities/${cityId}/areas`)
                .then(response => response.json())
                .then(areas => {
                    areaSelect.html('<option value="">Select an Area</option>');
                    areas.forEach(area => {
                        areaSelect.append(`<option value="${area.id}">${area.name_en} (${area.name_ar})</option>`);
                    });
                    
                    // Restore selected area if editing
                    const selectedAreaId = '{{ old("area_id", $farm->area_id) }}';
                    if (selectedAreaId) {
                        areaSelect.val(selectedAreaId);
                    }
                })
                .catch(error => {
                    console.error('Error loading areas:', error);
                    areaSelect.html('<option value="">Error loading areas</option>');
                });
        } else {
            areaSelect.html('<option value="">Select a City first</option>');
        }
    });

    // Coordinate validation
    $('#latitude').on('input', function() {
        const lat = parseFloat($(this).val());
        if (lat < -90 || lat > 90) {
            $(this).addClass('is-invalid');
            $(this).siblings('.invalid-feedback').text('Latitude must be between -90 and 90 degrees');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    
    $('#longitude').on('input', function() {
        const lng = parseFloat($(this).val());
        if (lng < -180 || lng > 180) {
            $(this).addClass('is-invalid');
            $(this).siblings('.invalid-feedback').text('Longitude must be between -180 and 180 degrees');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // Form validation
    $('#farmEditForm').on('submit', function(e) {
        // Check if at least one name is provided
        const nameEn = $('#name_en').val().trim();
        const nameAr = $('#name_ar').val().trim();
        
        if (!nameEn && !nameAr) {
            e.preventDefault();
            alert('Please provide at least one name (English or Arabic)');
            return false;
        }
        
        return true;
    });
});

// Quick action functions
function viewBookings() {
    window.location.href = `/dashboard/bookings?farm_id={{ $farm->id }}`;
}

function viewRatings() {
    alert('Ratings management coming soon');
}
</script>
@endpush
@endsection