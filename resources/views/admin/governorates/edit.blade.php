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
                    <form action="{{ route('governorates.update', $governorate->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <h4 class="mb-4 text-primary">Edit Governorate</h4>

                        <div class="row mb-3">

                            <div class="col-md-6">
                                <label for="name_en">English Name</label>
                                <input type="text" name="name_en" id="name_en" class="form-control"
                                    value="{{ $governorate->name_en }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="name_ar">Arabic Name</label>
                                <input type="text" name="name_ar" id="name_ar" class="form-control"
                                    value="{{ $governorate->name_ar }}" required>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-end">
                            <button type="submit" class="btn btn-success px-4">Update Governorate</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
