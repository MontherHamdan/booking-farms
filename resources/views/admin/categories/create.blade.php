@extends('admin.layout')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('categories.store') }}" method="POST">
                        @csrf

                        <h4 class="mb-4 text-primary">Create Category</h4>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-bold">English Name</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    name="name" id="name" value="{{ old('name') }}"
                                    placeholder="Enter the category English name" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="arabic_name" class="form-label fw-bold">Arabic Name</label>
                                <input type="text" class="form-control @error('arabic_name') is-invalid @enderror"
                                    name="arabic_name" id="arabic_name" value="{{ old('arabic_name') }}"
                                    placeholder="Enter the category Arabic name" required>
                                @error('arabic_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="type" id="type" value="multiple"
                                {{ old('type') === 'multiple' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="type">
                                Is Multiple?
                            </label>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-success px-4">Create Category</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
