@extends('admin.layout')

@section('content')
    <!-- Header Section -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center p-3">
                <h1 class="mb-0 text-primary">Manage Premade Categories</h1>
                <a href="{{ route('premade-categories.create') }}" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i> Add New Category
                </a>
            </div>
        </div>
    </div>

    <!-- Cards Grid -->
    <div class="row">
        @forelse($premadeCategories as $category)
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card shadow-sm mb-4">
                    <div class="card-body text-center">
                        <!-- Category Image or Placeholder -->
                        @if ($category->image)
                            <img src="{{ $category->image->image_path }}" alt="Category Image"
                                class="rounded-circle mb-3 mx-auto" style="width: 80px; height: 80px; object-fit: cover;">
                        @else
                            <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center mb-3 mx-auto" style="width: 80px; height: 80px;">
                                <span class="text-white small">{{ $category->name }}</span>
                            </div>
                        @endif

                        <!-- Category Details -->
                        <h5 class="card-title mb-1">{{ $category->name }}</h5>
                        <p class="card-text">
                            <span class="badge {{ $category->status == 'active' ? 'bg-success' : 'bg-secondary' }}">
                                {{ ucfirst($category->status) }}
                            </span>
                        </p>
                    </div>
                    <div class="card-footer bg-transparent border-top-0 text-center">
                        <!-- Icon-only Edit Button -->
                        <a href="{{ route('premade-categories.edit', $category) }}" class="btn btn-outline-primary btn-sm me-1" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <!-- Icon-only Delete Button -->
                        <form action="{{ route('premade-categories.destroy', $category) }}" method="POST" class="d-inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-outline-danger btn-sm sa-warning-btn" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <p class="text-center text-muted">No premade categories found.</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-end">
                {{ $premadeCategories->links() }}
            </div>
        </div>
    </div>
@endsection