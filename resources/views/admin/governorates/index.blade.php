@extends('admin.layout')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <!-- Header Section -->
                <div class="d-flex justify-content-between align-items-center p-3">
                    <h1 class="mb-0 text-primary">Governorates</h1>
                    <a href="{{ route('governorates.create') }}" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i> Create Governorate
                    </a>
                </div>

                <div class="card-body">

                        <table id="responsive-datatable" class="table table-striped table-bordered dt-responsive ">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name (EN)</th>
                                <th>Name (AR)</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($governorates as $governorate)
                                <tr>
                                    <td>{{ $governorate->id }}</td>
                                    <td>{{ $governorate->name_en }}</td>
                                    <td>{{ $governorate->name_ar }}</td>
                                    <td class="text-center">
                                        <!-- Actions Dropdown -->
                                        <div class="dropdown">
                                            <a class="dropdown-toggle" id="dropdownMenuButton{{ $governorate->id }}"
                                                data-bs-toggle="dropdown" style="cursor: pointer;" aria-expanded="false"
                                                title="Actions">
                                                <i class="fas fa-ellipsis-h"></i>
                                            </a>
                                            <ul class="dropdown-menu dropdown-menu-end"
                                                aria-labelledby="dropdownMenuButton{{ $governorate->id }}">
                                                <!-- Edit Action -->
                                                <li>
                                                    <a href="{{ route('governorates.edit', $governorate->id) }}"
                                                        class="dropdown-item" title="Edit Decoration">
                                                        <i class="fas fa-edit me-2"></i>Edit
                                                    </a>
                                                </li>
                                                <!-- Delete Action -->
                                                <li>
                                                    <form action="{{ route('governorates.destroy', $governorate->id) }}"
                                                        method="POST" id="delete-form-{{ $governorate->id }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button"
                                                            class="dropdown-item text-danger sa-warning-btn">
                                                            <i class="fas fa-trash me-2"></i>Delete
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item" data-bs-toggle="modal"
                                                        data-bs-target="#addressesModal" data-id="{{ $governorate->id }}">
                                                        <i class="fas fa-images me-2"></i>Manage Addresses
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Modal -->
                    <div class="modal fade" id="addressesModal" tabindex="-1" aria-labelledby="addressesModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addressesModalLabel">Manage Addresses</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <!-- Address Creation Form -->
                                    <form id="addAddressForm" class="mb-3">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-5">
                                                <input type="text" name="name_en" class="form-control"
                                                    placeholder="English Address" required>
                                            </div>
                                            <div class="col-md-5">
                                                <input type="text" name="name_ar" class="form-control"
                                                    placeholder="Arabic Address" required>
                                            </div>
                                            <div class="col-md-2">
                                                <button type="submit" class="btn btn-success w-100">Add</button>
                                            </div>
                                        </div>
                                    </form>

                                    <!-- Addresses List -->
                                    <div id="modalContent">
                                        <p>Loading...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('addressesModal');
            let governorateId = null;

            modal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                governorateId = button.getAttribute('data-id');
                const modalContent = document.getElementById('modalContent');

                // Load addresses
                fetch(`/governorates/${governorateId}/addresses`)
                    .then(response => response.text())
                    .then(html => {
                        modalContent.innerHTML = html;
                    })
                    .catch(error => {
                        modalContent.innerHTML = '<p>Error loading addresses.</p>';
                        console.error(error);
                    });
            });

            // Handle address creation form submission
            const addAddressForm = document.getElementById('addAddressForm');
            addAddressForm.addEventListener('submit', function(event) {
                event.preventDefault();

                const formData = new FormData(addAddressForm);
                fetch(`/governorates/${governorateId}/add-address`, {
                        method: 'POST',
                        body: formData,
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update the addresses list
                            fetch(`/governorates/${governorateId}/addresses`)
                                .then(response => response.text())
                                .then(html => {
                                    document.getElementById('modalContent').innerHTML = html;
                                });

                            // Clear the form fields
                            addAddressForm.reset();
                        } else {
                            alert('Error adding address.');
                        }
                    })
                    .catch(error => {
                        console.error(error);
                        alert('Error submitting form.');
                    });
            });
        });
    </script>
@endsection
