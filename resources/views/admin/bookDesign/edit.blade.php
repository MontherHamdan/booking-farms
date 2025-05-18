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

                    <!-- Form for Editing Book Design -->
                    <form action="{{ route('book-designs.update', $bookDesign->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Section Title -->
                        <h4 class="mb-4 text-primary">Edit Book Design</h4>

                        <!-- Book Design Image -->
                        <div class="form-group mb-3">
                            <label for="image" class="form-label fw-bold">Book Design Image</label>
                            <input type="file" data-plugins="dropify" data-height="200"
                                class="form-control @error('image') is-invalid @enderror" name="image" id="image"
                                data-default-file="{{ $bookDesign->image }}" accept="image/*">
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
                                    class="form-select @error('category_id') is-invalid @enderror">
                                    <option value="" disabled>Select Category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ $category->id == $bookDesign->category_id ? 'selected' : '' }}>
                                            {{ $category->arabic_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Subcategory -->
                            <div class="col-md-6">
                                <label for="sub_category_id" class="form-label fw-bold">Subcategory</label>
                                <select name="sub_category_id" id="sub_category_id"
                                    class="form-select @error('sub_category_id') is-invalid @enderror">
                                    <option value="">Select Subcategory</option>
                                    @foreach ($subCategories as $subCategory)
                                        <option value="{{ $subCategory->id }}"
                                            {{ $subCategory->id == $bookDesign->sub_category_id ? 'selected' : '' }}>
                                            {{ $subCategory->arabic_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('sub_category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary px-4">Update Design</button>
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
            const selectedCategoryId = '{{ $bookDesign->category_id }}'; // Get the currently selected category ID

            // Function to populate subcategories based on selected category
            function populateSubCategories(categoryId) {
                // Clear the subcategory options
                subCategorySelect.innerHTML =
                    '<option value="" disabled selected>Select Subcategory</option>';

                if (categoryId) {
                    fetch(`/api/v1/book_design_subCategories?category_id=${categoryId}`)
                        .then(response => response.json())
                        .then(responseData => {
                            if (responseData.status === 'success' && Array.isArray(responseData.data)) {
                                const subCategories = responseData.data;

                                subCategories.forEach(subCategory => {
                                    const option = document.createElement('option');
                                    option.value = subCategory.id;
                                    option.textContent = subCategory.arabic_name;
                                    subCategorySelect.appendChild(option);
                                });

                                // Select the current subcategory if it's already set
                                if ('{{ $bookDesign->sub_category_id }}') {
                                    subCategorySelect.value = '{{ $bookDesign->sub_category_id }}';
                                }
                            } else {
                                console.error('Error: Subcategories data is not an array or request failed');
                            }
                        })
                        .catch(error => console.error('Error fetching subcategories:', error));
                }
            }

            // Initially populate subcategories based on the current category
            populateSubCategories(selectedCategoryId);

            // Update subcategories when a new category is selected
            categorySelect.addEventListener('change', function() {
                populateSubCategories(this.value);
            });
        });
    </script>
@endsection
