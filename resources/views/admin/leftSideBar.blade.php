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
                    {{-- <form action="{{ route('auth.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item notify-item">
                            <i class="fe-log-out me-1"></i>
                            <span>Logout</span>
                        </button>
                    </form> --}}
                </div>
            </div>
        </div>
    
         <!--- Sidemenu -->
         <div id="sidebar-menu">
            <ul id="side-menu">
                <li class="menu-title">Navigation</li>
                <li>
                    <a href="{{ route('admin.dashboard') }}">
                        <i class="mdi mdi-view-dashboard-outline"></i>
                        <span class="badge bg-success rounded-pill float-end">9+</span>
                        <span> Dashboard </span>
                    </a>
                </li>
        
                 <li class="menu-title mt-2">Cities</li>
                <li>
                    <a href="{{ route('cities.index') }}">
                        <i class="mdi mdi-cart-outline"></i>
                        <span> cities </span>
                    </a>
                </li>
                
                {{-- <li>
                    <a href="{{ route('category-images.index') }}">
                        <i class="mdi mdi-cart-outline"></i>
                        <span> categories image</span>
                    </a>
                </li> --}}
        
                {{--<li class="menu-title mt-2">Pages</li>
                <li>
                    <a href="{{ route('book-types.index') }}">
                        <i class="mdi mdi-book-open-page-variant-outline"></i>
                        <span> Book Type </span>
                    </a>
                </li>
        
                <li>
                    <a href="#bookDesign" data-bs-toggle="collapse">
                        <i class="mdi mdi-pencil-box-outline"></i>
                        <span> Book Design </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="bookDesign">
                        <ul class="nav-second-level">
                            <li>
                                <a href="{{ route('book-designs.index') }}">
                                    <i class="mdi mdi-book-edit-outline"></i>
                                    Book Design
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('categories.index') }}">
                                    <i class="mdi mdi-shape-outline"></i>
                                    Categories
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('subcategories.index') }}">
                                    <i class="mdi mdi-shape-plus-outline"></i>
                                    Sub Categories
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
        
                <li>
                    <a href="{{ route('book-decorations.index') }}">
                        <i class="mdi mdi-format-color-fill"></i>
                        <span> Book Decoration </span>
                    </a>
                </li>
        
                <li>
                    <a href="{{ route('governorates.index') }}">
                        <i class="mdi mdi-map-marker-outline"></i>
                        <span> Governorates </span>
                    </a>
                </li>
        
                <li>
                    <a href="{{ route('discount-codes.index') }}">
                        <i class="mdi mdi-tag-outline"></i>
                        <span> Discount Codes </span>
                    </a>
                </li>
        
                <li>
                    <a href="{{ route('svgs.index') }}">
                        <i class="mdi mdi-vector-triangle"></i>
                        <span> SVG's </span>
                    </a>
                </li>
        
                <li>
                    <a href="{{ route('universities.index') }}">
                        <i class="mdi mdi-school-outline"></i>
                        <span> Universities </span>
                    </a>
                </li>
        
                <li>
                    <a href="{{ route('diplomas.index') }}">
                        <i class="mdi mdi-office-building-outline"></i>
                        <span> Colleges </span>
                    </a>
                </li>--}}
        
                {{-- <li class="menu-title mt-2">Manager</li>
                <li>
                    <a href="{{ route('users.index') }}">
                        <i class="mdi mdi-account-group-outline"></i>
                        <span> Users </span>
                    </a>
                </li>  --}}
            </ul>
         </ul>
     </div>
     <!-- End Sidebar -->

     <div class="clearfix"></div>

 </div>
 <!-- Sidebar -left -->

 </div>
