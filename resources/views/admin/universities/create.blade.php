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
                    <form action="{{ route('universities.store') }}" method="POST">
                        @csrf
                        <h4 class="mb-4 text-primary">Add New University</h4>
                        <div class="row mb-3">
                            <!-- University Name -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="name">University Name</label>
                                <input type="text" name="name" id="name" class="form-control" required>
                            </div>

                            <!-- Governorate Name -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="governorate_name">Governorate Name (Optional)</label>
                                <input type="text" name="governorate_name" id="governorate_name" class="form-control">
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-end">
                            <button type="submit" class="btn btn-success px-4">Create University</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
