@extends('admin.layout')

@section('content')
<div class="container-fluid">
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h4 class="font-weight-bold text-primary">Booking Reports & Analytics</h4>
        </div>
        <div class="col-auto">
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-download mr-1"></i> Export Reports
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#"><i class="fas fa-file-excel mr-2"></i>Export Excel</a></li>
                    <li><a class="dropdown-item" href="#"><i class="fas fa-file-pdf mr-2"></i>Export PDF</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard.bookings.reports') }}" class="row align-items-end">
                <div class="col-md-3">
                    <label for="start_date" class="form-label font-weight-bold">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" 
                           value="{{ $startDate->format('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label font-weight-bold">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" 
                           value="{{ $endDate->format('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-chart-bar mr-1"></i> Generate Report
                    </button>
                </div>
                <div class="col-md-3 text-end">
                    <small class="text-muted">
                        Period: {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}
                    </small>
                </div>
            </form>
        </div>
    </div>

    <!-- Revenue Summary -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-dollar-sign fa-2x mb-2"></i>
                    <h4>${{ number_format($revenueByStatus->sum('total'), 0) }}</h4>
                    <p class="mb-0">Total Revenue</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <h4>${{ number_format($revenueByStatus->where('booking_status', 'confirmed')->first()->total ?? 0, 0) }}</h4>
                    <p class="mb-0">Confirmed Revenue</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x mb-2"></i>
                    <h4>${{ number_format($revenueByStatus->where('booking_status', 'pending')->first()->total ?? 0, 0) }}</h4>
                    <p class="mb-0">Pending Revenue</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <i class="fas fa-chart-line fa-2x mb-2"></i>
                    @php
                        $totalRevenue = $revenueByStatus->sum('total');
                        $confirmedRevenue = $revenueByStatus->where('booking_status', 'confirmed')->first()->total ?? 0;
                        $successRate = $totalRevenue > 0 ? ($confirmedRevenue / $totalRevenue) * 100 : 0;
                    @endphp
                    <h4>{{ number_format($successRate, 1) }}%</h4>
                    <p class="mb-0">Success Rate</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Revenue by Status -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Revenue by Status</h5>
                </div>
                <div class="card-body">
                    @if($revenueByStatus->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Status</th>
                                        <th class="text-end">Revenue</th>
                                        <th class="text-end">Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($revenueByStatus as $status)
                                    <tr>
                                        <td>
                                            <span class="badge bg-{{ $status->booking_status === 'confirmed' ? 'success' : ($status->booking_status === 'pending' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($status->booking_status) }}
                                            </span>
                                        </td>
                                        <td class="text-end">${{ number_format($status->total, 2) }}</td>
                                        <td class="text-end">{{ number_format(($status->total / $revenueByStatus->sum('total')) * 100, 1) }}%</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center">No revenue data for selected period.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Bookings by Price Type -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Bookings by Price Type</h5>
                </div>
                <div class="card-body">
                    @if($bookingsByPriceType->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Price Type</th>
                                        <th class="text-end">Count</th>
                                        <th class="text-end">Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bookingsByPriceType as $priceType)
                                    <tr>
                                        <td>{{ ucfirst(str_replace('_', ' ', $priceType->price_type)) }}</td>
                                        <td class="text-end">{{ $priceType->count }}</td>
                                        <td class="text-end">{{ number_format(($priceType->count / $bookingsByPriceType->sum('count')) * 100, 1) }}%</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center">No booking data for selected period.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Top Performing Farms -->
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Top Performing Farms</h5>
                </div>
                <div class="card-body">
                    @if($topFarms->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Farm Name</th>
                                        <th class="text-center">Bookings</th>
                                        <th class="text-end">Revenue</th>
                                        <th class="text-end">Avg. Booking Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topFarms as $index => $farm)
                                    <tr>
                                        <td>
                                            @if($index === 0)
                                                <i class="fas fa-crown text-warning"></i> #1
                                            @elseif($index === 1)
                                                <i class="fas fa-medal text-secondary"></i> #2
                                            @elseif($index === 2)
                                                <i class="fas fa-award text-warning"></i> #3
                                            @else
                                                #{{ $index + 1 }}
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $farm->farm->name_en ?: $farm->farm->name_ar }}</strong>
                                            <div class="small text-muted">ID: {{ $farm->farm->id }}</div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary">{{ $farm->bookings_count }}</span>
                                        </td>
                                        <td class="text-end">
                                            <strong>${{ number_format($farm->total_revenue, 2) }}</strong>
                                        </td>
                                        <td class="text-end">
                                            ${{ number_format($farm->total_revenue / $farm->bookings_count, 2) }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center">No farm data for selected period.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Top Customers -->
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Top Customers</h5>
                </div>
                <div class="card-body">
                    @if($topCustomers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Customer</th>
                                        <th class="text-center">Bookings</th>
                                        <th class="text-end">Total Spent</th>
                                        <th class="text-end">Avg. Booking Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topCustomers as $index => $customer)
                                    <tr>
                                        <td>
                                            @if($index === 0)
                                                <i class="fas fa-star text-warning"></i> #1
                                            @else
                                                #{{ $index + 1 }}
                                            @endif
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $customer->user->name }}</strong>
                                                <div class="small text-muted">{{ $customer->user->email }}</div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info">{{ $customer->bookings_count }}</span>
                                        </td>
                                        <td class="text-end">
                                            <strong>${{ number_format($customer->total_spent, 2) }}</strong>
                                        </td>
                                        <td class="text-end">
                                            ${{ number_format($customer->total_spent / $customer->bookings_count, 2) }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center">No customer data for selected period.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection