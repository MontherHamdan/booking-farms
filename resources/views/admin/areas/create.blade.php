@extends('admin.layout')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card card-primary card-outline shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title m-0 font-weight-bold text-primary">
                            Create New Area
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
                    
                    <form action="{{ route('dashboard.areas.store') }}" method="POST" id="areaForm">
                        @csrf
                        
                        <div class="form-group mb-4">
                            <label for="city_id" class="font-weight-bold">
                                City <span class="text-danger">*</span>
                            </label>
                            <select class="form-control @error('city_id') is-invalid @enderror" 
                                    id="city_id" name="city_id" required>
                                <option value="">Select a City</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city->id }}" 
                                            {{ old('city_id') == $city->id ? 'selected' : '' }}>
                                        {{ $city->name_en }} ({{ $city->name_ar }})
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
                                    <label for="status" class="font-weight-bold">
                                        Status <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control @error('status') is-invalid @enderror" 
                                            id="statuss" name="status" required>
                                        <option value="{{ \App\Models\Area::STATUS_PUBLISHED }}" 
                                                {{ old('status') == \App\Models\Area::STATUS_PUBLISHED ? 'selected' : '' }}>
                                            Published
                                        </option>
                                        <option value="{{ \App\Models\Area::STATUS_UNPUBLISHED }}" 
                                                {{ old('status') == \App\Models\Area::STATUS_UNPUBLISHED ? 'selected' : '' }}>
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
                                <i class="fas fa-save mr-2"></i> Create Area
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection