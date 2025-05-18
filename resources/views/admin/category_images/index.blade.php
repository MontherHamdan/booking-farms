@extends('admin.layout')

@section('content')
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Category Images</h1>
        <a href="{{ route('category-images.create') }}" class="btn btn-success">Create Category Image</a>
    </div>

    <div class="row">
        @forelse($images as $image)
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-img-top d-flex align-items-center justify-content-center" style="height: 200px; background: #f8f9fa;">
                        <img src="{{ $image->image_path }}" alt="{{ $image->name_en }}" 
                             style="max-height: 100%; max-width: 100%; object-fit: contain;" loading="lazy">
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">{{ $image->name_en }}</h5>
                        <p class="card-text">{{ $image->name_ar }}</p>
                    </div>
                    <!-- Custom footer for action buttons -->
                    <div class="p-3 border-top d-flex justify-content-between">
                        <a href="{{ route('category-images.edit', $image->id) }}" class="btn btn-sm btn-primary">Edit</a>
                        <form action="{{ route('category-images.destroy', $image->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this image?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <p class="text-center">No category images found.</p>
            </div>
        @endforelse
    </div>

    <div class="d-flex justify-content-center">
        {{ $images->links() }}
    </div>
</div>
@endsection
