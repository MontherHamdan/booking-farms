@extends('admin.layout')

@section('content')
<div class="container my-4">
    <h1 class="mb-4">Create Category Image</h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('category-images.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="name_en" class="form-label">Name (English)</label>
                    <input type="text" name="name_en" id="name_en" class="form-control" value="{{ old('name_en') }}" required>
                    @error('name_en')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="name_ar" class="form-label">Name (Arabic)</label>
                    <input type="text" name="name_ar" id="name_ar" class="form-control" value="{{ old('name_ar') }}" required>
                    @error('name_ar')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="image_path" class="form-label">Image</label>
                    <input type="file" name="image_path" id="image_path" class="form-control" required>
                    @error('image_path')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Create Category Image</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
