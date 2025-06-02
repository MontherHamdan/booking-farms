@extends('admin.layout')
@section('content')
<style>
    /* Enhanced Card Styling */
    .card-enhanced {
      border: none;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .card-enhanced:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }
    /* Icon Circle Styling */
    .icon-circle {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    /* Typography for Card Titles & Counts */
    .card-title {
      font-size: 14px;
      color: #6c757d;
      margin-bottom: 0.25rem;
    }
    .card-count {
      font-size: 24px;
      font-weight: 700;
      margin: 0;
    }
  </style>
  
    {{-- order cards --}}
    {{-- @include('admin.partials.order_cards') --}}
    
    {{-- order charts --}}
    {{-- @include('admin.partials.order_charts') --}}

    {{-- first 4 users --}}
    <div class="row">
        @foreach($recentUsers as $user)
            @php
                // Define an array of 4 color classes
                $colors = ['text-warning', 'text-pink', 'text-success', 'text-primary'];
                // Use the loop index to assign a color in a cyclic manner
                $colorClass = $colors[$loop->index % count($colors)];
            @endphp
            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body widget-user">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 avatar-lg me-3">
                                @if(asset($user->avatar))
                                    <!-- If user has an image, display it -->
                                    <img src="{{ $user->avatar }}" 
                                        class="img-fluid rounded-circle" 
                                        alt="User Image" 
                                        style="width: 64px; height: 64px; object-fit: cover;">
                                @else
                                    <!-- If no image, show initials -->
                                    @php
                                        // Split the name and take up to two initials
                                        $nameParts = explode(' ', $user->name);
                                        $initials = collect($nameParts)
                                            ->filter(fn($part) => strlen($part) > 0)
                                            ->take(2)
                                            ->map(fn($part) => strtoupper(substr($part, 0, 1)))
                                            ->implode('');
                                    @endphp
                                    <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center"
                                        style="width: 64px; height: 64px; font-weight: 600; font-size: 1rem;">
                                        {{ $initials ?: 'U' }}
                                    </div>
                                @endif
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <h5 class="mt-0 mb-1">{{ $user->name }}</h5>
                                <p class="text-muted mb-2 font-13 text-truncate">{{ $user->email }}</p>
                                <!-- Use dynamic color class for the title -->
                                <small class="d-block text-truncate {{ $colorClass }}" style="max-width: 150px;">
                                    {{ $user->title ?? 'No Title' }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <!-- end row -->

    {{-- rders Count by University chart  --}}
    {{-- <div class="row">
        <div class="col">
                <div class="card shadow-lg border-0 w-100">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center" style="height: 600px;">
                        <h4 class="header-title mt-0">Orders Count by University</h4>
                        <canvas class="mt-2" id="schoolChart"></canvas>
                    </div>
                </div>
        </div><!-- end col -->
    </div> --}}
    <!-- end row -->

   <!-- Load Chart.js and Plugin in the Correct Order -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.2.1/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.1.0"></script>

    {{-- dashboard scripts --}}
    {{-- @include('admin.partials.dashboard_scripts') --}}
@endsection


