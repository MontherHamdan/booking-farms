@extends('admin.layout')

@section('content')
<div class="container-fluid">
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h4 class="font-weight-bold text-primary">Coupon Usage Details</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard.coupons.index') }}">Coupons</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        {{ $coupon->name }} Usage
                    </li>
                </ol>
            </nav>
        </div>
        <div class="col-auto">
            <a href="{{ route('dashboard.coupons.edit', $coupon->id) }}" class="btn btn-sm btn-outline-primary mr-2">
                <i class="fas fa-edit mr-1"></i> Edit Coupon
            </a>
            <a href="{{ route('dashboard.coupons.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back to Coupons
            </a>
        </div>
    </div>

    <!-- Coupon Summary -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-ticket-alt mr-2"></i>{{ $coupon->name }}
                <code class="bg-white text-primary ml-2 px-2 py-1 rounded">{{ $coupon->code }}</code>
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="text-center">
                        <h3 class="text-info mb-1">{{ $coupon->usages_count }}</h3>
                        <small class="text-muted">Total Uses</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h5 class="mb-1">
                            <span class="badge {{ $coupon->discount_type === \App\Models\Coupon::DISCOUNT_TYPE_PERCENTAGE ? 'bg-info' : 'bg-success' }} text-white">
                                {{ $coupon->discount_description }}
                            </span>
                        </h5>
                        <small class="text-muted">Discount</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h5 class="mb-1">
                            <span class="badge {{ $coupon->is_active ? 'bg-success' : 'bg-secondary' }} text-white">
                                {{ $coupon->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </h5>
                        <small class="text-muted">Status</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h5 class="mb-1">
                            @if($coupon->usage_limit)
                                {{ $coupon->remaining_usages }} / {{ $coupon->usage_limit }}
                            @else
                                <span class="text-success">Unlimited</span>
                            @endif
                        </h5>
                        <small class="text-muted">Remaining Uses</small>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-4">
                    <strong>Period:</strong> 
                    {{ $coupon->start_date->format('M d, Y H:i') }} - {{ $coupon->end_date->format('M d, Y H:i') }}
                </div>
                <div class="col-md-4">
                    <strong>Platform:</strong> {{ $coupon->platform_label }}
                </div>
                <div class="col-md-4">
                    <strong>Cities:</strong> 
                    @if(empty($coupon->cities))
                        <span class="text-success">All Cities</span>
                    @else
                        {{ implode(', ', $coupon->city_names) }}
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($usages->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="fas fa-chart-line fa-3x mb-3"></i>
            <h5>No Usage Data</h5>
            <p>This coupon hasn't been used yet.</p>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-list mr-2"></i>Usage History
                    <span class="badge bg-info text-white ml-2">{{ $usages->total() }} total</span>
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-center">User</th>
                                <th class="text-center">Booking ID</th>
                                <th class="text-center">Used At</th>
                                <th class="text-center">Booking Details</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($usages as $usage)
                            <tr>
                                <td class="text-center align-middle">
                                    {{ ($usages->currentPage() - 1) * $usages->perPage() + $loop->iteration }}
                                </td>
                                <td class="align-middle">
                                    @if($usage->user)
                                        <div>
                                            <strong>{{ $usage->user->name }}</strong>
                                            <br>
                                            @if($usage->user->email)
                                                <small class="text-muted">{{ $usage->user->email }}</small>
                                            @endif
                                            @if($usage->user->phone)
                                                <br><small class="text-muted">{{ $usage->user->phone }}</small>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">User not found</span>
                                    @endif
                                </td>
                                <td class="text-center align-middle">
                                    @if($usage->booking)
                                        <a href="#" class="text-primary font-weight-bold">
                                            #{{ $usage->booking_id }}
                                        </a>
                                    @else
                                        <span class="text-muted">#{{ $usage->booking_id }}</span>
                                        <br><small class="text-danger">Booking not found</small>
                                    @endif
                                </td>
                                <td class="text-center align-middle">
                                    <div>
                                        <strong>{{ $usage->used_at->format('M d, Y') }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $usage->used_at->format('H:i A') }}</small>
                                        <br>
                                        <small class="text-muted">{{ $usage->used_at->diffForHumans() }}</small>
                                    </div>
                                </td>
                                <td class="align-middle">
                                    @if($usage->booking)
                                        <div class="small">
                                            <strong>Farm:</strong> {{ $usage->booking->farm->name ?? 'N/A' }}
                                            <br>
                                            <strong>Date:</strong> {{ $usage->booking->booking_date ? $usage->booking->booking_date->format('M d, Y') : 'N/A' }}
                                            <br>
                                            <strong>Status:</strong> 
                                            <span class="badge bg-{{ $usage->booking->status === 'confirmed' ? 'success' : ($usage->booking->status === 'cancelled' ? 'danger' : 'warning') }} text-white">
                                                {{ ucfirst($usage->booking->status ?? 'Unknown') }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-muted">Booking details not available</span>
                                    @endif
                                </td>
                                <td class="text-center align-middle">
                                    <div class="btn-group-vertical btn-group-sm">
                                        @if($usage->user)
                                            <a href="#" class="btn btn-outline-info btn-sm" title="View User">
                                                <i class="fas fa-user"></i>
                                            </a>
                                        @endif
                                        @if($usage->booking)
                                            <a href="#" class="btn btn-outline-primary btn-sm" title="View Booking">
                                                <i class="fas fa-calendar-alt"></i>
                                            </a>
                                        @endif
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
                        Showing {{ $usages->firstItem() }} to {{ $usages->lastItem() }} of {{ $usages->total() }} usage records
                        <br>
                        Coupon created: {{ $coupon->created_at->format('M d, Y H:i A') }}
                    </div>
                    <div>
                        {{ $usages->links() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Usage Statistics -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-chart-bar mr-2"></i>Usage by Day (Last 7 days)
                        </h6>
                    </div>
                    <div class="card-body">
                        @php
                            $dailyUsage = $usages->groupBy(function($usage) {
                                return $usage->used_at->format('Y-m-d');
                            })->map->count()->take(7);
                        @endphp
                        @if($dailyUsage->count() > 0)
                            @foreach($dailyUsage as $date => $count)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>{{ \Carbon\Carbon::parse($date)->format('M d') }}</span>
                                    <span class="badge bg-primary">{{ $count }}</span>
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted">No recent usage data</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-users mr-2"></i>Top Users
                        </h6>
                    </div>
                    <div class="card-body">
                        @php
                            $topUsers = $usages->groupBy('user_id')->map(function($userUsages) {
                                return [
                                    'user' => $userUsages->first()->user,
                                    'count' => $userUsages->count()
                                ];
                            })->sortByDesc('count')->take(5);
                        @endphp
                        @if($topUsers->count() > 0)
                            @foreach($topUsers as $userData)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <strong>{{ $userData['user']->name ?? 'Unknown User' }}</strong>
                                        @if($userData['user'] && $userData['user']->email)
                                            <br><small class="text-muted">{{ $userData['user']->email }}</small>
                                        @endif
                                    </div>
                                    <span class="badge bg-info">{{ $userData['count'] }} use{{ $userData['count'] > 1 ? 's' : '' }}</span>
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted">No user data available</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection