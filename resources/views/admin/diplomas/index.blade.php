@extends('admin.layout')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="d-flex justify-content-between align-items-center p-3">
                    <h1 class="mb-0 text-primary">College</h1>
                    <a href="{{ route('diplomas.create') }}" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i> Add New College
                    </a>
                </div>

                <div class="card-body">
                    <table id="responsive-datatable" class="table table-striped table-bordered dt-responsive ">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Governorate</th>
                                <th>Majors Count</th>
                                <th>View Majors</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($diplomas as $diploma)
                                <tr>
                                    <td>{{ $diploma->id }}</td>
                                    <td>{{ $diploma->name }}</td>
                                    <td>{{ $diploma->governorate_name }}</td>
                                    <td>{{ $diploma->majors_count }}</td>
                                    <td>
                                        <button type="button" class="btn btn-info" data-bs-toggle="modal"
                                            data-bs-target="#majorsModal" data-id="{{ $diploma->id }}">
                                            View Majors
                                        </button>
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <a class="dropdown-toggle" id="dropdownMenuButton{{ $diploma->id }}"
                                                data-bs-toggle="dropdown" style="cursor: pointer;" aria-expanded="false"
                                                title="Actions">
                                                <i class="fas fa-ellipsis-h"></i>
                                            </a>
                                            <ul class="dropdown-menu dropdown-menu-end"
                                                aria-labelledby="dropdownMenuButton{{ $diploma->id }}">
                                                <li>
                                                    <a href="{{ route('diplomas.edit', $diploma->id) }}"
                                                        class="dropdown-item" title="Edit">
                                                        <i class="fas fa-edit me-2"></i>Edit
                                                    </a>
                                                </li>
                                                <li>
                                                    <form action="{{ route('diplomas.destroy', $diploma->id) }}"
                                                        method="POST" id="delete-form-{{ $diploma->id }}">
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
                                    <td colspan="6" class="text-center text-muted">No Colleges found.</td>
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
            let diplomaId = null;

            // Show modal and fetch majors for the selected diploma
            modal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                diplomaId = button.getAttribute('data-id');
                const modalContent = document.getElementById('modalContent');

                // Fetch majors for the diploma
                fetch(`/diplomas/${diplomaId}/majors`)
                    .then(response => response.text())
                    .then(html => {
                        modalContent.innerHTML = html;
                    })
                    .catch(error => {
                        modalContent.innerHTML = '<p>Error loading majors.</p>';
                        console.error(error);
                    });
            });

            // Handle add major form submission
            const addMajorForm = document.getElementById('addMajorForm');
            addMajorForm.addEventListener('submit', function(event) {
                event.preventDefault();

                const formData = new FormData(addMajorForm);
                // Update the URL to match the correct store route
                fetch(`/diplomas/${diplomaId}/majors`, {
                        method: 'POST',
                        body: formData,
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Fetch and update majors list after adding a new major
                            fetch(`/diplomas/${diplomaId}/majors`)
                                .then(response => response.text())
                                .then(html => {
                                    document.getElementById('modalContent').innerHTML = html;
                                });

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
