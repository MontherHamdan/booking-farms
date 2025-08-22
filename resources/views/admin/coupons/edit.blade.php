@extends('admin.layout')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card card-primary card-outline shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title m-0 font-weight-bold text-primary">
                            <i class="fas fa-edit mr-2"></i>Edit Coupon
                        </h3>
                        <div>
                            @if($coupon->usages_count > 0)
                                <a href="{{ route('dashboard.coupons.usages', $coupon->id) }}" class="btn btn-outline-info mr-2">
                                    <i class="fas fa-chart-line mr-1"></i> View Usage ({{ $coupon->usages_count }})
                                </a>
                            @endif
                            <a href="{{ route('dashboard.coupons.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left mr-1"></i> Back to Coupons
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            {{ session('error') }}
                        </div>
                    @endif

                    @if($coupon->usages_count > 0)
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <strong>Notice:</strong> This coupon has been used {{ $coupon->usages_count }} time(s). 
                            Some restrictions apply when editing used coupons.
                        </div>
                    @endif
                    
                    <form action="{{ route('dashboard.coupons.update', $coupon->id) }}" method="POST" id="couponForm">
                        @csrf
                        @method('PUT')
                        
                        <!-- Basic Information -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="font-weight-bold">
                                        Coupon Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $coupon->name) }}" required
                                           placeholder="e.g., Summer Sale 2024">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="code" class="font-weight-bold">
                                        Coupon Code <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                           id="code" name="code" value="{{ old('code', $coupon->code) }}" required
                                           placeholder="e.g., SUMMER2024" style="text-transform: uppercase;"
                                           {{ $coupon->usages_count > 0 ? 'readonly' : '' }}>
                                    <small class="form-text text-muted">
                                        @if($coupon->usages_count > 0)
                                            Code cannot be changed after coupon has been used
                                        @else
                                            Only uppercase letters, numbers, underscores, and hyphens allowed
                                        @endif
                                    </small>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Discount Settings -->
                        <div class="row mt-2">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="discount_type" class="font-weight-bold">
                                        Discount Type <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control @error('discount_type') is-invalid @enderror" 
                                            id="discount_type" name="discount_type" required
                                            {{ $coupon->usages_count > 0 ? 'disabled' : '' }}>
                                        <option value="">Select Discount Type</option>
                                        <option value="{{ \App\Models\Coupon::DISCOUNT_TYPE_PERCENTAGE }}" 
                                                {{ old('discount_type', $coupon->discount_type) == \App\Models\Coupon::DISCOUNT_TYPE_PERCENTAGE ? 'selected' : '' }}>
                                            Percentage (%)
                                        </option>
                                        <option value="{{ \App\Models\Coupon::DISCOUNT_TYPE_FIXED_AMOUNT }}" 
                                                {{ old('discount_type', $coupon->discount_type) == \App\Models\Coupon::DISCOUNT_TYPE_FIXED_AMOUNT ? 'selected' : '' }}>
                                            Fixed Amount
                                        </option>
                                    </select>
                                    @if($coupon->usages_count > 0)
                                        <input type="hidden" name="discount_type" value="{{ $coupon->discount_type }}">
                                        <small class="form-text text-muted">
                                            Cannot be changed after coupon has been used
                                        </small>
                                    @endif
                                    @error('discount_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="discount_value" class="font-weight-bold">
                                        Discount Value <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" step="0.01" min="0" 
                                           class="form-control @error('discount_value') is-invalid @enderror" 
                                           id="discount_value" name="discount_value" value="{{ old('discount_value', $coupon->discount_value) }}" required
                                           placeholder="0.00" {{ $coupon->usages_count > 0 ? 'readonly' : '' }}>
                                    <small class="form-text text-muted" id="discount_help">
                                        @if($coupon->usages_count > 0)
                                            Cannot be changed after coupon has been used
                                        @else
                                            Enter the discount value
                                        @endif
                                    </small>
                                    @error('discount_value')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="max_discount" class="font-weight-bold">
                                        Maximum Discount
                                    </label>
                                    <input type="number" step="0.01" min="0" 
                                           class="form-control @error('max_discount') is-invalid @enderror" 
                                           id="max_discount" name="max_discount" value="{{ old('max_discount', $coupon->max_discount) }}"
                                           placeholder="0.00" {{ $coupon->usages_count > 0 ? 'readonly' : '' }}>
                                    <small class="form-text text-muted">
                                        @if($coupon->usages_count > 0)
                                            Cannot be changed after coupon has been used
                                        @else
                                            Only for percentage discounts
                                        @endif
                                    </small>
                                    @error('max_discount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Current Usage Info -->
                        @if($coupon->usages_count > 0)
                        <div class="row mt-2 mb-3">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <strong>Current Usage:</strong> {{ $coupon->usages_count }} time(s)
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Status:</strong> {{ ucfirst($coupon->status) }}
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Discount:</strong> {{ $coupon->discount_description }}
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Platform:</strong> {{ $coupon->platform_label }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Date Range -->
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_date" class="font-weight-bold">
                                        Start Date <span class="text-danger">*</span>
                                    </label>
                                    <input type="datetime-local" 
                                           class="form-control @error('start_date') is-invalid @enderror" 
                                           id="start_date" name="start_date" 
                                           value="{{ old('start_date', $coupon->start_date->format('Y-m-d\TH:i')) }}" required>
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_date" class="font-weight-bold">
                                        End Date <span class="text-danger">*</span>
                                    </label>
                                    <input type="datetime-local" 
                                           class="form-control @error('end_date') is-invalid @enderror" 
                                           id="end_date" name="end_date" 
                                           value="{{ old('end_date', $coupon->end_date->format('Y-m-d\TH:i')) }}" required>
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Usage Limits -->
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="usage_limit" class="font-weight-bold">
                                        Total Usage Limit
                                    </label>
                                    <input type="number" min="{{ $coupon->usages_count }}" 
                                           class="form-control @error('usage_limit') is-invalid @enderror" 
                                           id="usage_limit" name="usage_limit" value="{{ old('usage_limit', $coupon->usage_limit) }}"
                                           placeholder="Leave empty for unlimited">
                                    <small class="form-text text-muted">
                                        @if($coupon->usages_count > 0)
                                            Must be at least {{ $coupon->usages_count }} (current usage)
                                        @else
                                            Maximum number of times this coupon can be used in total
                                        @endif
                                    </small>
                                    @error('usage_limit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="usage_limit_per_user_type" class="font-weight-bold">
                                        Usage Per User <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control @error('usage_limit_per_user_type') is-invalid @enderror" 
                                            id="usage_limit_per_user_type" name="usage_limit_per_user_type" required>
                                        <option value="">Select Usage Limit</option>
                                        <option value="{{ \App\Models\Coupon::USAGE_LIMIT_SINGLE }}" 
                                                {{ old('usage_limit_per_user_type', $coupon->usage_limit_per_user_type) == \App\Models\Coupon::USAGE_LIMIT_SINGLE ? 'selected' : '' }}>
                                            One use per user
                                        </option>
                                        <option value="{{ \App\Models\Coupon::USAGE_LIMIT_MULTIPLE }}" 
                                                {{ old('usage_limit_per_user_type', $coupon->usage_limit_per_user_type) == \App\Models\Coupon::USAGE_LIMIT_MULTIPLE ? 'selected' : '' }}>
                                            Multiple uses per user
                                        </option>
                                        <option value="{{ \App\Models\Coupon::USAGE_LIMIT_UNLIMITED }}" 
                                                {{ old('usage_limit_per_user_type', $coupon->usage_limit_per_user_type) == \App\Models\Coupon::USAGE_LIMIT_UNLIMITED ? 'selected' : '' }}>
                                            Unlimited uses per user
                                        </option>
                                    </select>
                                    @error('usage_limit_per_user_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mt-2" id="user_count_row" style="{{ old('usage_limit_per_user_type', $coupon->usage_limit_per_user_type) == \App\Models\Coupon::USAGE_LIMIT_MULTIPLE ? '' : 'display: none;' }}">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="usage_limit_per_user_count" class="font-weight-bold">
                                        Uses Per User Count <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" min="1" 
                                           class="form-control @error('usage_limit_per_user_count') is-invalid @enderror" 
                                           id="usage_limit_per_user_count" name="usage_limit_per_user_count" 
                                           value="{{ old('usage_limit_per_user_count', $coupon->usage_limit_per_user_count) }}">
                                    <small class="form-text text-muted">
                                        How many times each user can use this coupon
                                    </small>
                                    @error('usage_limit_per_user_count')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Platform and Status -->
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="platform" class="font-weight-bold">
                                        Platform <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control @error('platform') is-invalid @enderror" 
                                            id="platform" name="platform" required>
                                        <option value="">Select Platform</option>
                                        <option value="{{ \App\Models\Coupon::PLATFORM_WEB }}" 
                                                {{ old('platform', $coupon->platform) == \App\Models\Coupon::PLATFORM_WEB ? 'selected' : '' }}>
                                            Web Only
                                        </option>
                                        <option value="{{ \App\Models\Coupon::PLATFORM_MOBILE }}" 
                                                {{ old('platform', $coupon->platform) == \App\Models\Coupon::PLATFORM_MOBILE ? 'selected' : '' }}>
                                            Mobile Only
                                        </option>
                                        <option value="{{ \App\Models\Coupon::PLATFORM_BOTH }}" 
                                                {{ old('platform', $coupon->platform) == \App\Models\Coupon::PLATFORM_BOTH ? 'selected' : '' }}>
                                            Web & Mobile
                                        </option>
                                    </select>
                                    @error('platform')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="is_active" class="font-weight-bold">
                                        Status <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control @error('is_active') is-invalid @enderror" 
                                            id="is_active" name="is_active" required>
                                        <option value="1" {{ old('is_active', $coupon->is_active ? '1' : '0') == '1' ? 'selected' : '' }}>
                                            Active
                                        </option>
                                        <option value="0" {{ old('is_active', $coupon->is_active ? '1' : '0') == '0' ? 'selected' : '' }}>
                                            Inactive
                                        </option>
                                    </select>
                                    @error('is_active')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Cities Selection -->
                        <div class="row mt-2">
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="font-weight-bold">Cities</label>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="all_cities" name="all_cities" value="1"
                                               {{ empty(old('cities', $coupon->cities)) ? 'checked' : '' }}>
                                        <label class="form-check-label font-weight-bold text-success" for="all_cities">
                                            Apply to All Cities
                                        </label>
                                    </div>
                                    <div id="cities_selection" style="{{ empty(old('cities', $coupon->cities)) ? 'display: none;' : '' }}">
                                        <label for="cities" class="form-label">
                                            Select Specific Cities
                                        </label>
                                        <div class="row">
                                            @foreach($cities as $city)
                                                <div class="col-md-4 col-sm-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" 
                                                               name="cities[]" value="{{ $city->id }}" 
                                                               id="city_{{ $city->id }}"
                                                               {{ in_array($city->id, old('cities', $coupon->cities ?? [])) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="city_{{ $city->id }}">
                                                            {{ $city->name_en }} ({{ $city->name_ar }})
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        @error('cities')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4 text-center">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="fas fa-save mr-2"></i> Update Coupon
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Handle discount type change
    $('#discount_type').on('change', function() {
        const discountType = $(this).val();
        const maxDiscountField = $('#max_discount');
        const discountHelp = $('#discount_help');
        const hasUsage = {{ $coupon->usages_count > 0 ? 'true' : 'false' }};
        
        if (hasUsage) return; // Don't change anything if coupon has been used
        
        if (discountType === '{{ \App\Models\Coupon::DISCOUNT_TYPE_PERCENTAGE }}') {
            maxDiscountField.prop('disabled', false);
            discountHelp.text('Enter percentage (0-100)');
            $('#discount_value').attr('max', '100');
        } else if (discountType === '{{ \App\Models\Coupon::DISCOUNT_TYPE_FIXED_AMOUNT }}') {
            maxDiscountField.prop('disabled', true).val('');
            discountHelp.text('Enter fixed amount');
            $('#discount_value').removeAttr('max');
        } else {
            maxDiscountField.prop('disabled', true).val('');
            discountHelp.text('Enter the discount value');
            $('#discount_value').removeAttr('max');
        }
    });

    // Handle usage per user type change
    $('#usage_limit_per_user_type').on('change', function() {
        const userLimitType = $(this).val();
        const userCountRow = $('#user_count_row');
        const userCountField = $('#usage_limit_per_user_count');
        
        if (userLimitType === '{{ \App\Models\Coupon::USAGE_LIMIT_MULTIPLE }}') {
            userCountRow.show();
            userCountField.prop('required', true);
        } else {
            userCountRow.hide();
            userCountField.prop('required', false).val('');
        }
    });

    // Handle all cities checkbox
    $('#all_cities').on('change', function() {
        const citiesSelection = $('#cities_selection');
        const cityCheckboxes = $('input[name="cities[]"]');
        
        if ($(this).is(':checked')) {
            citiesSelection.hide();
            cityCheckboxes.prop('checked', false);
        } else {
            citiesSelection.show();
        }
    });

    // Auto-uppercase coupon code (only if not readonly)
    $('#code').on('input', function() {
        if (!$(this).prop('readonly')) {
            $(this).val($(this).val().toUpperCase());
        }
    });

    // Update end date minimum when start date changes
    $('#start_date').on('change', function() {
        const startDate = $(this).val();
        if (startDate) {
            $('#end_date').attr('min', startDate);
        }
    });

    // Trigger change events on page load for proper initialization
    $('#discount_type').trigger('change');
    $('#usage_limit_per_user_type').trigger('change');
});
</script>
@endpush
@endsection