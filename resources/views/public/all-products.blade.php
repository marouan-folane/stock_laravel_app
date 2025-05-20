<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>All Products - {{ config('app.name', 'Stock Management System') }}</title>
    
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .navbar-top {
            background-color: white;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        .product-card {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 10px;
            overflow: hidden;
        }
        .product-card:hover {
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            transform: translateY(-5px);
        }
        .stock-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 10;
        }
        .category-filter {
            border-radius: 30px;
            padding: 0.25rem 0.75rem;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
            cursor: pointer;
        }
        .category-filter.active {
            background-color: #3182ce;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light navbar-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/">
                <i class="bi bi-box-seam text-primary me-2"></i>{{ config('app.name', 'Stock Management System') }}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="/products">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/#categories">Categories</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/#about">About</a>
                    </li>
                    <li class="nav-item">
                        @auth
                            <div class="d-flex">
                                <a class="nav-link btn btn-outline-primary me-2" href="{{ route('home') }}">Dashboard</a>
                                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="nav-link btn btn-outline-danger">Logout</button>
                                </form>
                            </div>
                        @else
                            <div class="d-flex">
                                <a class="nav-link btn btn-outline-primary me-2" href="{{ route('login', ['redirect' => url()->current()]) }}">Login</a>
                                <a class="nav-link btn btn-primary text-white" href="{{ route('public.signup', ['redirect' => url()->current()]) }}">Sign Up</a>
                            </div>
                        @endauth
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Breadcrumb -->
    <div class="bg-light py-3">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">All Products</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Products Section -->
    <section class="py-5">
        <div class="container">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h1 class="fw-bold mb-0">All Products</h1>
                    <p class="text-muted">Showing {{ $products->count() }} of {{ $products->total() }} products</p>
                </div>
                <div class="col-md-6">
                    <form action="/products" method="GET" class="d-flex">
                        <input type="text" name="search" class="form-control me-2" placeholder="Search products..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-primary">Search</button>
                    </form>
                </div>
            </div>

            <!-- Category Filters -->
            <div class="mb-4">
                <h5 class="mb-2">Categories</h5>
                <div class="d-flex flex-wrap">
                    <a href="/products" class="badge bg-secondary bg-opacity-10 text-dark category-filter {{ request()->path() == 'products' && !request()->has('category') ? 'active' : '' }}">
                        All
                    </a>
                    @foreach($categories as $cat)
                        <a href="{{ route('public.category.products', $cat->id) }}" class="badge bg-secondary bg-opacity-10 text-dark category-filter {{ isset($category) && $category->id == $cat->id ? 'active' : '' }}">
                            {{ $cat->name }}
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Products Grid -->
            <div class="row g-4">
                @forelse($products as $product)
                    <div class="col-md-3">
                        <div class="card h-100 border-0 shadow-sm product-card">
                            <div class="position-relative">
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" class="card-img-top" alt="{{ $product->name }}" style="height: 200px; object-fit: cover;">
                                @else
                                    <div class="bg-light text-center p-4" style="height: 200px;">
                                        <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                    </div>
                                @endif
                                <span class="badge bg-success stock-badge">In Stock: {{ $product->current_stock }}</span>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">{{ $product->name }}</h5>
                                <p class="card-text text-muted mb-3">{{ Str::limit($product->description, 50) }}</p>
                                <div class="d-flex justify-content-between align-items-center mt-auto">
                                    <span class="fw-bold text-primary">${{ number_format($product->selling_price, 2) }}</span>
                                    <a href="{{ route('public.product.details', $product->id) }}" class="btn btn-sm btn-primary">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info">
                            No products found. Try a different search or category.
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-5">
                {{ $products->links() }}
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-5 bg-primary text-white">
        <div class="container text-center">
            <h2 class="fw-bold mb-3">Ready to Start Shopping?</h2>
            <p class="lead mb-4">Create an account to place orders and track your purchases.</p>
            <a href="{{ route('public.signup', ['redirect' => url()->current()]) }}" class="btn btn-light btn-lg me-2">Sign Up Now</a>
            <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="btn btn-outline-light btn-lg">Log In</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-4 bg-dark text-white">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5 class="fw-bold mb-3">
                        <i class="bi bi-box-seam me-2"></i>{{ config('app.name', 'Stock Management System') }}
                    </h5>
                    <p>Your complete solution for inventory and stock management.</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-white"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
                <div class="col-lg-2">
                    <h5 class="fw-bold mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="/" class="text-white text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="/products" class="text-white text-decoration-none">Products</a></li>
                        <li class="mb-2"><a href="/#categories" class="text-white text-decoration-none">Categories</a></li>
                        <li class="mb-2"><a href="/#about" class="text-white text-decoration-none">About</a></li>
                    </ul>
                </div>
                <div class="col-lg-2">
                    <h5 class="fw-bold mb-3">Support</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none">Help Center</a></li>
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none">FAQs</a></li>
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none">Contact Us</a></li>
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none">Privacy Policy</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h5 class="fw-bold mb-3">Newsletter</h5>
                    <p>Subscribe to our newsletter for updates and tips.</p>
                    <form class="mb-3">
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Your email">
                            <button class="btn btn-primary" type="submit">Subscribe</button>
                        </div>
                    </form>
                    <p class="small text-muted">© {{ date('Y') }} {{ config('app.name', 'Stock Management System') }}. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html> 