@extends('admin.layout')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <!-- Header Section -->
                <div class="d-flex justify-content-between align-items-center p-3">
                    <h1 class="mb-0 text-primary">Discount Codes</h1>
                    <a href="{{ route('discount-codes.create') }}" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i> Add New Discount Code
                    </a>
                </div>

                <!-- Table Section -->
                <div class="card-body">
                    <!-- DataTable -->
                    <table id="responsive-datatable" class="table table-striped table-bordered dt-responsive ">
                        <thead>
                            <tr>
                                <th class="text-center">ID</th>
                                <th class="text-center">Discount Code</th>
                                <th class="text-center">Discount Value</th>
                                <th class="text-center">Discount Type</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($discountCodes as $code)
                                <tr>
                                    <td class="text-center">{{ $code->id }}</td>
                                    <td class="text-center">{{ $code->discount_code }}</td>
                                    <td class="text-center">
                                        {{ $code->discount_value }}
                                        {{ $code->discount_type === 'percentage' ? '%' : 'JOD' }}
                                    </td>
                                    <td class="text-center">
                                        {{ ucfirst($code->discount_type) }}
                                    </td>
                                    <td class="text-center">
                                        <!-- Actions Dropdown -->
                                        <div class="dropdown">
                                            <a class="dropdown-toggle" id="dropdownMenuButton{{ $code->id }}"
                                                data-bs-toggle="dropdown" style="cursor: pointer;" aria-expanded="false"
                                                title="Actions">
                                                <i class="fas fa-ellipsis-h"></i>
                                            </a>
                                            <ul class="dropdown-menu dropdown-menu-end"
                                                aria-labelledby="dropdownMenuButton{{ $code->id }}">
                                                <!-- Edit Action -->
                                                <li>
                                                    <a href="{{ route('discount-codes.edit', $code) }}"
                                                        class="dropdown-item" title="Edit Discount Code">
                                                        <i class="fas fa-edit me-2"></i>Edit
                                                    </a>
                                                </li>
                                                <!-- Delete Action -->
                                                <li>
                                                    <form action="{{ route('discount-codes.destroy', $code) }}"
                                                        method="POST" id="delete-form-{{ $code->id }}">
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
                                    <td colspan="5" class="text-center text-muted">No discount codes found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
