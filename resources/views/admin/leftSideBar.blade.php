<div class="left-side-menu">

    <div class="h-100" data-simplebar>

      <!-- User box -->
       <div class="user-box text-center">
           {{-- @if(Auth::user()->image)
               <img src="{{ asset('storage/' . Auth::user()->image) }}" alt="user-img" title="{{ Auth::user()->name }}"
                   class="rounded-circle img-thumbnail avatar-md">
           @else
               @php
                   // Split the name into parts and extract up to two initials
                   $nameParts = explode(' ', Auth::user()->name);
                   $initials = collect($nameParts)
                       ->filter(fn($part) => strlen($part) > 0)
                       ->take(2)
                       ->map(fn($part) => strtoupper(substr($part, 0, 1)))
                       ->implode('');
               @endphp
               <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-secondary text-white avatar-md" 
                   style="width: 50px; height: 50px; font-weight: 400; font-size: 1rem;">
                   {{ $initials ?: 'U' }}
               </div>
           @endif --}}
       
           <!-- Display user name as plain text -->
           <h5 class="mt-2 mb-1 d-block">Admin</h5>
           <p class="text-muted left-user-info">BackEnd</p>
       
           <!-- Dropdown triggered by settings icon -->
           <div class="dropdown">
               <a href="#" class="text-muted left-user-info" data-bs-toggle="dropdown" aria-expanded="false">
                   <i class="mdi mdi-cog"></i>
               </a>
               <div class="dropdown-menu user-pro-dropdown">
                   <!-- item-->
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
                       <span> Dashboard </span>
                   </a>
               </li>

               <!-- ═══════════════════════════════════════════════════════════════ -->
               <!--                          WALLET MANAGEMENT                       -->
               <!-- ═══════════════════════════════════════════════════════════════ -->
               <li class="menu-title mt-2">Financial Management</li>
               <li>
                   <a href="#walletMenu" data-bs-toggle="collapse" aria-expanded="false" aria-controls="walletMenu">
                       <i class="mdi mdi-wallet"></i>
                       <span> Wallet Management </span>
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
                                   <span>All Transactions</span>
                               </a>
                           </li>
                       </ul>
                   </div>
               </li>
       
               <!-- ═══════════════════════════════════════════════════════════════ -->
               <!--                        EXISTING SECTIONS                         -->
               <!-- ═══════════════════════════════════════════════════════════════ -->
               <li class="menu-title mt-2">Platform Management</li>
               <li>
                   <a href="{{ route('dashboard.cities.index') }}">
                       <i class="mdi mdi-city"></i>
                       <span> Cities </span>
                   </a>
               </li>

               <li>
                   <a href="{{ route('dashboard.areas.index') }}">
                       <i class="mdi mdi-map-marker-radius"></i>
                       <span> Areas </span>
                   </a>
               </li>

               <li>
                   <a href="{{ route('dashboard.features.index') }}">
                       <i class="mdi mdi-star-circle"></i>
                       <span> Features </span>
                   </a>
               </li>

               <li>
                   <a href="{{ route('dashboard.coupons.index') }}">
                       <i class="mdi mdi-ticket-percent"></i>
                       <span> Coupons </span>
                   </a>
               </li>

               <!-- ═══════════════════════════════════════════════════════════════ -->
               <!--                       FUTURE SECTIONS                            -->
               <!-- ═══════════════════════════════════════════════════════════════ -->
               <li class="menu-title mt-2">Farm Management</li>
               <li>
                   <a href="#farmMenu" data-bs-toggle="collapse" aria-expanded="false" aria-controls="farmMenu">
                       <i class="mdi mdi-home-variant"></i>
                       <span> Farms </span>
                       <span class="menu-arrow"></span>
                   </a>
                   <div class="collapse" id="farmMenu">
                       <ul class="nav-second-level">
                           <li>
                               <a href="#" style="opacity: 0.6; pointer-events: none;">
                                   <i class="mdi mdi-format-list-bulleted"></i>
                                   <span>All Farms</span>
                                   <small class="text-muted">(Coming Soon)</small>
                               </a>
                           </li>
                           <li>
                               <a href="#" style="opacity: 0.6; pointer-events: none;">
                                   <i class="mdi mdi-clock-outline"></i>
                                   <span>Pending Approval</span>
                                   <small class="text-muted">(Coming Soon)</small>
                               </a>
                           </li>
                       </ul>
                   </div>
               </li>

               <li class="menu-title mt-2">Booking Management</li>
               <li>
                   <a href="#bookingMenu" data-bs-toggle="collapse" aria-expanded="false" aria-controls="bookingMenu">
                       <i class="mdi mdi-calendar-check"></i>
                       <span> Bookings </span>
                       <span class="menu-arrow"></span>
                   </a>
                   <div class="collapse" id="bookingMenu">
                       <ul class="nav-second-level">
                           <li>
                               <a href="#" style="opacity: 0.6; pointer-events: none;">
                                   <i class="mdi mdi-format-list-bulleted"></i>
                                   <span>All Bookings</span>
                                   <small class="text-muted">(Coming Soon)</small>
                               </a>
                           </li>
                           <li>
                               <a href="#" style="opacity: 0.6; pointer-events: none;">
                                   <i class="mdi mdi-chart-bar"></i>
                                   <span>Booking Reports</span>
                                   <small class="text-muted">(Coming Soon)</small>
                               </a>
                           </li>
                       </ul>
                   </div>
               </li>
           </ul>
        </div>
        <!-- End Sidebar -->

        <div class="clearfix"></div>

    </div>
    <!-- Sidebar -left -->

</div>