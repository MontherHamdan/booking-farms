@extends('admin.layout')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <!-- Display Errors if any -->
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Form for Creating Book Design -->
                    <form action="{{ route('book-designs.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Section Title -->
                        <h4 class="mb-4 text-primary">Create Book Design</h4>

                        <!-- Book Design Image -->
                        <div class="form-group mb-3">
                            <label for="image" class="form-label fw-bold">Book Design Image</label>
                            <input type="file" data-plugins="dropify" data-height="200"
                                class="form-control @error('image') is-invalid @enderror" name="image" id="image"
                                required>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Category and Subcategory Selection -->
                        <div class="row mb-3">
                            <!-- Category -->
                            <div class="col-md-6">
                                <label for="category_id" class="form-label fw-bold">Category</label>
                                <select name="category_id" id="category_id"
                                    class="form-select @error('category_id') is-invalid @enderror" required>
                                    <option value="" disabled selected>Select Category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->arabic_name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Subcategory -->
                            <div class="col-md-6">
                                <label for="sub_category_id" class="form-label fw-bold">Subcategory (optional)</label>
                                <select name="sub_category_id" id="sub_category_id"
                                    class="form-select @error('sub_category_id') is-invalid @enderror">
                                    <option value="" disabled selected>Select Subcategory</option>
                                    <!-- Subcategories will be dynamically populated using JavaScript -->
                                </select>
                                @error('sub_category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-end">
                            <button type="submit" class="btn btn-success px-4">Create Design</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Script for dynamic subcategory population -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const categorySelect = document.getElementById('category_id');
            const subCategorySelect = document.getElementById('sub_category_id');

            categorySelect.addEventListener('change', function() {
                const categoryId = this.value;

                // Clear the subcategory options
                subCategorySelect.innerHTML =
                    '<option value="" disabled selected>Select a subcategory</option>';

                if (categoryId) {
                    fetch(`/api/v1/book_design_subCategories?category_id=${categoryId}`)
                        .then(response => response.json())
                        .then(responseData => {
                            // Check the structure of the response
                            if (responseData.status === 'success' && Array.isArray(responseData.data)) {
                                const subCategories = responseData.data;

                                subCategories.forEach(subCategory => {
                                    const option = document.createElement('option');
                                    option.value = subCategory.id;
                                    option.textContent = subCategory.arabic_name;
                                    subCategorySelect.appendChild(option);
                                });
                            } else {
                                console.error(
                                    'Error: Subcategories data is not an array or request failed');
                            }
                        })
                        .catch(error => console.error('Error fetching subcategories:', error));
                }
            });
        });
    </script>
@endsection
