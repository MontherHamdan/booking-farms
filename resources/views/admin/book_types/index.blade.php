@extends('admin.layout')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <!-- Header Section -->
                <div class="d-flex justify-content-between align-items-center p-3">
                    <h1 class="mb-0 text-primary">Book Types</h1>
                    <a href="{{ route('book-types.create') }}" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i> Add New Book Type
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
                                <th class="text-center">Arabic Name</th>
                                <th class="text-center">English Name</th>
                                <th class="text-center">Price</th>
                                <th class="text-center">Description (EN)</th>
                                <th class="text-center">Description (AR)</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($bookTypes as $bookType)
                                <tr>
                                    <td class="text-center">{{ $bookType->id }}</td>
                                    <td class="text-center">
                                        <img class="img-fluid img-thumbnail rounded-circle" src="{{ $bookType->image }}"
                                            alt="Book Type" width="70" height="70">
                                    </td>
                                    <td class="text-center">{{ $bookType->name_ar }}</td>
                                    <td class="text-center">{{ $bookType->name_en }}</td>
                                    <td class="text-center">{{ $bookType->price }}</td>
                                    <td class="text-center">{{ $bookType->description_en }}</td>
                                    <td class="text-center">{{ $bookType->description_ar }}</td>
                                    <td class="text-center">
                                        <!-- Actions Dropdown -->
                                        <div class="dropdown">
                                            <a class="dropdown-toggle" id="dropdownMenuButton{{ $bookType->id }}"
                                                data-bs-toggle="dropdown" style="cursor: pointer;" aria-expanded="false"
                                                title="Actions">
                                                <i class="fas fa-ellipsis-h"></i>
                                            </a>
                                            <ul class="dropdown-menu dropdown-menu-end"
                                                aria-labelledby="dropdownMenuButton{{ $bookType->id }}">
                                                <!-- Edit Action -->
                                                <li>
                                                    <a href="{{ route('book-types.edit', $bookType) }}"
                                                        class="dropdown-item" title="Edit Book Type">
                                                        <i class="fas fa-edit me-2"></i>Edit
                                                    </a>
                                                </li>
                                                <!-- Sub Media Action -->
                                                <li>
                                                    <button type="button" class="dropdown-item" data-bs-toggle="modal"
                                                        data-bs-target="#subMediaModal{{ $bookType->id }}">
                                                        <i class="fas fa-images me-2"></i>Manage Sub Media
                                                    </button>
                                                </li>
                                                <!-- Delete Action -->
                                                <li>
                                                    <form action="{{ route('book-types.destroy', $bookType) }}"
                                                        method="POST" id="delete-form-{{ $bookType->id }}">
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

                                <!-- Sub Media Modal -->
                                <div class="modal fade" id="subMediaModal{{ $bookType->id }}" tabindex="-1"
                                    aria-labelledby="subMediaModalLabel{{ $bookType->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="subMediaModalLabel{{ $bookType->id }}">
                                                    Manage Sub Media for {{ $bookType->description_en }}
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    @foreach ($bookType->subMedia as $subMedia)
                                                        <div class="col-md-4 text-center mb-3">
                                                            @if ($subMedia->type === 'image')
                                                                <img class="img-fluid img-thumbnail"
                                                                    src="{{ $subMedia->media }}" alt="Sub Media"
                                                                    width="150">
                                                            @else
                                                                <video class="img-fluid" width="150" controls>
                                                                    <source src="{{ $subMedia->media }}" type="video/mp4">
                                                                </video>
                                                            @endif
                                                            <form
                                                                action="{{ route('book-type-sub-media.destroy', $subMedia) }}"
                                                                method="POST" class="mt-2">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button class="btn btn-sm btn-danger">Delete</button>
                                                            </form>
                                                        </div>
                                                    @endforeach
                                                </div>

                                                <!-- Add Sub Media Form -->
                                                <form action="{{ route('book-type-sub-media.store') }}" method="POST"
                                                    enctype="multipart/form-data">
                                                    @csrf
                                                    <input type="hidden" name="book_type_id" value="{{ $bookType->id }}">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label for="media" class="form-label">Media</label>
                                                            <input type="file" name="media" id="media"
                                                                class="form-control" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="type" class="form-label">Type</label>
                                                            <select name="type" id="type" class="form-select"
                                                                required>
                                                                <option value="image">Image</option>
                                                                <option value="video">Video</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="text-end mt-3">
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="fas fa-save me-2"></i>Add Sub Media
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No book types found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
