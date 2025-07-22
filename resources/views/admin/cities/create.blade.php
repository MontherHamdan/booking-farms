@extends('admin.layout')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card card-primary card-outline shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title m-0 font-weight-bold text-primary">
                            Create New City
                        </h3>
                        <a href="{{ route('dashboard.cities.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Cities
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
                    
                    <form action="{{ route('dashboard.cities.store') }}" method="POST" enctype="multipart/form-data" id="cityForm">
                        @csrf
                        
                        <div class="form-group mb-4">
                            <label for="image" class="form-label fw-bold">City Image <span class="text-danger">*</span></label>
                            <input type="file" data-plugins="dropify" data-height="200"
                                class="form-control @error('image') is-invalid @enderror" name="image" id="image"
                                required>
                            <small class="form-text text-muted">
                                Recommended size: 800x600 pixels, max size: 2MB
                            </small>
                            @error('image')
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
                                           id="name_ar" name="name_ar" value="{{ old('name_ar') }}" dir="rtl" required>
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
                                           id="name_en" name="name_en" value="{{ old('name_en') }}" required>
                                    @error('name_en')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="description_ar" class="font-weight-bold">Arabic Description</label>
                                    <textarea class="form-control @error('description_ar') is-invalid @enderror" 
                                              id="description_ar" name="description_ar" rows="4" dir="rtl" 
                                              placeholder="Enter city description in Arabic...">{{ old('description_ar') }}</textarea>
                                    <small class="form-text text-muted">Maximum 1000 characters</small>
                                    @error('description_ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="description_en" class="font-weight-bold">English Description</label>
                                    <textarea class="form-control @error('description_en') is-invalid @enderror" 
                                              id="description_en" name="description_en" rows="4" 
                                              placeholder="Enter city description in English...">{{ old('description_en') }}</textarea>
                                    <small class="form-text text-muted">Maximum 1000 characters</small>
                                    @error('description_en')
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
                                           id="latitude" name="latitude" value="{{ old('latitude') }}" 
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
                                           id="longitude" name="longitude" value="{{ old('longitude') }}" 
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
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status" class="font-weight-bold">
                                        Status <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control @error('status') is-invalid @enderror" 
                                            id="statuss" name="status" required>
                                        <option value="{{ \App\Models\City::STATUS_PUBLISHED }}" 
                                                {{ old('status') == \App\Models\City::STATUS_PUBLISHED ? 'selected' : '' }}>
                                            Published
                                        </option>
                                        <option value="{{ \App\Models\City::STATUS_UNPUBLISHED }}" 
                                                {{ old('status') == \App\Models\City::STATUS_UNPUBLISHED ? 'selected' : '' }}>
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
                                           id="order" name="order" value="{{ old('order') }}" 
                                           placeholder="Leave empty for automatic ordering">
                                    <small class="form-text text-muted">
                                        City display order (lower numbers appear first)
                                    </small>
                                    @error('order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4 text-center">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="fas fa-save mr-2"></i> Create City
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
    
    // Optional: Add coordinate validation
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