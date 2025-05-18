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

                    <!-- Form for Editing Book Decoration -->
                    <form action="{{ route('book-decorations.update', $bookDecoration->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Section Title -->
                        <h4 class="mb-4 text-primary">Edit Book Decoration</h4>

                        <!-- Upload New Image -->
                        <div class="form-group mb-4">
                            <label for="image" class="form-label fw-bold">Book Decoration Image</label>
                            <input type="file" data-plugins="dropify" data-height="200"
                                data-default-file="{{ $bookDecoration->image }}"
                                class="form-control @error('image') is-invalid @enderror" name="image" id="image">
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="text-end">
                            <button type="submit" class="btn btn-success px-4">Update Book Decoration</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
