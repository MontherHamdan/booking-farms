@extends('admin.layout')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h4 class="font-weight-bold text-primary">
                Edit Booking: {{ $booking->booking_reference }}
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.bookings.index') }}">Bookings</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.bookings.show', $booking->id) }}">{{ $booking->booking_reference }}</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
        <div class="col-auto">
            <a href="{{ route('dashboard.bookings.show', $booking->id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back to Details
            </a>
        </div>
    </div>

    <!-- Warning Alert -->
    <div class="alert alert-warning alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Important:</strong> Be careful when editing booking details. Changes to dates, times, or farm assignments may affect pricing and earnings calculations.
        Critical changes will be logged for audit purposes.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    <!-- Edit Form -->
    <form action="{{ route('dashboard.bookings.update', $booking->id) }}" method="POST" id="editBookingForm">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- Main Content -->
            <div class="col-md-8">
                <!-- Customer Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i>Customer Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="customer_name" class="form-label">Customer Name</label>
                                    <input type="text" 
                                           class="form-control @error('customer_name') is-invalid @enderror" 
                                           id="customer_name" 
                                           name="customer_name" 
                                           value="{{ old('customer_name', $booking->customer_name ?: $booking->user?->name) }}"
                                           placeholder="Enter customer name">
                                    @error('customer_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="customer_email" class="form-label">Customer Email</label>
                                    <input type="email" 
                                           class="form-control @error('customer_email') is-invalid @enderror" 
                                           id="customer_email" 
                                           name="customer_email" 
                                           value="{{ old('customer_email', $booking->customer_email ?: $booking->user?->email) }}"
                                           placeholder="Enter customer email">
                                    @error('customer_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="customer_phone" class="form-label">Customer Phone</label>
                                    <input type="text" 
                                           class="form-control @error('customer_phone') is-invalid @enderror" 
                                           id="customer_phone" 
                                           name="customer_phone" 
                                           value="{{ old('customer_phone', $booking->customer_phone ?: $booking->user?->phone) }}"
                                           placeholder="Enter customer phone">
                                    @error('customer_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="guest_count" class="form-label">Guest Count *</label>
                                    <input type="number" 
                                           class="form-control @error('guest_count') is-invalid @enderror" 
                                           id="guest_count" 
                                           name="guest_count" 
                                           value="{{ old('guest_count', $booking->guest_count) }}"
                                           min="1" max="100" required>
                                    @error('guest_count')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Booking Details -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Booking Details</h5>
                        <span class="badge bg-info">Current: {{ ucfirst(str_replace('_', ' ', $booking->price_type)) }}</span>
                    </div>
                    <div class="card-body">
                        <!-- Critical Change Warning -->
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note:</strong> Changing dates or times may affect pricing. Price recalculation is not automatic and may require manual adjustment.
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date *</label>
                                    <input type="date" 
                                           class="form-control @error('start_date') is-invalid @enderror" 
                                           id="start_date" 
                                           name="start_date" 
                                           value="{{ old('start_date', $booking->start_date?->format('Y-m-d')) }}"
                                           required>
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date *</label>
                                    <input type="date" 
                                           class="form-control @error('end_date') is-invalid @enderror" 
                                           id="end_date" 
                                           name="end_date" 
                                           value="{{ old('end_date', $booking->end_date?->format('Y-m-d')) }}"
                                           required>
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_time" class="form-label">Start Time</label>
                                    <input type="time" 
                                           class="form-control @error('start_time') is-invalid @enderror" 
                                           id="start_time" 
                                           name="start_time" 
                                           value="{{ old('start_time', $booking->start_time?->format('H:i')) }}">
                                    @error('start_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_time" class="form-label">End Time</label>
                                    <input type="time" 
                                           class="form-control @error('end_time') is-invalid @enderror" 
                                           id="end_time" 
                                           name="end_time" 
                                           value="{{ old('end_time', $booking->end_time?->format('H:i')) }}">
                                    @error('end_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Customer Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" 
                                      name="notes" 
                                      rows="3" 
                                      placeholder="Any special requests or notes from the customer">{{ old('notes', $booking->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Status Management -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-tags me-2"></i>Status Management</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="booking_status" class="form-label">Booking Status *</label>
                                    <select class="form-control @error('booking_status') is-invalid @enderror" 
                                            id="booking_status" 
                                            name="booking_status" 
                                            required>
                                        <option value="pending" {{ old('booking_status', $booking->booking_status) == 'pending' ? 'selected' : '' }}>
                                            Pending
                                        </option>
                                        <option value="confirmed" {{ old('booking_status', $booking->booking_status) == 'confirmed' ? 'selected' : '' }}>
                                            Confirmed
                                        </option>
                                        <option value="cancelled" {{ old('booking_status', $booking->booking_status) == 'cancelled' ? 'selected' : '' }}>
                                            Cancelled
                                        </option>
                                        <option value="failed" {{ old('booking_status', $booking->booking_status) == 'failed' ? 'selected' : '' }}>
                                            Failed
                                        </option>
                                        <option value="completed" {{ old('booking_status', $booking->booking_status) == 'completed' ? 'selected' : '' }}>
                                            Completed
                                        </option>
                                    </select>
                                    @error('booking_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="payment_status" class="form-label">Payment Status *</label>
                                    <select class="form-control @error('payment_status') is-invalid @enderror" 
                                            id="payment_status" 
                                            name="payment_status" 
                                            required>
                                        <option value="pending" {{ old('payment_status', $booking->payment_status) == 'pending' ? 'selected' : '' }}>
                                            Pending
                                        </option>
                                        <option value="paid" {{ old('payment_status', $booking->payment_status) == 'paid' ? 'selected' : '' }}>
                                            Paid
                                        </option>
                                        <option value="partially_paid" {{ old('payment_status', $booking->payment_status) == 'partially_paid' ? 'selected' : '' }}>
                                            Partially Paid
                                        </option>
                                        <option value="failed" {{ old('payment_status', $booking->payment_status) == 'failed' ? 'selected' : '' }}>
                                            Failed
                                        </option>
                                        <option value="expired" {{ old('payment_status', $booking->payment_status) == 'expired' ? 'selected' : '' }}>
                                            Expired
                                        </option>
                                        <option value="refunded" {{ old('payment_status', $booking->payment_status) == 'refunded' ? 'selected' : '' }}>
                                            Refunded
                                        </option>
                                    </select>
                                    @error('payment_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Status Change Indicators -->
                        <div class="row">
                            <div class="col-12">
                                <div id="status-warnings" class="mt-2"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Update Reason (Required) -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Update Reason</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="update_reason" class="form-label">Reason for Update *</label>
                            <textarea class="form-control @error('update_reason') is-invalid @enderror" 
                                      id="update_reason" 
                                      name="update_reason" 
                                      rows="3" 
                                      placeholder="Please explain why you are making these changes (required for audit trail)"
                                      required>{{ old('update_reason') }}</textarea>
                            <div class="form-text">This will be logged for audit purposes.</div>
                            @error('update_reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Current Booking Summary -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Current Booking Summary</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td><strong>Reference:</strong></td>
                                <td>{{ $booking->booking_reference }}</td>
                            </tr>
                            <tr>
                                <td><strong>Farm:</strong></td>
                                <td>{{ $booking->farm->name_en ?: $booking->farm->name_ar }}</td>
                            </tr>
                            <tr>
                                <td><strong>Current Status:</strong></td>
                                <td>
                                    <span class="badge bg-{{ $booking->booking_status === 'confirmed' ? 'success' : ($booking->booking_status === 'pending' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($booking->booking_status) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Payment Status:</strong></td>
                                <td>
                                    <span class="badge bg-{{ $booking->payment_status === 'paid' ? 'success' : ($booking->payment_status === 'pending' ? 'warning' : 'danger') }}">
                                        {{ ucfirst(str_replace('_', ' ', $booking->payment_status)) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Total Amount:</strong></td>
                                <td><strong>${{ number_format($booking->total_amount, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <td><strong>Created:</strong></td>
                                <td>{{ $booking->created_at->format('M d, Y') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Farm Information (Read-only) -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fas fa-home me-2"></i>Farm Information</h6>
                        <a href="{{ route('dashboard.farms.show', $booking->farm->id) }}" 
                           class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            @if($booking->farm->images->where('is_main', true)->first())
                                <img src="{{ $booking->farm->images->where('is_main', true)->first()->image_path }}" 
                                     alt="Farm Image" 
                                     class="img-fluid rounded"
                                     style="max-height: 120px;">
                            @else
                                <div class="bg-secondary rounded d-flex align-items-center justify-content-center text-white mx-auto" 
                                     style="width: 120px; height: 80px;">
                                    <i class="fas fa-home fa-2x"></i>
                                </div>
                            @endif
                        </div>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td><strong>Name:</strong></td>
                                <td>{{ $booking->farm->name_en ?: $booking->farm->name_ar }}</td>
                            </tr>
                            <tr>
                                <td><strong>Owner:</strong></td>
                                <td>{{ $booking->farm->user->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Location:</strong></td>
                                <td>{{ $booking->farm->city->name_en ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Earnings Information (if applicable) -->
                @if($booking->earnings_processed || $booking->farm_owner_earning)
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-dollar-sign me-2"></i>Earnings Info</h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Warning:</strong> This booking has processed earnings. Changes may affect wallet calculations.
                        </div>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td><strong>Farm Owner Earning:</strong></td>
                                <td>${{ number_format($booking->farm_owner_earning ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Commission:</strong></td>
                                <td>${{ number_format($booking->platform_commission_amount ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    @if($booking->earnings_confirmed)
                                        <span class="badge bg-success">Confirmed</span>
                                    @elseif($booking->earnings_processed)
                                        <span class="badge bg-warning">Processed</span>
                                    @else
                                        <span class="badge bg-secondary">Not Processed</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                @endif

                <!-- Action Buttons -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary" id="saveChanges">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                            <a href="{{ route('dashboard.bookings.show', $booking->id) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                        
                        <hr>
                        
                        <div class="text-center">
                            <small class="text-muted">
                                Last updated: {{ $booking->updated_at->format('M d, Y \a\t g:i A') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Track original values for change detection
    const originalValues = {
        booking_status: '{{ $booking->booking_status }}',
        payment_status: '{{ $booking->payment_status }}',
        start_date: '{{ $booking->start_date?->format("Y-m-d") }}',
        end_date: '{{ $booking->end_date?->format("Y-m-d") }}'
    };
    
    // Status change warnings
    $('#booking_status, #payment_status').on('change', function() {
        updateStatusWarnings();
    });
    
    // Date validation
    $('#start_date, #end_date').on('change', function() {
        validateDates();
        updateStatusWarnings();
    });
    
    // Form submission validation
    $('#editBookingForm').on('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
            return false;
        }
        
        // Show confirmation for critical changes
        if (hasCriticalChanges()) {
            if (!confirm('You are making critical changes that may affect pricing and earnings. Are you sure you want to proceed?')) {
                e.preventDefault();
                return false;
            }
        }
    });
    
    function updateStatusWarnings() {
        const $warnings = $('#status-warnings');
        const bookingStatus = $('#booking_status').val();
        const paymentStatus = $('#payment_status').val();
        
        $warnings.empty();
        
        // Booking status warnings
        if (bookingStatus !== originalValues.booking_status) {
            let warningClass = 'alert-info';
            let warningMessage = '';
            
            switch (bookingStatus) {
                case 'confirmed':
                    warningMessage = 'Confirming this booking will automatically process earnings if payment is completed.';
                    break;
                case 'cancelled':
                    warningMessage = 'Cancelling will trigger refund processing if earnings were already processed.';
                    warningClass = 'alert-warning';
                    break;
                case 'completed':
                    warningMessage = 'Completing this booking will confirm earnings and make them available for payout.';
                    warningClass = 'alert-success';
                    break;
                case 'failed':
                    warningMessage = 'Marking as failed will prevent any further processing.';
                    warningClass = 'alert-danger';
                    break;
            }
            
            if (warningMessage) {
                $warnings.append(`
                    <div class="alert ${warningClass} alert-sm">
                        <i class="fas fa-info-circle me-2"></i>${warningMessage}
                    </div>
                `);
            }
        }
        
        // Payment status warnings
        if (paymentStatus !== originalValues.payment_status) {
            let paymentWarning = '';
            
            if (paymentStatus === 'paid' && bookingStatus === 'confirmed') {
                paymentWarning = 'Marking as paid will trigger earnings processing.';
            } else if (paymentStatus === 'refunded') {
                paymentWarning = 'Refunded status should match cancelled booking status.';
            }
            
            if (paymentWarning) {
                $warnings.append(`
                    <div class="alert alert-info alert-sm">
                        <i class="fas fa-credit-card me-2"></i>${paymentWarning}
                    </div>
                `);
            }
        }
    }
    
    function validateDates() {
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        
        if (startDate && endDate && startDate > endDate) {
            $('#end_date').addClass('is-invalid');
            if (!$('#end_date').next('.invalid-feedback').length) {
                $('#end_date').after('<div class="invalid-feedback">End date must be after start date.</div>');
            }
            return false;
        } else {
            $('#end_date').removeClass('is-invalid').next('.invalid-feedback').remove();
        }
        
        return true;
    }
    
    function hasCriticalChanges() {
        return $('#start_date').val() !== originalValues.start_date ||
               $('#end_date').val() !== originalValues.end_date ||
               $('#booking_status').val() !== originalValues.booking_status ||
               $('#payment_status').val() !== originalValues.payment_status;
    }
    
    function validateForm() {
        let isValid = true;
        
        // Required fields
        const requiredFields = ['guest_count', 'start_date', 'end_date', 'booking_status', 'payment_status', 'update_reason'];
        
        requiredFields.forEach(function(field) {
            const $field = $(`#${field}`);
            if (!$field.val()) {
                $field.addClass('is-invalid');
                isValid = false;
            } else {
                $field.removeClass('is-invalid');
            }
        });
        
        // Date validation
        if (!validateDates()) {
            isValid = false;
        }
        
        // Guest count validation
        const guestCount = parseInt($('#guest_count').val());
        if (guestCount < 1 || guestCount > 100) {
            $('#guest_count').addClass('is-invalid');
            isValid = false;
        }
        
        return isValid;
    }
    
    // Initialize warnings on load
    updateStatusWarnings();
});
</script>
@endpush

@push('styles')
<style>
.alert-sm {
    padding: 0.5rem 0.75rem;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.card-header h6 {
    color: #495057;
    font-weight: 600;
}

.badge {
    font-size: 0.75em;
}

.form-text {
    font-size: 0.8rem;
}

@media print {
    .no-print {
        display: none !important;
    }
}
</style>
@endpush

@endsection