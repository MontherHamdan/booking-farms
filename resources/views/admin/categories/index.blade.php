@extends('admin.layout')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="d-flex justify-content-between align-items-center p-3">
                    <h1 class="mb-0 text-primary">Categories</h1>
                    <a href="{{ route('categories.create') }}" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i> Add New Category
                    </a>
                </div>

                <div class="card-body">
                    <table id="responsive-datatable" class="table table-striped table-bordered dt-responsive ">
                        <thead>
                            <tr>
                                <th class="text-center">ID</th>
                                <th class="text-center">Category Name</th>
                                <th class="text-center">Category Arabic Name</th>
                                <th class="text-center">Category Type</th>
                                <th class="text-center">Subcategories Count</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($categories as $category)
                                <tr>
                                    <td class="text-center">{{ $category->id }}</td>
                                    <td class="text-center">{{ $category->name }}</td>
                                    <td class="text-center">{{ $category->arabic_name }}</td>
                                    <td class="text-center">{{ $category->type }}</td>
                                    <td class="text-center">{{ $category->subcategories->count() }}</td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <a class=" dropdown-toggle" id="dropdownMenuButton{{ $category->id }}"
                                                data-bs-toggle="dropdown" style="cursor: pointer" aria-expanded="false">
                                                <i class="fas fa-ellipsis-h"></i>
                                            </a>
                                            <ul class="dropdown-menu"
                                                aria-labelledby="dropdownMenuButton{{ $category->id }}">
                                                <li>
                                                    <a href="{{ route('categories.edit', $category) }}"
                                                        class="dropdown-item" title="Edit Category">
                                                        <i class="fas fa-edit me-2"></i>Edit
                                                    </a>
                                                </li>
                                                <li>
                                                    <form action="{{ route('categories.destroy', $category) }}"
                                                        method="POST" id="delete-form-{{ $category->id }}">
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
                                    <td colspan="6" class="text-center text-muted">No categories found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
