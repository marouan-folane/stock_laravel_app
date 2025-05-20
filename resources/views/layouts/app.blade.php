<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config('app.name', 'Stock Management System') }} - @yield('title')</title>
    
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
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
            background: linear-gradient(to right, #3182ce, #2c5282) !important;
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
        @include('layouts.partials.topbar')
        
        <div class="container-fluid">
            <div class="row">
                @include('layouts.partials.sidebar')
                
                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                    @include('layouts.partials.alerts')
                    
                    @yield('content')
                </main>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    
    @stack('scripts')
</body>
</html> 