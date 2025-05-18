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

                    <!-- Form for Creating Book Decoration -->
                    <form action="{{ route('book-decorations.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Section Title -->
                        <h4 class="mb-4 text-primary">Add New Book Decoration</h4>

                        <!-- Decoration Image -->
                        <div class="form-group mb-4">
                            <label for="image" class="form-label fw-bold">Decoration Image</label>
                            <input type="file" data-plugins="dropify" data-height="200"
                                class="form-control @error('image') is-invalid @enderror" name="image" id="image"
                                required>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="text-end">
                            <button type="submit" class="btn btn-success px-4">Add Book Decoration</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
