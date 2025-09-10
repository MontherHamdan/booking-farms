<div class="left-side-menu">
    <div class="h-100" data-simplebar>
        <!-- User box -->
        <div class="user-box text-center">
            <h5 class="mt-2 mb-1 d-block">Admin</h5>
            <p class="text-muted left-user-info">Backend</p>
            
            <div class="dropdown">
                <a href="#" class="text-muted left-user-info" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="mdi mdi-cog"></i>
                </a>
                <div class="dropdown-menu user-pro-dropdown">
                    <form action="{{ route('dashboard.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item notify-item">
                            <i class="fe-log-out me-1"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!--- Sidemenu -->
        <div id="sidebar-menu">
            <ul id="side-menu">
                <li class="menu-title">Navigation</li>
                <li>
                    <a href="{{ route('dashboard.home') }}">
                        <i class="mdi mdi-view-dashboard-outline"></i>
                        <span class="badge bg-success rounded-pill float-end">9+</span>
                        <span>Dashboard</span>
                    </a>
                </li>

                <!-- FARM MANAGEMENT -->
                <li class="menu-title mt-2">Farm Management</li>
                <li>
                    <a href="#farmMenu" data-bs-toggle="collapse" aria-expanded="false" aria-controls="farmMenu">
                        <i class="mdi mdi-home-variant"></i>
                        <span>Farms</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="farmMenu">
                        <ul class="nav-second-level">
                            <li>
                                <a href="{{ route('dashboard.farms.index') }}">
                                    <i class="mdi mdi-format-list-bulleted"></i>
                                    <span>All Farms</span>
                                    @php
                                        $totalFarms = \App\Models\Farm::count();
                                    @endphp
                                    @if($totalFarms > 0)
                                        <span class="badge bg-primary rounded-pill float-end">{{ $totalFarms }}</span>
                                    @endif
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('dashboard.farms.index', ['status' => 'pending']) }}">
                                    <i class="mdi mdi-clock-outline"></i>
                                    <span>Pending Approval</span>
                                    @php
                                        $pendingFarms = \App\Models\Farm::where('status', 'pending')->count();
                                    @endphp
                                    @if($pendingFarms > 0)
                                        <span class="badge bg-warning rounded-pill float-end">{{ $pendingFarms }}</span>
                                    @endif
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('dashboard.farms.index', ['status' => 'active']) }}">
                                    <i class="mdi mdi-check-circle"></i>
                                    <span>Active Farms</span>
                                    @php
                                        $activeFarms = \App\Models\Farm::where('status', 'active')->count();
                                    @endphp
                                    @if($activeFarms > 0)
                                        <span class="badge bg-success rounded-pill float-end">{{ $activeFarms }}</span>
                                    @endif
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('dashboard.farms.index', ['status' => 'rejected']) }}">
                                    <i class="mdi mdi-close-circle"></i>
                                    <span>Rejected Farms</span>
                                    @php
                                        $rejectedFarms = \App\Models\Farm::where('status', 'rejected')->count();
                                    @endphp
                                    @if($rejectedFarms > 0)
                                        <span class="badge bg-danger rounded-pill float-end">{{ $rejectedFarms }}</span>
                                    @endif
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- BOOKING MANAGEMENT -->
                <li class="menu-title mt-2">Booking Management</li>
                <li>
                    <a href="#bookingMenu" data-bs-toggle="collapse" aria-expanded="false" aria-controls="bookingMenu">
                        <i class="mdi mdi-calendar-check"></i>
                        <span>Bookings</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="bookingMenu">
                        <ul class="nav-second-level">
                            <li>
                                <a href="{{ route('dashboard.bookings.index') }}">
                                    <i class="mdi mdi-format-list-bulleted"></i>
                                    <span>All Bookings</span>
                                    @php
                                        $totalBookings = \App\Models\FarmBooking::count();
                                    @endphp
                                    @if($totalBookings > 0)
                                        <span class="badge bg-primary rounded-pill float-end">{{ $totalBookings }}</span>
                                    @endif
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('dashboard.bookings.index', ['booking_status' => 'pending']) }}">
                                    <i class="mdi mdi-clock-outline"></i>
                                    <span>Pending Bookings</span>
                                    @php
                                        $pendingBookings = \App\Models\FarmBooking::where('booking_status', 'pending')->count();
                                    @endphp
                                    @if($pendingBookings > 0)
                                        <span class="badge bg-warning rounded-pill float-end">{{ $pendingBookings }}</span>
                                    @endif
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('dashboard.bookings.index', ['booking_status' => 'confirmed']) }}">
                                    <i class="mdi mdi-check-circle"></i>
                                    <span>Confirmed Bookings</span>
                                    @php
                                        $confirmedBookings = \App\Models\FarmBooking::where('booking_status', 'confirmed')->count();
                                    @endphp
                                    @if($confirmedBookings > 0)
                                        <span class="badge bg-success rounded-pill float-end">{{ $confirmedBookings }}</span>
                                    @endif
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('dashboard.bookings.reports') }}">
                                    <i class="mdi mdi-chart-bar"></i>
                                    <span>Booking Reports</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- WALLET MANAGEMENT -->
                <li class="menu-title mt-2">Wallet Management</li>
                <li>
                    <a href="#walletMenu" data-bs-toggle="collapse" aria-expanded="false" aria-controls="walletMenu">
                        <i class="mdi mdi-wallet"></i>
                        <span>Wallets</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="walletMenu">
                        <ul class="nav-second-level">
                            <li>
                                <a href="{{ route('dashboard.wallet.index') }}">
                                    <i class="mdi mdi-chart-line"></i>
                                    <span>Overview</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('dashboard.wallet.wallets') }}">
                                    <i class="mdi mdi-account-cash"></i>
                                    <span>Farm Owner Wallets</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('dashboard.wallet.pending-payments') }}">
                                    <i class="mdi mdi-bank-transfer-out"></i>
                                    <span>Pending Payments</span>
                                    @php
                                        $pendingPayments = \App\Models\FarmOwnerWallet::where('balance', '>=', \App\Models\PlatformSetting::getMinimumTransferAmount())->count();
                                    @endphp
                                    @if($pendingPayments > 0)
                                        <span class="badge bg-warning rounded-pill float-end">{{ $pendingPayments }}</span>
                                    @endif
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('dashboard.wallet.transactions') }}">
                                    <i class="mdi mdi-format-list-bulleted"></i>
                                    <span>Transactions</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- PLATFORM MANAGEMENT -->
                <li class="menu-title mt-2">Platform Management</li>
                <li>
                    <a href="{{ route('dashboard.cities.index') }}">
                        <i class="mdi mdi-city"></i>
                        <span>Cities</span>
                        @php
                            $totalCities = \App\Models\City::count();
                        @endphp
                        @if($totalCities > 0)
                            <span class="badge bg-info rounded-pill float-end">{{ $totalCities }}</span>
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('dashboard.areas.index') }}">
                        <i class="mdi mdi-map-marker-radius"></i>
                        <span>Areas</span>
                        @php
                            $totalAreas = \App\Models\Area::count();
                        @endphp
                        @if($totalAreas > 0)
                            <span class="badge bg-info rounded-pill float-end">{{ $totalAreas }}</span>
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('dashboard.features.index') }}">
                        <i class="mdi mdi-star-circle"></i>
                        <span>Features</span>
                        @php
                            $totalFeatures = \App\Models\Feature::count();
                        @endphp
                        @if($totalFeatures > 0)
                            <span class="badge bg-info rounded-pill float-end">{{ $totalFeatures }}</span>
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('dashboard.coupons.index') }}">
                        <i class="mdi mdi-ticket-percent"></i>
                        <span>Coupons</span>
                        @php
                            $activeCoupons = \App\Models\Coupon::where('is_active', true)->count();
                        @endphp
                        @if($activeCoupons > 0)
                            <span class="badge bg-success rounded-pill float-end">{{ $activeCoupons }}</span>
                        @endif
                    </a>
                </li>

                <!-- PLATFORM SETTINGS -->
                <li class="menu-title mt-2">Settings</li>
                <li>
                    <a href="{{ route('dashboard.settings.index') }}">
                        <i class="mdi mdi-cog-outline"></i>
                        <span>Platform Settings</span>
                    </a>
                </li>
            </ul>
        </div>
        <!-- End Sidebar -->

        <div class="clearfix"></div>
    </div>
    <!-- Sidebar -left -->
</div>