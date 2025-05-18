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

                    <!-- Form -->
                    <form action="{{ route('discount-codes.update', $discountCode->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <h4 class="mb-4 text-primary">Edit Discount Code</h4>
                        <div class="row mb-3">
                            <!-- Discount Code Field -->
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="discount_code">Discount Code</label>
                                <input type="text" name="discount_code" id="discount_code" class="form-control"
                                    value="{{ old('discount_code', $discountCode->discount_code) }}" required>
                            </div>

                            <!-- Discount Value Field -->
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="discount_value">Discount Value</label>
                                <input type="number" name="discount_value" id="discount_value" class="form-control"
                                    value="{{ old('discount_value', $discountCode->discount_value) }}" min="1" required>
                            </div>

                            <!-- Discount Type Field -->
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="discount_type">Discount Type</label>
                                <select name="discount_type" id="discount_type" class="form-select" required>
                                    <option value="" disabled>Select Discount Type</option>
                                    <option value="percentage" 
                                        {{ old('discount_type', $discountCode->discount_type) == 'percentage' ? 'selected' : '' }}>
                                        Percentage (%)
                                    </option>
                                    <option value="byJd" 
                                        {{ old('discount_type', $discountCode->discount_type) == 'byJd' ? 'selected' : '' }}>
                                        By JOD
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary px-4">Update Discount Code</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
