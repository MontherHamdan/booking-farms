@extends('admin.layout')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="d-flex justify-content-between align-items-center p-3">
                    <h1 class="mb-0 text-primary">Subcategories</h1>
                    <a href="{{ route('subcategories.create') }}" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i> Add New Subcategory
                    </a>
                </div>

                <div class="card-body">

                    <table id="responsive-datatable" class="table table-striped table-bordered dt-responsive ">
                        <thead>
                            <tr>
                                <th class="text-center">ID</th>
                                <th class="text-center">English Name</th>
                                <th class="text-center">Arabic Name</th>
                                <th class="text-center">Category</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($subCategories as $subCategory)
                                <tr>
                                    <td class="text-center">{{ $subCategory->id }}</td>
                                    <td class="text-center">{{ $subCategory->name }}</td>
                                    <td class="text-center">{{ $subCategory->arabic_name }}</td>
                                    <td class="text-center">{{ $subCategory->category->name }}</td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center align-items-center gap-2">
                                            <a href="{{ route('subcategories.edit', $subCategory->id) }}"
                                                class="btn btn-warning btn-sm" title="Edit Subcategory">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('subcategories.destroy', $subCategory) }}" method="POST"
                                                style="display:inline;" id="delete-form-{{ $subCategory->id }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-danger btn-sm sa-warning-btn">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
