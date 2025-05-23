@extends('admin.layout')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card card-primary card-outline shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title m-0 font-weight-bold text-primary">
                            <i class="fas fa-edit mr-2"></i>Edit Feature
                        </h3>
                        <a href="{{ route('features.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Features
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

                    <form action="{{ route('features.update', $feature->id) }}" method="POST" enctype="multipart/form-data" id="featureForm">
                        @csrf
                        @method('PUT')

                        <div class="form-group mb-4">
                            <label for="icon" class="form-label fw-bold">Feature Icon</label>
                            <input type="file" data-plugins="dropify" data-height="200"
                                   class="form-control @error('icon') is-invalid @enderror" name="icon" id="icon"
                                   @if($feature->icon) data-default-file="{{ $feature->icon }}" @endif>
                            <small class="form-text text-muted">
                                Recommended size: 64x64px. Supported formats: JPG, PNG, GIF, SVG. Leave empty to keep current icon.
                            </small>
                            @error('icon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name_ar" class="font-weight-bold">Name (Arabic) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name_ar') is-invalid @enderror"
                                           id="name_ar" name="name_ar" value="{{ old('name_ar', $feature->name_ar) }}" dir="rtl" required>
                                    @error('name_ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name_en" class="font-weight-bold">Name (English) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name_en') is-invalid @enderror"
                                           id="name_en" name="name_en" value="{{ old('name_en', $feature->name_en) }}" required>
                                    @error('name_en')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="order" class="font-weight-bold">Order</label>
                            <input type="number" class="form-control @error('order') is-invalid @enderror"
                                   id="order" name="order" value="{{ old('order', $feature->order) }}"
                                   placeholder="Leave empty for automatic ordering">
                            <small class="form-text text-muted">Display order (lower numbers appear first)</small>
                            @error('order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mt-4 text-center">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="fas fa-save mr-2"></i> Update Feature
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection