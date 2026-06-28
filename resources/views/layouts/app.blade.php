<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Mobile Shop ERP') - ERP Portal</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- jQuery (Loaded early in head to prevent race conditions with type="module" deferred scripts) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Styles -->
    @vite(['resources/css/app.css'])
    @stack('styles')
</head>
<body>

    <!-- Loading Overlay -->
    <div id="loading-overlay">
        <div class="spinner-border spinner-custom" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- Page Wrapper -->
    <div id="wrapper" class="toggled">
        
        <!-- Sidebar -->
        <div id="sidebar-wrapper">
            <div class="sidebar-brand d-flex justify-content-between align-items-center">
                <a href="{{ route('dashboard') }}" class="d-flex align-items-center gap-2 text-decoration-none">
                    <i class="bi bi-phone-vibrate text-primary"></i>
                    <span>MobileERP</span>
                </a>
                <button class="btn btn-link text-white p-0 d-lg-none" id="sidebar-close-btn" style="font-size: 1.25rem;" title="Close Menu">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            
            <ul class="sidebar-nav">
                <!-- Dashboard -->
                <li class="{{ Request::routeIs('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <!-- Master Modules -->
                <li class="nav-heading"><span>Catalog & Business</span></li>
                
                @can('view-products')
                <li class="{{ Request::is('catalog*') ? 'active' : '' }}">
                    <a href="#catalogMenu" data-bs-toggle="collapse" class="submenu-toggle">
                        <i class="bi bi-box-seam"></i>
                        <span>Catalog</span>
                    </a>
                    <ul class="collapse list-unstyled ps-3 show" id="catalogMenu">
                        <li class="{{ Request::routeIs('products.*') ? 'active-submenu' : '' }}"><a href="{{ route('products.index') }}"><i class="bi bi-tablet"></i> Products</a></li>
                        <li class="{{ Request::routeIs('brands.*') ? 'active-submenu' : '' }}"><a href="{{ route('brands.index') }}"><i class="bi bi-award"></i> Brands</a></li>
                        <li class="{{ Request::routeIs('categories.*') ? 'active-submenu' : '' }}"><a href="{{ route('categories.index') }}"><i class="bi bi-tags"></i> Categories</a></li>
                        <li class="{{ Request::routeIs('variants.*') ? 'active-submenu' : '' }}"><a href="{{ route('variants.index') }}"><i class="bi bi-sliders"></i> Variants</a></li>
                    </ul>
                </li>
                @endcan
 
                @can('view-contacts')
                <li class="{{ Request::is('contacts*') ? 'active' : '' }}">
                    <a href="#contactsMenu" data-bs-toggle="collapse" class="submenu-toggle">
                        <i class="bi bi-people"></i>
                        <span>Contacts</span>
                    </a>
                    <ul class="collapse list-unstyled ps-3" id="contactsMenu">
                        <li class="{{ Request::routeIs('customers.*') ? 'active-submenu' : '' }}"><a href="{{ route('customers.index') }}"><i class="bi bi-person-heart"></i> Customers</a></li>
                        <li class="{{ Request::routeIs('suppliers.*') ? 'active-submenu' : '' }}"><a href="{{ route('suppliers.index') }}"><i class="bi bi-truck"></i> Suppliers</a></li>
                    </ul>
                </li>
                @endcan

                <!-- Inventory Module -->
                <li class="nav-heading"><span>Operations</span></li>
                <li class="{{ Request::is('inventory*') ? 'active' : '' }}">
                    <a href="{{ route('inventory.index') }}">
                        <i class="bi bi-database-check"></i>
                        <span>Inventory</span>
                    </a>
                </li>
                
                <li class="{{ Request::is('warehouses*') ? 'active' : '' }}">
                    <a href="{{ route('warehouses.index') }}">
                        <i class="bi bi-building-gear"></i>
                        <span>Warehouses</span>
                    </a>
                </li>

                <!-- Purchase Module -->
                <li class="{{ Request::is('purchases*') ? 'active' : '' }}">
                    <a href="{{ route('purchases.index') }}">
                        <i class="bi bi-cart-check"></i>
                        <span>Purchases</span>
                    </a>
                </li>

                <!-- Sales Module -->
                <li class="{{ Request::is('sales*') ? 'active' : '' }}">
                    <a href="{{ route('sales.pos') }}">
                        <i class="bi bi-printer"></i>
                        <span>POS Billing</span>
                    </a>
                </li>

                <!-- Accounts Module -->
                <li class="nav-heading"><span>Finance & Admin</span></li>
                <li class="{{ Request::is('accounts*') ? 'active' : '' }}">
                    <a href="{{ route('accounts.index') }}">
                        <i class="bi bi-cash-coin"></i>
                        <span>Accounts</span>
                    </a>
                </li>

                @can('view-users')
                <li class="{{ Request::routeIs('users.*') ? 'active' : '' }}">
                    <a href="{{ route('users.index') }}">
                        <i class="bi bi-people"></i>
                        <span>Users</span>
                    </a>
                </li>
                @endcan

                @can('view-roles')
                <li class="{{ Request::routeIs('roles.*') ? 'active' : '' }}">
                    <a href="{{ route('roles.index') }}">
                        <i class="bi bi-shield-lock"></i>
                        <span>Roles & Perms</span>
                    </a>
                </li>
                @endcan

                <!-- Reports -->
                <li class="{{ Request::is('reports*') ? 'active' : '' }}">
                    <a href="{{ route('reports.index') }}">
                        <i class="bi bi-graph-up-arrow"></i>
                        <span>Reports</span>
                    </a>
                </li>

                <!-- Settings -->
                @can('view-settings')
                <li class="{{ Request::is('settings*') ? 'active' : '' }}">
                    <a href="{{ route('settings.index') }}">
                        <i class="bi bi-gear"></i>
                        <span>Settings</span>
                    </a>
                </li>
                @endcan
            </ul>
        </div>
        <!-- /#sidebar-wrapper -->

        <!-- Page Content -->
        <div id="page-content-wrapper">
            
            <!-- Top Navbar -->
            <nav class="navbar-custom">
                <div class="d-flex align-items-center">
                    <button class="btn btn-outline-secondary btn-sm me-3" id="menu-toggle">
                        <i class="bi bi-justify"></i>
                    </button>
                    <span class="fw-bold d-none d-md-inline text-muted">@yield('module-title', 'Management Portal')</span>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <!-- Font Size Selector Button Group -->
                    <div class="btn-group btn-group-sm" role="group" aria-label="Font Size Changer">
                        <button type="button" class="btn btn-outline-secondary font-size-btn" data-size="small" title="Decrease Font Size">A-</button>
                        <button type="button" class="btn btn-outline-secondary font-size-btn active" data-size="normal" title="Normal Font Size">A</button>
                        <button type="button" class="btn btn-outline-secondary font-size-btn" data-size="large" title="Increase Font Size">A+</button>
                    </div>

                    <!-- Light/Dark Mode Switcher -->
                    <button class="btn btn-link text-muted p-0" id="theme-toggle" title="Toggle Theme">
                        <i class="bi bi-moon-fill fs-5"></i>
                    </button>

                    <!-- Branch Indicator -->
                    <span class="badge bg-primary px-2 py-2" title="{{ auth()->user()->branch->name ?? 'Main Branch' }}">
                        <i class="bi bi-building"></i>
                        <span class="d-none d-sm-inline ms-1">{{ auth()->user()->branch->name ?? 'Main Branch' }}</span>
                    </span>

                    <!-- Profile Dropdown -->
                    <div class="dropdown">
                        <a class="d-flex align-items-center text-decoration-none dropdown-toggle text-primary" href="#" role="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            @if(auth()->user()->profile_image)
                                <img src="{{ asset('storage/' . auth()->user()->profile_image) }}" alt="Avatar" class="rounded-circle me-2" width="36" height="36" style="object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-2" style="width: 36px; height: 36px; font-weight: 600;">
                                    {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                                </div>
                            @endif
                            <span class="d-none d-md-inline">{{ auth()->user()->name ?? 'User' }}</span>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2" aria-labelledby="profileDropdown">
                            <li><a class="dropdown-item py-2" href="{{ route('profile.index') }}"><i class="bi bi-person me-2"></i> Profile</a></li>
                            <li><a class="dropdown-item py-2" href="{{ route('profile.password') }}"><i class="bi bi-key me-2"></i> Change Password</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item py-2 text-danger"><i class="bi bi-box-arrow-right me-2"></i> Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Main Scrollable Content -->
            <div class="main-content">
                <!-- Breadcrumbs -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Home</a></li>
                        @yield('breadcrumbs')
                    </ol>
                </nav>

                <!-- Page Body -->
                @yield('content')
            </div>
        </div>
        <!-- /#page-content-wrapper -->

    </div>
    <!-- /#wrapper -->

    <!-- Scripts -->
    @vite(['resources/js/app.js'])
    @stack('scripts')
</body>
</html>
