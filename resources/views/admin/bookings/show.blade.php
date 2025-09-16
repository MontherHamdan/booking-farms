@extends('admin.layout')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h4 class="font-weight-bold text-primary">
                Booking Details: {{ $booking->booking_reference }}
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.bookings.index') }}">Bookings</a></li>
                    <li class="breadcrumb-item active">{{ $booking->booking_reference }}</li>
                </ol>
            </nav>
        </div>
        <div class="col-auto">
            <div class="btn-group">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-cog mr-1"></i> Actions
                </button>
                <ul class="dropdown-menu">
                    @if($booking->booking_status === 'pending')
                    <li>
                        <button class="dropdown-item text-success" onclick="updateStatus('{{ $booking->id }}', 'confirmed')">
                            <i class="fas fa-check-circle me-2"></i>Confirm Booking
                        </button>
                    </li>
                    @endif
                    @if(in_array($booking->booking_status, ['pending', 'confirmed']))
                    <li>
                        <button class="dropdown-item text-danger" onclick="updateStatus('{{ $booking->id }}', 'cancelled')">
                            <i class="fas fa-times-circle me-2"></i>Cancel Booking
                        </button>
                    </li>
                    @endif
                    @if($booking->booking_status === 'confirmed' && !$booking->hasEnded())
                    <li>
                        <button class="dropdown-item text-primary" onclick="updateStatus('{{ $booking->id }}', 'completed')">
                            <i class="fas fa-flag-checkered me-2"></i>Mark as Complete
                        </button>
                    </li>
                    @endif
                    @if($booking->booking_status === 'failed')
                    <li>
                        <button class="dropdown-item text-warning" onclick="updateStatus('{{ $booking->id }}', 'pending')">
                            <i class="fas fa-undo me-2"></i>Reset to Pending
                        </button>
                    </li>
                    @endif
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('dashboard.bookings.edit', $booking->id) }}">
                            <i class="fas fa-edit me-2"></i>Edit Booking
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('dashboard.farms.show', $booking->farm->id) }}">
                            <i class="fas fa-home me-2"></i>View Farm
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#" onclick="printBooking()">
                            <i class="fas fa-print me-2"></i>Print Details
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Status Banner -->
    @php
        $statusConfig = [
            'pending' => ['class' => 'alert-warning', 'icon' => 'fas fa-clock', 'message' => 'This booking is pending confirmation.'],
            'confirmed' => ['class' => 'alert-success', 'icon' => 'fas fa-check-circle', 'message' => 'This booking is confirmed.'],
            'cancelled' => ['class' => 'alert-danger', 'icon' => 'fas fa-times-circle', 'message' => 'This booking has been cancelled.'],
            'failed' => ['class' => 'alert-dark', 'icon' => 'fas fa-exclamation-triangle', 'message' => 'This booking has failed.'],
            'completed' => ['class' => 'alert-primary', 'icon' => 'fas fa-flag-checkered', 'message' => 'This booking is completed.']
        ];
        $config = $statusConfig[$booking->booking_status] ?? ['class' => 'alert-secondary', 'icon' => 'fas fa-question', 'message' => 'Unknown status.'];
    @endphp
    
    <div class="alert {{ $config['class'] }} alert-dismissible">
        <i class="{{ $config['icon'] }} me-2"></i>
        <strong>Status: {{ ucfirst($booking->booking_status) }}</strong> - {{ $config['message'] }}
        <div class="mt-2">
            <strong>Payment Status:</strong> 
            <span class="badge bg-{{ $booking->payment_status === 'paid' ? 'success' : ($booking->payment_status === 'pending' ? 'warning' : 'danger') }}">
                {{ ucfirst(str_replace('_', ' ', $booking->payment_status)) }}
            </span>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-md-8">
            <!-- Booking Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Booking Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Reference:</strong></td>
                                    <td>{{ $booking->booking_reference }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Price Type:</strong></td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $booking->price_type)) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Booking Period:</strong></td>
                                    <td>{{ $booking->booking_period }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Time Range:</strong></td>
                                    <td>{{ $booking->booking_time_range ?: 'Not specified' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Guest Count:</strong></td>
                                    <td>{{ $booking->guest_count }} guests</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td>{{ $booking->created_at->format('M d, Y \a\t g:i A') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Last Updated:</strong></td>
                                    <td>{{ $booking->updated_at->format('M d, Y \a\t g:i A') }}</td>
                                </tr>
                                @if($booking->expires_at)
                                <tr>
                                    <td><strong>Expires At:</strong></td>
                                    <td>
                                        {{ $booking->expires_at->format('M d, Y \a\t g:i A') }}
                                        @if($booking->isPaymentExpired())
                                            <span class="badge bg-danger">Expired</span>
                                        @endif
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    @if($booking->notes)
                    <div class="mt-3">
                        <strong>Customer Notes:</strong>
                        <div class="border rounded p-3 mt-2 bg-light">
                            {{ $booking->notes }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Farm Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-home me-2"></i>Farm Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            @if($booking->farm->images->where('is_main', true)->first())
                                <img src="{{ $booking->farm->images->where('is_main', true)->first()->image_path }}" 
                                     alt="Farm Image" 
                                     class="img-fluid rounded"
                                     style="width: 100%; height: 200px; object-fit: cover;">
                            @else
                                <div class="bg-secondary rounded d-flex align-items-center justify-content-center text-white" 
                                     style="width: 100%; height: 200px;">
                                    <i class="fas fa-home fa-3x"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-8">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Farm Name:</strong></td>
                                    <td>{{ $booking->farm->name_en ?: $booking->farm->name_ar }}</td>
                                </tr>
                                @if($booking->farm->name_ar && $booking->farm->name_en)
                                <tr>
                                    <td><strong>Arabic Name:</strong></td>
                                    <td dir="rtl">{{ $booking->farm->name_ar }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td><strong>Location:</strong></td>
                                    <td>
                                        {{ $booking->farm->city->name_en ?? 'N/A' }}
                                        @if($booking->farm->area)
                                            - {{ $booking->farm->area->name_en }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Farm Owner:</strong></td>
                                    <td>{{ $booking->farm->user->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Owner Contact:</strong></td>
                                    <td>
                                        {{ $booking->farm->user->email }}
                                        @if($booking->farm->user->phone)
                                            <br><small>{{ $booking->farm->user->phone }}</small>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                            <div class="mt-3">
                                <a href="{{ route('dashboard.farms.show', $booking->farm->id) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-external-link-alt me-1"></i>View Farm Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Payment Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Subtotal:</strong></td>
                                    <td>${{ number_format($booking->subtotal, 2) }}</td>
                                </tr>
                                @if($booking->discount_amount > 0)
                                <tr>
                                    <td><strong>Offer Discount:</strong></td>
                                    <td class="text-success">-${{ number_format($booking->discount_amount, 2) }}</td>
                                </tr>
                                @endif
                                @if($booking->coupon_code)
                                <tr>
                                    <td><strong>Coupon ({{ $booking->coupon_code }}):</strong></td>
                                    <td class="text-success">-${{ number_format($booking->coupon_discount_amount, 2) }}</td>
                                </tr>
                                @endif
                                <tr class="border-top">
                                    <td><strong>Total Amount:</strong></td>
                                    <td><strong>${{ number_format($booking->total_amount, 2) }}</strong></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Payment Option:</strong></td>
                                    <td>{{ ucfirst($booking->payment_option) }}</td>
                                </tr>
                                @if($booking->hasDepositPayment())
                                <tr>
                                    <td><strong>Deposit Amount:</strong></td>
                                    <td>${{ number_format($booking->deposit_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Remaining Amount:</strong></td>
                                    <td>${{ number_format($booking->remaining_amount, 2) }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td><strong>Amount Paid:</strong></td>
                                    <td class="text-success">${{ number_format($booking->amount_paid, 2) }}</td>
                                </tr>
                                @if($booking->stripe_payment_intent_id)
                                <tr>
                                    <td><strong>Payment Intent:</strong></td>
                                    <td><small class="text-muted">{{ $booking->stripe_payment_intent_id }}</small></td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    @if($booking->coupon)
                    <div class="mt-3">
                        <div class="alert alert-info">
                            <i class="fas fa-ticket-alt me-2"></i>
                            <strong>Coupon Applied:</strong> {{ $booking->coupon->name }}
                            @if($booking->coupon->discount_description)
                                <div class="mt-1">{{ $booking->coupon->discount_description }}</div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Booking Dates -->
            @if($booking->booking_dates && count($booking->booking_dates) > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Booking Dates</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($booking->formatted_booking_dates as $dateInfo)
                        <div class="col-md-3 mb-2">
                            <div class="border rounded p-2 text-center">
                                <div class="font-weight-bold">{{ $dateInfo['human_readable'] }}</div>
                                <small class="text-muted">{{ $dateInfo['day_name'] }}</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Customer Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Customer Information</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        @if($booking->user && $booking->user->avatar)
                            <img src="{{ $booking->user->avatar }}" alt="Customer Avatar" class="rounded-circle" width="80" height="80">
                        @else
                            <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center text-white" style="width: 80px; height: 80px;">
                                <i class="fas fa-user fa-2x"></i>
                            </div>
                        @endif
                    </div>
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td><strong>Name:</strong></td>
                            <td>{{ $booking->customer_name ?: ($booking->user->name ?? 'N/A') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td>{{ $booking->customer_email ?: ($booking->user->email ?? 'N/A') }}</td>
                        </tr>
                        @if($booking->customer_phone || ($booking->user && $booking->user->phone))
                        <tr>
                            <td><strong>Phone:</strong></td>
                            <td>{{ $booking->customer_phone ?: $booking->user->phone }}</td>
                        </tr>
                        @endif
                        @if($booking->user)
                        <tr>
                            <td><strong>Joined:</strong></td>
                            <td>{{ $booking->user->created_at->format('M d, Y') }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            <!-- Related Bookings -->
            @if($relatedBookings->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Customer's Other Bookings</h5>
                </div>
                <div class="card-body">
                    @foreach($relatedBookings as $relatedBooking)
                    <div class="border-bottom pb-2 mb-2">
                        <div class="d-flex justify-content-between">
                            <div>
                                <small class="text-muted">{{ $relatedBooking->booking_reference }}</small>
                                <div class="font-weight-bold">{{ $relatedBooking->farm->name_en ?: $relatedBooking->farm->name_ar }}</div>
                                <small class="text-muted">{{ $relatedBooking->created_at->format('M d, Y') }}</small>
                            </div>
                            <div class="text-end">
                                <div>${{ number_format($relatedBooking->total_amount, 0) }}</div>
                                <span class="badge bg-{{ $relatedBooking->booking_status === 'confirmed' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($relatedBooking->booking_status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    <div class="text-center mt-3">
                        <a href="{{ route('dashboard.bookings.index', ['search' => $booking->user->email]) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-external-link-alt me-1"></i>View All
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($booking->booking_status === 'pending')
                        <button class="btn btn-success" onclick="updateStatus('{{ $booking->id }}', 'confirmed')">
                            <i class="fas fa-check-circle me-2"></i>Confirm Booking
                        </button>
                        @endif
                        @if(in_array($booking->booking_status, ['pending', 'confirmed']))
                        <button class="btn btn-danger" onclick="updateStatus('{{ $booking->id }}', 'cancelled')">
                            <i class="fas fa-times-circle me-2"></i>Cancel Booking
                        </button>
                        @endif
                        <a href="{{ route('dashboard.farms.show', $booking->farm->id) }}" class="btn btn-outline-primary">
                            <i class="fas fa-home me-2"></i>View Farm
                        </a>
                        <button class="btn btn-outline-secondary" onclick="printBooking()">
                            <i class="fas fa-print me-2"></i>Print Details
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Booking Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="statusForm" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="status" id="statusInput">
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason (Optional)</label>
                        <textarea class="form-control" name="reason" id="reason" rows="3" placeholder="Please provide a reason for this status change..."></textarea>
                    </div>
                    <p id="statusMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Confirm</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Status update functionality
function updateStatus(bookingId, status) {
    const modal = new bootstrap.Modal(document.getElementById('statusModal'));
    const form = document.getElementById('statusForm');
    const statusInput = document.getElementById('statusInput');
    const statusMessage = document.getElementById('statusMessage');
    
    form.action = `/dashboard/bookings/${bookingId}/status`;
    statusInput.value = status;
    
    statusMessage.textContent = `Are you sure you want to ${status} this booking?`;
    
    modal.show();
}

// Print functionality
function printBooking() {
    window.print();
}

// Print styles
@media print {
    .btn, .dropdown, .alert-dismissible .btn-close, nav {
        display: none !important;
    }
    .card {
        border: 1px solid #000 !important;
        box-shadow: none !important;
    }
}
</script>
@endpush
@endsection