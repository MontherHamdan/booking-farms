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
                    <form action="{{ route('universities.update', $university->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <h4 class="mb-4 text-primary">Edit University</h4>

                        <div class="row mb-3">
                            <!-- University Name -->
                            <div class="col-md-6">
                                <label for="name">University Name</label>
                                <input type="text" name="name" id="name" class="form-control"
                                    value="{{ old('name', $university->name) }}" required>
                            </div>

                            <!-- Governorate Name -->
                            <div class="col-md-6">
                                <label for="governorate_name">Governorate Name (Optional)</label>
                                <input type="text" name="governorate_name" id="governorate_name" class="form-control"
                                    value="{{ old('governorate_name', $university->governorate_name) }}">
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-end">
                            <button type="submit" class="btn btn-success px-4">Update University</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
