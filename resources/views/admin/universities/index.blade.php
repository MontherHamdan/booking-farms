@extends('admin.layout')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="d-flex justify-content-between align-items-center p-3">
                    <h1 class="mb-0 text-primary">Universities</h1>
                    <a href="{{ route('universities.create') }}" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i> Add New University
                    </a>
                </div>

                <div class="card-body">
                    <table id="responsive-datatable" class="table table-striped table-bordered dt-responsive ">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Governorate</th>
                                <th>View Majors</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($universities as $university)
                                <tr>
                                    <td>{{ $university->id }}</td>
                                    <td>{{ $university->name }}</td>
                                    <td>{{ $university->governorate_name }}</td>
                                    <td>
                                        <button type="button" class="btn btn-info" data-bs-toggle="modal"
                                            data-bs-target="#majorsModal" data-id="{{ $university->id }}">
                                            View Majors
                                        </button>

                                    </td>
                                    <td class="text-center">
                                        <!-- Actions Dropdown -->
                                        <div class="dropdown">
                                            <a class="dropdown-toggle" id="dropdownMenuButton{{ $university->id }}"
                                                data-bs-toggle="dropdown" style="cursor: pointer;" aria-expanded="false"
                                                title="Actions">
                                                <i class="fas fa-ellipsis-h"></i>
                                            </a>
                                            <ul class="dropdown-menu dropdown-menu-end"
                                                aria-labelledby="dropdownMenuButton{{ $university->id }}">
                                                <!-- Edit Action -->
                                                <li>
                                                    <a href="{{ route('universities.edit', $university->id) }}"
                                                        class="dropdown-item" title="Edit Decoration">
                                                        <i class="fas fa-edit me-2"></i>Edit
                                                    </a>
                                                </li>
                                                <!-- Delete Action -->
                                                <li>
                                                    <form action="{{ route('universities.destroy', $university->id) }}"
                                                        method="POST" id="delete-form-{{ $university->id }}">
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
                                    <td colspan="4" class="text-center text-muted">No universities found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="majorsModal" tabindex="-1" aria-labelledby="majorsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="majorsModalLabel">Manage Majors</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Major Creation Form -->
                    <form id="addMajorForm" class="mb-3">
                        @csrf
                        <div class="row">
                            <div class="col-md-10">
                                <input type="text" name="name" class="form-control" placeholder="Major Name" required>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-success w-100">Add</button>
                            </div>
                        </div>
                    </form>

                    <!-- Majors List -->
                    <div id="modalContent">
                        <p>Loading...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('majorsModal');
            let universityId = null;

            modal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                universityId = button.getAttribute('data-id');
                const modalContent = document.getElementById('modalContent');

                // Load majors
                fetch(`/universities/${universityId}/majors`)
                    .then(response => response.text())
                    .then(html => {
                        modalContent.innerHTML = html;
                    })
                    .catch(error => {
                        modalContent.innerHTML = '<p>Error loading majors.</p>';
                        console.error(error);
                    });
            });

            // Handle major creation form submission
            const addMajorForm = document.getElementById('addMajorForm');
            addMajorForm.addEventListener('submit', function(event) {
                event.preventDefault();

                const formData = new FormData(addMajorForm);
                fetch(`/universities/${universityId}/add-major`, {
                        method: 'POST',
                        body: formData,
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update the majors list
                            fetch(`/universities/${universityId}/majors`)
                                .then(response => response.text())
                                .then(html => {
                                    document.getElementById('modalContent').innerHTML = html;
                                });

                            // Clear the form fields
                            addMajorForm.reset();
                        } else {
                            alert('Error adding major.');
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
