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

                    <!-- Form for Creating Book Type -->
                    <form action="{{ route('book-types.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Section Title -->
                        <h4 class="mb-4 text-primary">Add New Book Type</h4>

                        <!-- Book Type Image -->
                        <div class="form-group mb-4">
                            <label for="image" class="form-label fw-bold">Book Type Image</label>
                            <input type="file" data-plugins="dropify" data-height="200"
                                class="form-control @error('image') is-invalid @enderror" name="image" id="image"
                                required>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- name en -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name_en" class="form-label fw-bold">English Name</label>
                                <input type="text" class="form-control @error('name_en') is-invalid @enderror"
                                    id="name_en" name="name_en" required>
                                @error('name_en')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="name_ar" class="form-label fw-bold">Arabic Name</label>
                                <input type="text" class="form-control @error('name_ar') is-invalid @enderror"
                                    id="name_ar" name="name_ar" required>
                                @error('name_ar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>


                        <!-- Description in Arabic -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="description_ar" class="form-label fw-bold">Description (AR)</label>
                                <textarea class="form-control @error('description_ar') is-invalid @enderror" id="description_ar" name="description_ar"></textarea>
                                @error('description_ar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <!-- Description in English -->
                            <div class="col-md-6">
                                <label for="description_en" class="form-label fw-bold">Description (EN)</label>
                                <textarea class="form-control @error('description_en') is-invalid @enderror" id="description_en" name="description_en"></textarea>
                                @error('description_en')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Price -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="price" class="form-label fw-bold">Price</label>
                                <input type="number" min="0" value="0"
                                    class="form-control @error('price') is-invalid @enderror" id="price" name="price"
                                    required>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-end">
                            <button type="submit" class="btn btn-success px-4">Add Book Type</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
