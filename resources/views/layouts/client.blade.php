<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config('app.name', 'Stock Management System') }} - Client - @yield('title')</title>
    
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #3182ce;
            --secondary-color: #2c5282;
            --success-color: #38a169;
            --danger-color: #e53e3e;
            --dark-color: #2d3748;
            --light-color: #f7fafc;
            --gray-color: #718096;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --transition: all 0.3s ease;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            color: #2d3748;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        #app {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        /* Navbar styling */
        .navbar {
            padding: 0.75rem 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            background: linear-gradient(to right, var(--success-color), #2f855a) !important;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.25rem;
            letter-spacing: -0.5px;
        }
        
        .navbar .nav-link {
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: var(--transition);
            border-radius: var(--border-radius);
            margin: 0 0.25rem;
        }
        
        .navbar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .navbar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2);
            font-weight: 600;
        }
        
        .navbar .dropdown-menu {
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            border: none;
            padding: 0.5rem 0;
            margin-top: 0.5rem;
        }
        
        .navbar .dropdown-item {
            padding: 0.5rem 1.5rem;
            transition: var(--transition);
        }
        
        .navbar .dropdown-item:hover {
            background-color: #edf2f7;
        }
        
        /* Main content container */
        .container {
            max-width: 1200px;
            padding: 0 1.5rem;
        }
        
        main {
            padding: 1.5rem 0;
        }
        
        /* Card styling */
        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            overflow: hidden;
        }
        
        .card:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            font-weight: 600;
            padding: 1rem 1.25rem;
        }
        
        /* Button styling */
        .btn {
            padding: 0.5rem 1rem;
            font-weight: 500;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }
        
        .btn-success:hover {
            background-color: #2f855a;
            border-color: #2f855a;
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }
        
        .btn-danger:hover {
            background-color: #c53030;
            border-color: #c53030;
        }
        
        /* Form styling */
        .form-control {
            border-radius: var(--border-radius);
            padding: 0.5rem 0.75rem;
            border: 1px solid #e2e8f0;
            transition: var(--transition);
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.3);
        }
        
        /* Alert styling */
        .alert {
            border-radius: var(--border-radius);
            border: none;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background-color: #c6f6d5;
            color: #276749;
        }
        
        .alert-danger {
            background-color: #fed7d7;
            color: #c53030;
        }
        
        /* Badge styling */
        .badge {
            padding: 0.25rem 0.5rem;
            font-weight: 600;
        }
        
        /* Table styling */
        .table {
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
            background-color: #fff;
        }
        
        .table thead th {
            background-color: #f9fafb;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding: 0.75rem 1rem;
        }
        
        .table tbody td {
            padding: 1rem;
            border-bottom: 1px solid #f1f1f1;
            vertical-align: middle;
        }
        
        .table tbody tr:last-child td {
            border-bottom: none;
        }
        
        /* Cart badge */
        .cart-badge {
            position: relative;
            top: -8px;
            right: 2px;
            font-size: 0.7rem;
        }
        
        /* Footer styling */
        footer {
            background-color: var(--dark-color);
            color: var(--light-color);
            padding: 2rem 0;
            margin-top: auto;
        }
        
        /* Pagination */
        .pagination {
            margin-top: 1.5rem;
        }
        
        .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .page-link {
            color: var(--primary-color);
            border-radius: 4px;
            margin: 0 2px;
        }
        
        /* Product card styling */
        .product-card {
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .product-card .card-img-top {
            height: 200px;
            object-fit: cover;
        }
        
        .product-card .card-body {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .product-card .card-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .product-card .card-text {
            margin-bottom: 1rem;
            flex: 1;
        }
        
        .product-card .btn {
            align-self: flex-start;
        }
        
        /* Order history styling */
        .order-item {
            border-left: 4px solid var(--primary-color);
            padding-left: 1rem;
            margin-bottom: 1rem;
        }
        
        /* Animation keyframes */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <div id="app">
        <!-- Top Navigation -->
        <nav class="navbar navbar-expand-md navbar-dark">
            <div class="container">
                <a class="navbar-brand" href="{{ route('client.dashboard') }}">
                    <i class="bi bi-box-seam me-2"></i>{{ config('app.name', 'StockSystem') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('client.dashboard') ? 'active' : '' }}" href="{{ route('client.dashboard') }}">
                                <i class="bi bi-house-door me-1"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('client.products') ? 'active' : '' }}" href="{{ route('client.products') }}">
                                <i class="bi bi-grid me-1"></i> Products
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('client.orders') ? 'active' : '' }}" href="{{ route('client.orders') }}">
                                <i class="bi bi-receipt me-1"></i> My Orders
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/" target="_blank">
                                <i class="bi bi-globe me-1"></i> Home Page
                            </a>
                        </li>
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('client.cart') ? 'active' : '' }}" href="{{ route('client.cart') }}">
                                <i class="bi bi-cart3"></i>
                                @if(session()->has('cart') && count(session()->get('cart')) > 0)
                                    <span class="badge bg-danger rounded-pill cart-badge">{{ count(session()->get('cart')) }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                <i class="bi bi-person-circle me-1"></i> {{ Auth::user()->name }}
                            </a>

                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('client.profile.edit') }}">
                                    <i class="bi bi-person me-2"></i> Profile
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="bi bi-box-arrow-right me-2"></i> {{ __('Logout') }}
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        
        <div class="container mt-4">
            <div class="row">
                <main class="col-md-12">
                    <!-- Alerts -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show fade-in" role="alert">
                            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show fade-in" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    
                    @yield('content')
                </main>
            </div>
        </div>
        
        <!-- Footer -->
        <footer class="text-center py-4 mt-5">
            <div class="container">
                <div class="row">
                    <div class="col-md-4 mb-4 mb-md-0">
                        <h5 class="mb-3">{{ config('app.name', 'Stock System') }}</h5>
                        <p class="mb-0">Your reliable partner for inventory management</p>
                    </div>
                    <div class="col-md-4 mb-4 mb-md-0">
                        <h5 class="mb-3">Quick Links</h5>
                        <ul class="list-unstyled">
                            <li><a href="{{ route('client.dashboard') }}" class="text-light">Dashboard</a></li>
                            <li><a href="{{ route('client.products') }}" class="text-light">Products</a></li>
                            <li><a href="{{ route('client.orders') }}" class="text-light">Orders</a></li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h5 class="mb-3">Contact</h5>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-envelope me-2"></i> support@stocksystem.com</li>
                            <li><i class="bi bi-telephone me-2"></i> +1 (555) 123-4567</li>
                        </ul>
                    </div>
                </div>
                <hr class="my-4" style="background-color: rgba(255,255,255,0.2);">
                <p class="mb-0">© {{ date('Y') }} {{ config('app.name', 'Stock System') }}. All rights reserved.</p>
            </div>
        </footer>
    </div>
    
    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    
    @stack('scripts')
</body>
</html>