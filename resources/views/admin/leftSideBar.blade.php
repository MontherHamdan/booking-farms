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
       
               <li class="menu-title mt-2">Cities</li>
               <li>
                   <a href="{{ route('dashboard.cities.index') }}">
                       <i class="mdi mdi-cart-outline"></i>
                       <span> Cities </span>
                   </a>
               </li>

               <li class="menu-title mt-2">areas</li>
               <li>
                   <a href="{{ route('dashboard.areas.index') }}">
                       <i class="mdi mdi-cart-outline"></i>
                       <span> Areas </span>
                   </a>
               </li>

               <li class="menu-title mt-2">Features</li>
               <li>
                   <a href="{{ route('dashboard.features.index') }}">
                       <i class="mdi mdi-cart-outline"></i>
                       <span> Features </span>
                   </a>
               </li>

               <li class="menu-title mt-2">Coupons</li>
               <li>
                   <a href="{{ route('dashboard.coupons.index') }}">
                       <i class="mdi mdi-ticket-percent"></i>
                       <span> Coupons </span>
                   </a>
               </li>
           </ul>
        </ul>
    </div>
    <!-- End Sidebar -->

    <div class="clearfix"></div>

</div>
<!-- Sidebar -left -->

</div>