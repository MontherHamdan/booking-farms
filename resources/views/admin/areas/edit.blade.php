@extends('admin.layout')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card card-primary card-outline shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title m-0 font-weight-bold text-primary">
                            <i class="fas fa-edit mr-2"></i>Edit Area
                        </h3>
                        <a href="{{ route('dashboard.areas.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Areas
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            {{ session('error') }}
                        </div>
                    @endif
                    
                    <form action="{{ route('dashboard.areas.update', $area->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-group mb-4">
                            <label for="city_id" class="font-weight-bold">
                                City <span class="text-danger">*</span>
                            </label>
                            <select class="form-control @error('city_id') is-invalid @enderror" 
                                    id="city_id" name="city_id" required>
                                <option value="">Select a City</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city->id }}" 
                                            {{ old('city_id', $area->city_id) == $city->id ? 'selected' : '' }}
                                            @if($city->hasCoordinates())
                                                data-city-lat="{{ $city->latitude }}" 
                                                data-city-lng="{{ $city->longitude }}"
                                            @endif
                                            >
                                        {{ $city->name_en }} ({{ $city->name_ar }})
                                        {{-- @if($city->hasCoordinates())
                                            - <small class="text-muted">{{ $city->coordinates }}</small>
                                        @endif --}}
                                    </option>
                                @endforeach
                            </select>
                            @error('city_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name_ar" class="font-weight-bold">
                                        Arabic Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('name_ar') is-invalid @enderror" 
                                           id="name_ar" name="name_ar" value="{{ old('name_ar', $area->name_ar) }}" dir="rtl" required>
                                    @error('name_ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name_en" class="font-weight-bold">
                                        English Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('name_en') is-invalid @enderror" 
                                           id="name_en" name="name_en" value="{{ old('name_en', $area->name_en) }}" required>
                                    @error('name_en')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Coordinates Section -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="latitude" class="font-weight-bold">
                                        Latitude
                                        <i class="fas fa-info-circle text-muted" 
                                           title="Decimal degrees format (e.g., 31.9500)" 
                                           data-toggle="tooltip"></i>
                                    </label>
                                    <input type="number" step="any" 
                                           class="form-control @error('latitude') is-invalid @enderror" 
                                           id="latitude" name="latitude" 
                                           value="{{ old('latitude', $area->latitude) }}" 
                                           placeholder="31.9500">
                                    <small class="form-text text-muted">
                                        Decimal degrees format (e.g., 31.9500 for Amman)
                                    </small>
                                    @error('latitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="longitude" class="font-weight-bold">
                                        Longitude
                                        <i class="fas fa-info-circle text-muted" 
                                           title="Decimal degrees format (e.g., 35.9333)" 
                                           data-toggle="tooltip"></i>
                                    </label>
                                    <input type="number" step="any" 
                                           class="form-control @error('longitude') is-invalid @enderror" 
                                           id="longitude" name="longitude" 
                                           value="{{ old('longitude', $area->longitude) }}" 
                                           placeholder="35.9333">
                                    <small class="form-text text-muted">
                                        Decimal degrees format (e.g., 35.9333 for Amman)
                                    </small>
                                    @error('longitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        @if($area->hasCoordinates())
                        <div class="row mb-3 mt-3">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-map-marker-alt mr-2"></i>
                                    <strong>Current Coordinates:</strong> {{ $area->coordinates }}
                                    <a href="https://www.google.com/maps?q={{ $area->latitude }},{{ $area->longitude }}" 
                                       target="_blank" class="btn btn-sm btn-outline-primary ml-2">
                                        <i class="fas fa-external-link-alt mr-1"></i> View on Maps
                                    </a>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="row mb-3 mt-3">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-lightbulb mr-2"></i>
                                    <strong>Tip:</strong> You can use the city coordinates as reference.
                                    @if($area->city && $area->city->hasCoordinates())
                                        <strong>City coordinates:</strong> {{ $area->city->coordinates }}
                                        <button type="button" class="btn btn-sm btn-outline-primary ml-2" id="useCityCoordinates">
                                            <i class="fas fa-copy mr-1"></i> Use City Coordinates
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status" class="font-weight-bold">
                                        Status <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control @error('status') is-invalid @enderror" 
                                            id="statuss" name="status" required>
                                        <option value="{{ \App\Models\Area::STATUS_PUBLISHED }}" 
                                                {{ old('status', $area->status) == \App\Models\Area::STATUS_PUBLISHED ? 'selected' : '' }}>
                                            Published
                                        </option>
                                        <option value="{{ \App\Models\Area::STATUS_UNPUBLISHED }}" 
                                                {{ old('status', $area->status) == \App\Models\Area::STATUS_UNPUBLISHED ? 'selected' : '' }}>
                                            Unpublished
                                        </option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="order" class="font-weight-bold">Order</label>
                                    <input type="number" class="form-control @error('order') is-invalid @enderror" 
                                           id="order" name="order" value="{{ old('order', $area->order) }}">
                                    <small class="form-text text-muted">
                                        Area display order within the city (lower numbers appear first)
                                    </small>
                                    @error('order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4 text-center">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="fas fa-save mr-2"></i> Update Area
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Use city coordinates button (for areas without coordinates)
    $('#useCityCoordinates').on('click', function() {
        @if($area->city && $area->city->hasCoordinates())
            $('#latitude').val({{ $area->city->latitude }});
            $('#longitude').val({{ $area->city->longitude }});
        @endif
    });
    
    // Handle city selection to update coordinates reference
    $('#city_id').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const cityLat = selectedOption.data('city-lat');
        const cityLng = selectedOption.data('city-lng');
        
        // You could add dynamic city coordinate display here if needed
    });
    
    // Coordinate validation
    $('#latitude').on('input', function() {
        var lat = parseFloat($(this).val());
        if (lat < -90 || lat > 90) {
            $(this).addClass('is-invalid');
            $(this).siblings('.invalid-feedback').text('Latitude must be between -90 and 90 degrees');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    
    $('#longitude').on('input', function() {
        var lng = parseFloat($(this).val());
        if (lng < -180 || lng > 180) {
            $(this).addClass('is-invalid');
            $(this).siblings('.invalid-feedback').text('Longitude must be between -180 and 180 degrees');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
});
</script>
@endpush
@endsection