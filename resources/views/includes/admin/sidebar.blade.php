<!-- Sidebar -->
@php $currentRoute = Route::currentRouteName(); @endphp
<nav class="pc-sidebar ">
    <div class="navbar-wrapper">
        <div class="m-header">
            <a href="javascript:void(0);" class="b-brand"><span>{{ config('app.name') }}</span></a>
        </div>
        <div class="navbar-content">
            <ul class="pc-navbar">               
                <li class="pc-item {{ ($currentRoute === 'admin.dashboard') ? 'active' : '' }} ">
                    <a href="{{route('admin.dashboard')}}" class="pc-link ">
                        <span class="pc-micon"><i class="material-icons-two-tone">home</i></span>
                        <span class="pc-mtext">Dashboard</span>
                    </a>
                </li>

               
                
                @php 
                $dealRoutes = [                
                'admin.deals.index','admin.deals.create','admin.deals.edit'
                ]; 
                @endphp 
                <li class="pc-item pc-hasmenu {{ in_array($currentRoute,$dealRoutes) ? 'active pc-trigger' : '' }}">
                    <a href="javascript:void(0);" class="pc-link ">
                        <span class="pc-micon"><i class="material-icons-two-tone">settings</i></span>
                        <span class="pc-mtext">Manage Deals</span>
                        <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                    </a>
                    <ul class="pc-submenu">
                        <li class="pc-item {{ $currentRoute === 'admin.deals.index' ? 'active' : '' }}">
                            <a class="pc-link " href="{{route('admin.deals.index')}}">All Deal</a>
                        </li>                       
                        <li class="pc-item {{ $currentRoute === 'admin.deals.create' ? 'active' : '' }}">
                            <a class="pc-link " href="{{route('admin.deals.create')}}">Add Deal</a>
                        </li>                       
                    </ul>
                </li>

                @php 
                $productRoutes = [                
                'admin.products.index','admin.products.create','admin.products.edit'
                ]; 
                @endphp 
                <li class="pc-item pc-hasmenu {{ in_array($currentRoute,$productRoutes) ? 'active pc-trigger' : '' }}">
                    <a href="javascript:void(0);" class="pc-link ">
                        <span class="pc-micon"><i class="material-icons-two-tone">watch</i></span>
                        <span class="pc-mtext">Manage Products</span>
                        <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                    </a>
                    <ul class="pc-submenu">
                        <li class="pc-item {{ $currentRoute === 'admin.products.index' ? 'active' : '' }}">
                            <a class="pc-link " href="{{route('admin.products.index')}}">All Products</a>
                        </li>                       
                        <li class="pc-item {{ $currentRoute === 'admin.products.create' ? 'active' : '' }}">
                            <a class="pc-link " href="{{route('admin.products.create')}}">Add Product</a>
                        </li>                       
                    </ul>
                </li>

            </ul>
        </div>
    </div>
</nav>
<header class="pc-header ">
    <div class="header-wrapper">
        <div class="mr-auto pc-mob-drp">           
        </div>
        <div class="ml-auto">
            <ul class="list-unstyled">                
                <li class="dropdown pc-h-item">
                    <a class="pc-head-link dropdown-toggle arrow-none mr-0" data-toggle="dropdown" href="#"
                        role="button" aria-haspopup="false" aria-expanded="false">
                        <img src="{{url('/backend/images/profile_icon.png')}}" alt="user-image" class="user-avtar">
                        <span>
                            <span class="user-name">
                                {{auth('admin')->user()->firstname}} {{auth('admin')->user()->lastname}}
                            </span>
                            <span class="user-desc">Administrator</span>
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right pc-h-dropdown">                       
                        <a href="{{route('admin.profile')}}" class="dropdown-item">
                            <i class="material-icons-two-tone">account_circle</i>
                            <span>My Profile</span>
                        </a>
                        <a href="{{route('admin.changepassword')}}" class="dropdown-item">
                            <i class="material-icons-two-tone">lock_open</i>
                            <span>Change Password</span>
                        </a>
                        <a href="{{route('admin.logout')}}" class="dropdown-item">
                            <i class="material-icons-two-tone">chrome_reader_mode</i>
                            <span>Logout</span>
                        </a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</header>