@extends('admin.layout')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="d-flex justify-content-between align-items-center p-3">
                    <h1 class="mb-0 text-primary">SVGs</h1>
                    <a href="{{ route('svgs.create') }}" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i> Add New SVG
                    </a>
                </div>

                <div class="card-body">
                    <table id="responsive-datatable" class="table table-striped table-bordered dt-responsive ">
                        <thead>
                            <tr>
                                <th class="text-center">ID</th>
                                <th class="text-center">Title</th>
                                <th class="text-center">Preview</th>
                                <th class="text-center">Actions</th>
                                <th class="text-center">Copy Code</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($svgs as $svg)
                                <tr>
                                    <td class="text-center">{{ $svg->id }}</td>
                                    <td class="text-center">{{ $svg->title ?? 'No Title' }}</td>
                                    <td class="text-center">
                                        <!-- Center SVG Preview -->
                                        <div class="svg-preview-container"
                                            style="display: flex; justify-content: center; align-items: center; height: 100%;">
                                            <div class="svg-preview img-thumbnail"
                                                style="width: 70px; height: 70px; display: flex; justify-content: center; align-items: center; overflow: hidden;">
                                                {!! $svg->svg_code !!}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <a class="dropdown-toggle" id="dropdownMenuButton{{ $svg->id }}"
                                                data-bs-toggle="dropdown" style="cursor: pointer" aria-expanded="false">
                                                <i class="fas fa-ellipsis-h"></i>
                                            </a>
                                            <ul class="dropdown-menu"
                                                aria-labelledby="dropdownMenuButton{{ $svg->id }}">
                                                <li>
                                                    <a href="{{ route('svgs.edit', $svg->id) }}" class="dropdown-item"
                                                        title="Edit SVG">
                                                        <i class="fas fa-edit me-2"></i>Edit
                                                    </a>
                                                </li>
                                                <li>
                                                    <form action="{{ route('svgs.destroy', $svg->id) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger"
                                                            onclick="return confirm('Are you sure you want to delete this SVG?')">
                                                            <i class="fas fa-trash me-2"></i>Delete
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-primary btn-sm copy-svg-button">
                                            <i class="fas fa-copy me-1"></i> Copy
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No SVGs found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Attach event listener to dynamically copy SVG code
        document.addEventListener('DOMContentLoaded', function() {
            const copyButtons = document.querySelectorAll('.copy-svg-button');

            // Create a reusable toast notification container
            const toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.style.position = 'fixed';
            toastContainer.style.bottom = '20px';
            toastContainer.style.right = '20px';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);

            copyButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const svgPreviewDiv = this.closest('tr').querySelector('.svg-preview');
                    const svgCode = svgPreviewDiv.innerHTML
                        .trim(); // Extract the SVG code from the div

                    navigator.clipboard.writeText(svgCode)
                        .then(() => {
                            showToast('SVG code copied to clipboard!', 'success');
                        })
                        .catch(err => {
                            console.error('Failed to copy SVG code: ', err);
                            showToast('Failed to copy SVG code. Please try again.', 'error');
                        });
                });
            });

            // Function to show a toast notification
            function showToast(message, type = 'success') {
                const toast = document.createElement('div');
                toast.textContent = message;
                toast.style.padding = '10px 20px';
                toast.style.marginTop = '10px';
                toast.style.borderRadius = '5px';
                toast.style.color = '#fff';
                toast.style.fontSize = '14px';
                toast.style.boxShadow = '0 2px 4px rgba(0, 0, 0, 0.2)';
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.3s ease, transform 0.3s ease';

                if (type === 'success') {
                    toast.style.backgroundColor = '#28a745'; // Green for success
                } else if (type === 'error') {
                    toast.style.backgroundColor = '#dc3545'; // Red for error
                }

                toastContainer.appendChild(toast);

                // Show the toast with animation
                setTimeout(() => {
                    toast.style.opacity = '1';
                    toast.style.transform = 'translateY(-10px)';
                }, 100);

                // Remove the toast after 3 seconds
                setTimeout(() => {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateY(0)';
                    setTimeout(() => toast.remove(), 300);
                }, 3000);
            }
        });
    </script>
@endsection
