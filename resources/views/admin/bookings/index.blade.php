@extends('admin.layout')

@section('content')
<div class="container-fluid">
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h4 class="font-weight-bold text-primary">Booking Management</h4>
        </div>
        <div class="col-auto">
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-download mr-1"></i> Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('dashboard.bookings.export', request()->query()) }}"><i class="fas fa-file-csv mr-2"></i>Export CSV</a></li>
                    <li><a class="dropdown-item" href="{{ route('dashboard.bookings.reports') }}"><i class="fas fa-chart-bar mr-2"></i>View Reports</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    @php
        $totalBookings = \App\Models\FarmBooking::count();
        $confirmedBookings = \App\Models\FarmBooking::where('booking_status', 'confirmed')->count();
        $pendingBookings = \App\Models\FarmBooking::where('booking_status', 'pending')->count();
        $totalRevenue = \App\Models\FarmBooking::where('booking_status', 'confirmed')->sum('total_amount');
    @endphp
    
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Total Bookings</h5>
                            <h3 class="mb-0">{{ number_format($totalBookings) }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar-check fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Confirmed</h5>
                            <h3 class="mb-0">{{ number_format($confirmedBookings) }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Pending</h5>
                            <h3 class="mb-0">{{ number_format($pendingBookings) }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Total Revenue</h5>
                            <h3 class="mb-0">${{ number_format($totalRevenue, 0) }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-dollar-sign fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Filters -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard.bookings.index') }}" id="filterForm">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label for="search" class="form-label font-weight-bold">Search</label>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               placeholder="Reference, customer, farm..."
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="booking_status" class="form-label font-weight-bold">Booking Status</label>
                        <select class="form-control" id="booking_status" name="booking_status">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('booking_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="confirmed" {{ request('booking_status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="cancelled" {{ request('booking_status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            <option value="failed" {{ request('booking_status') == 'failed' ? 'selected' : '' }}>Failed</option>
                            <option value="completed" {{ request('booking_status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="payment_status" class="form-label font-weight-bold">Payment Status</label>
                        <select class="form-control" id="payment_status" name="payment_status">
                            <option value="">All Payments</option>
                            <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="partially_paid" {{ request('payment_status') == 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                            <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Failed</option>
                            <option value="expired" {{ request('payment_status') == 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="refunded" {{ request('payment_status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="farm_id" class="form-label font-weight-bold">Farm</label>
                        <select class="form-control" id="farm_id" name="farm_id">
                            <option value="">All Farms</option>
                            @foreach($farms as $farm)
                                <option value="{{ $farm->id }}" {{ request('farm_id') == $farm->id ? 'selected' : '' }}>
                                    {{ $farm->name_en ?: $farm->name_ar }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="btn-group w-100">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-search mr-1"></i> Filter
                            </button>
                            <a href="{{ route('dashboard.bookings.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times mr-1"></i> Clear
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Additional Filters Row -->
                <div class="row mt-3">
                    <div class="col-md-2">
                        <label for="date_from" class="form-label font-weight-bold">Date From</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="date_to" class="form-label font-weight-bold">Date To</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="amount_from" class="form-label font-weight-bold">Amount From</label>
                        <input type="number" class="form-control" id="amount_from" name="amount_from" value="{{ request('amount_from') }}" placeholder="0">
                    </div>
                    <div class="col-md-2">
                        <label for="amount_to" class="form-label font-weight-bold">Amount To</label>
                        <input type="number" class="form-control" id="amount_to" name="amount_to" value="{{ request('amount_to') }}" placeholder="1000">
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($bookings->isEmpty())
        <div class="text-center py-4 text-muted">
            <i class="fas fa-info-circle fa-2x mb-2"></i>
            <p>
                @if(request()->hasAny(['search', 'booking_status', 'payment_status', 'farm_id', 'date_from', 'date_to']))
                    No bookings found matching your filters. 
                    <a href="{{ route('dashboard.bookings.index') }}" class="text-primary">Clear filters</a> to see all bookings.
                @else
                    No bookings yet.
                @endif
            </p>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-center">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'booking_reference', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="text-dark">
                                        Reference <i class="fas fa-sort"></i>
                                    </a>
                                </th>
                                <th class="text-center">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'farm_name', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="text-dark">
                                        Farm <i class="fas fa-sort"></i>
                                    </a>
                                </th>
                                <th class="text-center">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'customer_name', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="text-dark">
                                        Customer <i class="fas fa-sort"></i>
                                    </a>
                                </th>
                                <th class="text-center">Booking Period</th>
                                <th class="text-center">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'total_amount', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="text-dark">
                                        Amount <i class="fas fa-sort"></i>
                                    </a>
                                </th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Payment</th>
                                <th class="text-center">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="text-dark">
                                        Created <i class="fas fa-sort"></i>
                                    </a>
                                </th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bookings as $booking)
                            <tr>
                                <td class="text-center align-middle">
                                    <div>
                                        <strong>{{ $booking->booking_reference }}</strong>
                                        @if($booking->coupon_code)
                                            <div class="small text-success">
                                                <i class="fas fa-ticket-alt"></i> {{ $booking->coupon_code }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="align-middle">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            @if($booking->farm->images->where('is_main', true)->first())
                                                <img src="{{ $booking->farm->images->where('is_main', true)->first()->image_path }}" 
                                                     alt="Farm Image" 
                                                     class="rounded" 
                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                            @else
                                                <div class="bg-secondary rounded d-flex align-items-center justify-content-center text-white" 
                                                     style="width: 40px; height: 40px;">
                                                    <i class="fas fa-home"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <h6 class="mb-1">{{ $booking->farm->name_en ?: $booking->farm->name_ar }}</h6>
                                            <small class="text-muted">ID: {{ $booking->farm->id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center align-middle">
                                    <div>
                                        <strong>{{ $booking->customer_name ?: $booking->user->name }}</strong>
                                        <div class="small text-muted">{{ $booking->customer_email ?: $booking->user->email }}</div>
                                        @if($booking->customer_phone)
                                            <div class="small text-muted">{{ $booking->customer_phone }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center align-middle">
                                    <div>
                                        <strong>{{ $booking->booking_period }}</strong>
                                        <div class="small text-muted">
                                            {{ ucfirst(str_replace('_', ' ', $booking->price_type)) }}
                                        </div>
                                        @if($booking->guest_count)
                                            <div class="small text-info">
                                                <i class="fas fa-users"></i> {{ $booking->guest_count }} guests
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center align-middle">
                                    <div>
                                        <strong>${{ number_format($booking->total_amount, 2) }}</strong>
                                        @if($booking->discount_amount > 0 || $booking->coupon_discount_amount > 0)
                                            <div class="small text-success">
                                                Saved: ${{ number_format(($booking->discount_amount ?? 0) + ($booking->coupon_discount_amount ?? 0), 2) }}
                                            </div>
                                        @endif
                                        @if($booking->hasDepositPayment())
                                            <div class="small text-warning">
                                                Deposit: ${{ number_format($booking->deposit_amount, 2) }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center align-middle">
                                    @php
                                        $statusConfig = [
                                            'pending' => ['class' => 'bg-warning text-dark', 'icon' => 'fas fa-clock'],
                                            'confirmed' => ['class' => 'bg-success', 'icon' => 'fas fa-check-circle'],
                                            'cancelled' => ['class' => 'bg-danger', 'icon' => 'fas fa-times-circle'],
                                            'failed' => ['class' => 'bg-dark', 'icon' => 'fas fa-exclamation-triangle'],
                                            'completed' => ['class' => 'bg-primary', 'icon' => 'fas fa-flag-checkered']
                                        ];
                                        $config = $statusConfig[$booking->booking_status] ?? ['class' => 'bg-secondary', 'icon' => 'fas fa-question'];
                                    @endphp
                                    <span class="badge rounded-pill {{ $config['class'] }}">
                                        <i class="{{ $config['icon'] }} me-1"></i>
                                        {{ ucfirst($booking->booking_status) }}
                                    </span>
                                </td>
                                <td class="text-center align-middle">
                                    @php
                                        $paymentConfig = [
                                            'pending' => ['class' => 'bg-warning text-dark', 'icon' => 'fas fa-clock'],
                                            'paid' => ['class' => 'bg-success', 'icon' => 'fas fa-check'],
                                            'partially_paid' => ['class' => 'bg-info', 'icon' => 'fas fa-coins'],
                                            'failed' => ['class' => 'bg-danger', 'icon' => 'fas fa-times'],
                                            'refunded' => ['class' => 'bg-secondary', 'icon' => 'fas fa-undo']
                                        ];
                                        $payConfig = $paymentConfig[$booking->payment_status] ?? ['class' => 'bg-dark', 'icon' => 'fas fa-question'];
                                    @endphp
                                    <span class="badge rounded-pill {{ $payConfig['class'] }}">
                                        <i class="{{ $payConfig['icon'] }} me-1"></i>
                                        {{ ucfirst(str_replace('_', ' ', $booking->payment_status)) }}
                                    </span>
                                </td>
                                <td class="text-center align-middle">
                                    <div class="small">
                                        {{ $booking->created_at->format('M d, Y') }}
                                        <div class="text-muted">{{ $booking->created_at->format('g:i A') }}</div>
                                    </div>
                                </td>
                                <td class="text-center align-middle">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" 
                                                type="button" 
                                                data-bs-toggle="dropdown">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('dashboard.bookings.show', $booking->id) }}">
                                                    <i class="fas fa-eye me-2"></i>View Details
                                                </a>
                                            </li>
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
                                            <li><hr class="dropdown-divider"></li>
                                            @if($booking->booking_status === 'pending')
                                            <li>
                                                <button class="dropdown-item text-success" 
                                                        onclick="updateBookingStatus({{ $booking->id }}, 'confirmed')">
                                                    <i class="fas fa-check-circle me-2"></i>Confirm
                                                </button>
                                            </li>
                                            @endif
                                            @if(in_array($booking->booking_status, ['pending', 'confirmed']))
                                            <li>
                                                <button class="dropdown-item text-danger" 
                                                        onclick="updateBookingStatus({{ $booking->id }}, 'cancelled')">
                                                    <i class="fas fa-times-circle me-2"></i>Cancel
                                                </button>
                                            </li>
                                            @endif
                                            @if($booking->booking_status === 'confirmed' && !$booking->hasEnded())
                                            <li>
                                                <button class="dropdown-item text-primary" 
                                                        onclick="updateBookingStatus({{ $booking->id }}, 'completed')">
                                                    <i class="fas fa-flag-checkered me-2"></i>Complete
                                                </button>
                                            </li>
                                            @endif
                                            @if($booking->booking_status === 'failed')
                                            <li>
                                                <button class="dropdown-item text-warning" 
                                                        onclick="updateBookingStatus({{ $booking->id }}, 'pending')">
                                                    <i class="fas fa-undo me-2"></i>Reset
                                                </button>
                                            </li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-top-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Showing {{ $bookings->firstItem() }} to {{ $bookings->lastItem() }} of {{ $bookings->total() }} bookings
                        | Total Revenue: ${{ number_format($bookings->where('booking_status', 'confirmed')->sum('total_amount'), 2) }}
                    </div>
                    <div>
                        {{ $bookings->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusUpdateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Booking Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="statusUpdateForm" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="status" id="modalStatus">
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason (Optional)</label>
                        <textarea class="form-control" 
                                  id="reason" 
                                  name="reason" 
                                  rows="3" 
                                  placeholder="Please provide a reason for this status change..."></textarea>
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
$(document).ready(function() {
    // Auto-submit form on filter change
    const searchInput = document.getElementById('search');
    const filterSelects = ['booking_status', 'payment_status', 'farm_id', 'date_from', 'date_to', 'amount_from', 'amount_to'];
    
    let debounceTimer;
    
    // Debounced search for text input
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() {
            document.getElementById('filterForm').submit();
        }, 500);
    });
    
    // Immediate submit for dropdowns and date inputs
    filterSelects.forEach(selectId => {
        const element = document.getElementById(selectId);
        if (element) {
            element.addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
        }
    });
});

function updateBookingStatus(bookingId, status) {
    const modal = new bootstrap.Modal(document.getElementById('statusUpdateModal'));
    const form = document.getElementById('statusUpdateForm');
    const modalStatus = document.getElementById('modalStatus');
    const statusMessage = document.getElementById('statusMessage');
    
    // Set form action
    form.action = `/dashboard/bookings/${bookingId}/status`;
    modalStatus.value = status;
    
    statusMessage.textContent = `Are you sure you want to ${status} this booking?`;
    
    modal.show();
}
</script>
@endpush
@endsection