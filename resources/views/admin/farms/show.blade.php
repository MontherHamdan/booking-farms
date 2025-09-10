@extends('admin.layout')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h4 class="font-weight-bold text-primary">
                Farm Details: {{ $farm->name_en ?: $farm->name_ar }}
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.farms.index') }}">Farms</a></li>
                    <li class="breadcrumb-item active">{{ $farm->name_en ?: $farm->name_ar }}</li>
                </ol>
            </nav>
        </div>
        <div class="col-auto">
            <div class="btn-group">
                <a href="{{ route('dashboard.farms.edit', $farm->id) }}" class="btn btn-primary">
                    <i class="fas fa-edit mr-1"></i> Edit Farm
                </a>
                <button type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split" 
                        data-bs-toggle="dropdown">
                    <span class="sr-only">Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu">
                    @if($farm->status !== 'active')
                    <li>
                        <button class="dropdown-item text-success" onclick="updateStatus('{{ $farm->id }}', 'active')">
                            <i class="fas fa-check-circle me-2"></i>Approve Farm
                        </button>
                    </li>
                    @endif
                    @if($farm->status !== 'rejected')
                    <li>
                        <button class="dropdown-item text-danger" onclick="updateStatus('{{ $farm->id }}', 'rejected')">
                            <i class="fas fa-times-circle me-2"></i>Reject Farm
                        </button>
                    </li>
                    @endif
                    @if($farm->status !== 'disabled')
                    <li>
                        <button class="dropdown-item text-warning" onclick="updateStatus('{{ $farm->id }}', 'disabled')">
                            <i class="fas fa-ban me-2"></i>Disable Farm
                        </button>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>

    <!-- Status Banner -->
    @php
        $statusConfig = [
            'pending' => ['class' => 'alert-warning', 'icon' => 'fas fa-clock', 'message' => 'This farm is pending approval.'],
            'active' => ['class' => 'alert-success', 'icon' => 'fas fa-check-circle', 'message' => 'This farm is active and visible to users.'],
            'rejected' => ['class' => 'alert-danger', 'icon' => 'fas fa-times-circle', 'message' => 'This farm has been rejected.'],
            'disabled' => ['class' => 'alert-secondary', 'icon' => 'fas fa-ban', 'message' => 'This farm is disabled.']
        ];
        $config = $statusConfig[$farm->status] ?? ['class' => 'alert-dark', 'icon' => 'fas fa-question', 'message' => 'Unknown status.'];
    @endphp
    
    <div class="alert {{ $config['class'] }} alert-dismissible">
        <i class="{{ $config['icon'] }} me-2"></i>
        <strong>Status: {{ ucfirst($farm->status) }}</strong> - {{ $config['message'] }}
        @if($farm->status === 'rejected' && isset($farm->rejection_reason))
            <div class="mt-2">
                <strong>Rejection Reason:</strong> {{ $farm->rejection_reason }}
            </div>
        @endif
    </div>

    <!-- Statistics Row -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-check fa-2x mb-2"></i>
                    <h4>{{ $stats['total_bookings'] }}</h4>
                    <p class="mb-0">Total Bookings</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <h4>{{ $stats['confirmed_bookings'] }}</h4>
                    <p class="mb-0">Confirmed</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <i class="fas fa-dollar-sign fa-2x mb-2"></i>
                    <h4>${{ number_format($stats['total_revenue'], 2) }}</h4>
                    <p class="mb-0">Total Revenue</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <i class="fas fa-star fa-2x mb-2"></i>
                    <h4>{{ $stats['average_rating'] ? number_format($stats['average_rating'], 1) : 'N/A' }}</h4>
                    <p class="mb-0">Avg Rating ({{ $stats['total_ratings'] }} reviews)</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Farm Information -->
        <div class="col-md-8">
            <!-- Basic Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Basic Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>English Name:</strong></td>
                                    <td>{{ $farm->name_en ?: 'Not provided' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Arabic Name:</strong></td>
                                    <td dir="rtl">{{ $farm->name_ar ?: 'Not provided' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Guest Capacity:</strong></td>
                                    <td>{{ $farm->guest_count }} guests</td>
                                </tr>
                                <tr>
                                    <td><strong>Deposit Rate:</strong></td>
                                    <td>{{ $farm->deposit_rate ?? 0 }}%</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td>{{ $farm->created_at->format('M d, Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Last Updated:</strong></td>
                                    <td>{{ $farm->updated_at->format('M d, Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Current Step:</strong></td>
                                    <td>{{ $farm->current_step ?? 'Not set' }}/5</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    @if($farm->description_en || $farm->description_ar)
                    <div class="mt-3">
                        <strong>Description:</strong>
                        @if($farm->description_en)
                            <div class="mt-2">
                                <h6>English:</h6>
                                <p>{{ $farm->description_en }}</p>
                            </div>
                        @endif
                        @if($farm->description_ar)
                            <div class="mt-2">
                                <h6>Arabic:</h6>
                                <p dir="rtl">{{ $farm->description_ar }}</p>
                            </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <!-- Location Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Location</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>City:</strong></td>
                                    <td>{{ $farm->city->name_en ?? 'Not set' }} ({{ $farm->city->name_ar ?? 'N/A' }})</td>
                                </tr>
                                <tr>
                                    <td><strong>Area:</strong></td>
                                    <td>{{ $farm->area->name_en ?? 'Not set' }} ({{ $farm->area->name_ar ?? 'N/A' }})</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            @if($farm->hasCoordinates())
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Coordinates:</strong></td>
                                    <td>{{ $farm->coordinates }}</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>
                                        <a href="https://www.google.com/maps?q={{ $farm->latitude }},{{ $farm->longitude }}" 
                                           target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-external-link-alt me-1"></i>View on Maps
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            @else
                            <div class="text-muted">
                                <i class="fas fa-map-pin me-2"></i>No coordinates provided
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features -->
            @if($farm->features->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-star me-2"></i>Features</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($farm->features as $feature)
                        <div class="col-md-4 mb-2">
                            <span class="badge bg-primary p-2">
                                @if($feature->icon)
                                    <i class="{{ $feature->icon }} me-1"></i>
                                @endif
                                {{ $feature->name_en }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Pricing Information -->
            @if($farm->pricing->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-dollar-sign me-2"></i>Pricing</h5>
                </div>
                <div class="card-body">
                    @foreach($farm->pricing as $pricing)
                    <div class="mb-4">
                        <h6>{{ ucfirst(str_replace('_', ' ', $pricing->price_type)) }}</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">Time Range:</small>
                                <div>{{ $pricing->time_range }}</div>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">Price Range:</small>
                                <div>${{ $pricing->min_price }} - ${{ $pricing->max_price }}</div>
                            </div>
                        </div>
                        <div class="table-responsive mt-2">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Sat</th><th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>${{ $pricing->saturday_price }}</td>
                                        <td>${{ $pricing->sunday_price }}</td>
                                        <td>${{ $pricing->monday_price }}</td>
                                        <td>${{ $pricing->tuesday_price }}</td>
                                        <td>${{ $pricing->wednesday_price }}</td>
                                        <td>${{ $pricing->thursday_price }}</td>
                                        <td>${{ $pricing->friday_price }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Offers -->
            @if($farm->offers->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-percentage me-2"></i>Offers</h5>
                </div>
                <div class="card-body">
                    @foreach($farm->offers as $offer)
                    <div class="mb-3 p-3 border rounded">
                        <div class="row">
                            <div class="col-md-8">
                                <h6>{{ $offer->percentage }}% Discount</h6>
                                <small class="text-muted">
                                    {{ $offer->start_date->format('M d, Y') }} - {{ $offer->end_date->format('M d, Y') }}
                                </small>
                            </div>
                            <div class="col-md-4 text-end">
                                <span class="badge {{ $offer->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $offer->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Recent Bookings -->
            @if($recentBookings->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Recent Bookings</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>Customer</th>
                                    <th>Dates</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentBookings as $booking)
                                <tr>
                                    <td>{{ $booking->booking_reference }}</td>
                                    <td>{{ $booking->customer_name ?: $booking->user->name }}</td>
                                    <td>{{ $booking->booking_period }}</td>
                                    <td>${{ $booking->total_amount }}</td>
                                    <td>
                                        <span class="badge bg-{{ $booking->booking_status === 'confirmed' ? 'success' : ($booking->booking_status === 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($booking->booking_status) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Owner Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Farm Owner</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        @if($farm->user->avatar)
                            <img src="{{ $farm->user->avatar }}" alt="Owner Avatar" class="rounded-circle" width="80" height="80">
                        @else
                            <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center text-white" style="width: 80px; height: 80px;">
                                <i class="fas fa-user fa-2x"></i>
                            </div>
                        @endif
                    </div>
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td><strong>Name:</strong></td>
                            <td>{{ $farm->user->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td>{{ $farm->user->email }}</td>
                        </tr>
                        @if($farm->user->phone)
                        <tr>
                            <td><strong>Phone:</strong></td>
                            <td>{{ $farm->user->phone }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td><strong>Joined:</strong></td>
                            <td>{{ $farm->user->created_at->format('M d, Y') }}</td>
                        </tr>
                    </table>
                    <div class="text-center mt-3">
                        <a href="#" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye me-1"></i>View Profile
                        </a>
                    </div>
                </div>
            </div>

            <!-- Farm Images -->
            @if($farm->images->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-images me-2"></i>Images ({{ $farm->images->count() }})</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($farm->images as $image)
                        <div class="col-6 mb-3">
                            <div class="position-relative">
                                <img src="{{ $image->image_path }}" 
                                     alt="Farm Image" 
                                     class="img-fluid rounded"
                                     style="width: 100%; height: 120px; object-fit: cover;"
                                     data-bs-toggle="modal" 
                                     data-bs-target="#imageModal"
                                     data-image="{{ $image->image_path }}"
                                     style="cursor: pointer;">
                                @if($image->is_main)
                                    <span class="position-absolute top-0 start-0 badge bg-primary m-1">Main</span>
                                @endif
                                <button class="position-absolute top-0 end-0 btn btn-sm btn-danger m-1" 
                                        onclick="deleteImage({{ $image->id }})"
                                        title="Delete Image">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
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
                        <a href="{{ route('dashboard.farms.edit', $farm->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Edit Farm
                        </a>
                        <button class="btn btn-outline-info" onclick="viewBookings()">
                            <i class="fas fa-calendar-check me-2"></i>View All Bookings
                        </button>
                        <button class="btn btn-outline-warning" onclick="viewRatings()">
                            <i class="fas fa-star me-2"></i>View Ratings
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Farm Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="Farm Image" class="img-fluid">
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Farm Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="statusForm" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="status" id="statusInput">
                    <div id="rejectionDiv" style="display: none;">
                        <label for="rejection_reason" class="form-label">Rejection Reason</label>
                        <textarea class="form-control" name="rejection_reason" id="rejection_reason" rows="3"></textarea>
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
// Image modal functionality
$('#imageModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var imageUrl = button.data('image');
    $('#modalImage').attr('src', imageUrl);
});

// Status update functionality
function updateStatus(farmId, status) {
    const modal = new bootstrap.Modal(document.getElementById('statusModal'));
    const form = document.getElementById('statusForm');
    const statusInput = document.getElementById('statusInput');
    const rejectionDiv = document.getElementById('rejectionDiv');
    const statusMessage = document.getElementById('statusMessage');
    
    form.action = `/dashboard/farms/${farmId}/status`;
    statusInput.value = status;
    
    if (status === 'rejected') {
        rejectionDiv.style.display = 'block';
        statusMessage.textContent = 'Please provide a reason for rejecting this farm:';
    } else {
        rejectionDiv.style.display = 'none';
        statusMessage.textContent = `Are you sure you want to ${status} this farm?`;
    }
    
    modal.show();
}

// Delete image functionality
function deleteImage(imageId) {
    if (confirm('Are you sure you want to delete this image?')) {
        fetch(`/dashboard/farms/{{ $farm->id }}/images/${imageId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting image');
            }
        })
        .catch(error => {
            alert('Error deleting image');
        });
    }
}

// Quick action functions
function viewBookings() {
    window.location.href = `/dashboard/bookings?farm_id={{ $farm->id }}`;
}

function viewRatings() {
    // Implement when ratings management is created
    alert('Ratings management coming soon');
}
</script>
@endpush
@endsection