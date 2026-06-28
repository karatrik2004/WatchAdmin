<!-- Sidebar -->
<nav class="pc-sidebar ">
    <div class="navbar-wrapper">
        <div class="m-header">
            <a href="javascript:void(0);" class="b-brand"><span>{{ config('app.name') }}</span></a>
        </div>
        <div class="navbar-content">
            <ul class="pc-navbar">
                <li class="pc-item {{ isActiveUrl(['admin/dashboard'], 'active') }}">
                    <a href="{{route('admin.dashboard')}}" class="pc-link ">
                        <span class="pc-micon"><i class="material-icons-two-tone">home</i></span>
                        <span class="pc-mtext">Dashboard</span>
                    </a>
                </li>

                <!-- Manage Deals -->
                <li class="pc-item pc-hasmenu {{ isActiveUrl(['admin/deals*','admin/account/deals*'], 'active pc-trigger') }}">
                    <a href="javascript:void(0);" class="pc-link">
                        <span class="pc-micon"><i class="material-icons-two-tone">settings</i></span>
                        <span class="pc-mtext">Manage Deals</span>
                        <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                    </a>
                    <ul class="pc-submenu">
                        <li class="pc-item {{ isActiveUrl('admin/deals') }}">
                            <a class="pc-link" href="{{ url('admin/deals') }}">All Deals</a>
                        </li>
                        @if(auth('admin')->user()->hasRole('super admin'))
                            <li class="pc-item {{ isActiveUrl('admin/deals/create') }}">
                                <a class="pc-link" href="{{ url('admin/deals/create') }}">Add Deal</a>
                            </li>
                        @endif
                    </ul>
                </li>
                    <!-- Brands Section -->
                    <li class="pc-item pc-hasmenu {{ isActiveUrl(['admin/brands*'], 'active pc-trigger') }}">
                        <a href="javascript:void(0);" class="pc-link">
                            <span class="pc-micon"><i class="material-icons-two-tone">watch</i></span>
                            <span class="pc-mtext">Brands</span>
                            <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                        </a>
                        <ul class="pc-submenu">
                            <li class="pc-item {{ isActiveUrl('admin/brands') }}">
                                <a class="pc-link" href="{{ url('admin/brands') }}">All Brands</a>
                            </li>
                            <li class="pc-item {{ isActiveUrl('admin/brands/create') }}">
                                <a class="pc-link" href="{{ url('admin/brands/create') }}">Add Brand</a>
                            </li>
                        </ul>
                    </li>
                <!-- Manage Stocks -->
                <li class="pc-item pc-hasmenu {{ isActiveUrl(['admin/stocks*'], 'active pc-trigger') }}">
                    <a href="javascript:void(0);" class="pc-link">
                        <span class="pc-micon"><i class="material-icons-two-tone">settings</i></span>
                        <span class="pc-mtext">Manage Stocks</span>
                        <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                    </a>
                    <ul class="pc-submenu">
                        <li class="pc-item {{ isActiveUrl('admin/stocks') }}">
                            <a class="pc-link" href="{{ url('admin/stocks') }}">All Stocks</a>
                        </li>
                    </ul>
                </li>
                <!-- Shipping: deals + invoices + create -->
                <li class="pc-item pc-hasmenu {{ isActiveUrl(['admin/shipping-deals','admin/shipping-invoices*'], 'active pc-trigger') }}">
                    <a href="javascript:void(0);" class="pc-link">
                        <span class="pc-micon"><i class="material-icons-two-tone">local_shipping</i></span>
                        <span class="pc-mtext">Shipping</span>
                        <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                    </a>
                    <ul class="pc-submenu">
                        <li class="pc-item {{ isActiveUrl('admin/shipping-deals', 'active') }}">
                            <a class="pc-link" href="{{ url('admin/shipping-deals') }}">Shipping Deals</a>
                        </li>
                        <li class="pc-item {{ isActiveUrl('admin/shipping-invoices', 'active') }}">
                            <a class="pc-link" href="{{ url('admin/shipping-invoices') }}">Invoices</a>
                        </li>
                        <li class="pc-item {{ isActiveUrl('admin/shipping-invoices/create', 'active') }}">
                            <a class="pc-link" href="{{ route('admin.shipping-invoices.create') }}">Create</a>
                        </li>
                    </ul>
                </li>
                <!-- Company Settings (separate items) -->
                <li class="pc-item {{ isActiveUrl('admin/company-settings', 'active') }}">
                    <a href="{{ url('admin/company-settings') }}" class="pc-link">
                        <span class="pc-micon"><i class="material-icons-two-tone">settings</i></span>
                        <span class="pc-mtext">Company Settings</span>
                    </a>
                </li>
                <li class="pc-item {{ isActiveUrl('admin/notification-emails', 'active') }}">
                    <a href="{{ url('admin/notification-emails') }}" class="pc-link">
                        <span class="pc-micon"><i class="material-icons-two-tone">email</i></span>
                        <span class="pc-mtext">Notification Emails</span>
                    </a>
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
