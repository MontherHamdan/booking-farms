@extends('admin.layout')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <!-- Header Section -->
                <div class="d-flex justify-content-between align-items-center p-3">
                    <h1 class="mb-0 text-primary">Book Designs</h1>
                    <a href="{{ route('book-designs.create') }}" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i> Add New Design
                    </a>
                </div>

                <!-- Table Section -->
                <div class="card-body">

                    <!-- DataTable -->
                    <table id="responsive-datatable" class="table table-striped table-bordered dt-responsive ">
                        <thead>
                            <tr>
                                <th class="text-center">ID</th>
                                <th class="text-center">Image</th>
                                <th class="text-center">Category</th>
                                <th class="text-center">Subcategory</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($bookDesigns as $design)
                                <tr>
                                    <td class="text-center">
                                        {{ $design->id }}
                                    </td>
                                    <td class="text-center">
                                        <img class="img-fluid img-thumbnail rounded-circle" src="{{ $design->image }}"
                                            alt="Design Image" width="70" height="70">
                                    </td>
                                    <td class="text-center">{{ $design->category->arabic_name ?? 'N/A'}}</td>
                                    <td class="text-center">{{ $design->subCategory->arabic_name ?? 'N/A' }}</td>
                                    <td class="text-center">
                                        <!-- Actions Dropdown -->
                                        <div class="dropdown">
                                            <a class="dropdown-toggle" id="dropdownMenuButton{{ $design->id ?? 'N/A'}}"
                                                data-bs-toggle="dropdown" style="cursor: pointer;" aria-expanded="false"
                                                title="Actions">
                                                <i class="fas fa-ellipsis-h"></i>
                                            </a>
                                            <ul class="dropdown-menu dropdown-menu-end"
                                                aria-labelledby="dropdownMenuButton{{ $design->id ?? 'N/A' }}">
                                                <!-- Edit Action -->
                                                <li>
                                                    <a href="{{ route('book-designs.edit', $design) }}"
                                                        class="dropdown-item" title="Edit Design">
                                                        <i class="fas fa-edit me-2"></i>Edit
                                                    </a>
                                                </li>
                                                <!-- Delete Action -->
                                                <li>
                                                    <form action="{{ route('book-designs.destroy', $design) }}"
                                                        method="POST" id="delete-form-{{ $design->id }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button"
                                                            class="dropdown-item text-danger sa-warning-btn">
                                                            <i class="fas fa-trash me-2"></i>Delete
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No book designs found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
