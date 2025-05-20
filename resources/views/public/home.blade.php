<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config('app.name', 'Stock Management System') }} - Home</title>
    
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .hero {
            background: linear-gradient(135deg, #4a6cf7 0%, #2541b2 100%);
            color: white;
            padding: 6rem 0;
        }
        .feature-card {
            transition: transform 0.3s ease;
            border-radius: 10px;
            overflow: hidden;
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
        .category-card {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 10px;
            overflow: hidden;
        }
        .category-card:hover {
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .product-card {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 10px;
            overflow: hidden;
        }
        .product-card:hover {
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .stock-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 10;
        }
        .navbar-top {
            background-color: white;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
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
                        <a class="nav-link active" href="/">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#products">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#categories">Categories</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
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
                                <a class="nav-link btn btn-primary text-black" href="{{ route('public.signup', ['redirect' => url()->current()]) }}">Sign Up</a>
                            </div>
                        @endauth
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <h1 class="display-4 fw-bold mb-3">Smart Inventory Management Solution</h1>
                    <p class="lead mb-4">Efficiently manage your stock, track inventory, process sales, and more with our intuitive management system.</p>
                    <div class="d-flex gap-3">
                        <a href="{{ route('public.signup', ['redirect' => url()->current()]) }}" class="btn btn-light btn-lg">Get Started</a>
                        <a href="#features" class="btn btn-outline-light btn-lg">Learn More</a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <img src="{{ asset('img/back.jpeg') }}" alt="Inventory Management" class="img-fluid rounded shadow-lg">
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-3 mb-4 mb-md-0">
                    <div class="card border-0 bg-white shadow-sm p-4">
                        <div class="display-4 text-primary mb-2">{{ $totalProducts }}</div>
                        <div class="fw-bold">Total Products</div>
                    </div>
                </div>
                <div class="col-md-3 mb-4 mb-md-0">
                    <div class="card border-0 bg-white shadow-sm p-4">
                        <div class="display-4 text-success mb-2">{{ $inStockProducts }}</div>
                        <div class="fw-bold">In-Stock Products</div>
                    </div>
                </div>
                <div class="col-md-3 mb-4 mb-md-0">
                    <div class="card border-0 bg-white shadow-sm p-4">
                        <div class="display-4 text-info mb-2">{{ $categories->count() }}</div>
                        <div class="fw-bold">Categories</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 bg-white shadow-sm p-4">
                        <div class="display-4 text-warning mb-2">24/7</div>
                        <div class="fw-bold">Support Available</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Powerful Features</h2>
                <p class="text-muted">Our system provides everything you need to manage your inventory efficiently</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm feature-card">
                        <div class="card-body text-center p-4">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3 d-inline-block mb-3">
                                <i class="bi bi-boxes text-primary fs-3"></i>
                            </div>
                            <h4>Inventory Tracking</h4>
                            <p class="text-muted">Keep track of your inventory in real-time with automated stock updates.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm feature-card">
                        <div class="card-body text-center p-4">
                            <div class="rounded-circle bg-success bg-opacity-10 p-3 d-inline-block mb-3">
                                <i class="bi bi-cart-check text-success fs-3"></i>
                            </div>
                            <h4>Sales Management</h4>
                            <p class="text-muted">Process sales, generate invoices, and track customer orders seamlessly.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm feature-card">
                        <div class="card-body text-center p-4">
                            <div class="rounded-circle bg-info bg-opacity-10 p-3 d-inline-block mb-3">
                                <i class="bi bi-graph-up text-info fs-3"></i>
                            </div>
                            <h4>Advanced Reports</h4>
                            <p class="text-muted">Generate insightful reports to make better business decisions.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm feature-card">
                        <div class="card-body text-center p-4">
                            <div class="rounded-circle bg-warning bg-opacity-10 p-3 d-inline-block mb-3">
                                <i class="bi bi-people text-warning fs-3"></i>
                            </div>
                            <h4>User Management</h4>
                            <p class="text-muted">Assign roles and permissions to staff members for better control.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm feature-card">
                        <div class="card-body text-center p-4">
                            <div class="rounded-circle bg-danger bg-opacity-10 p-3 d-inline-block mb-3">
                                <i class="bi bi-bell text-danger fs-3"></i>
                            </div>
                            <h4>Low Stock Alerts</h4>
                            <p class="text-muted">Get notified when stock levels fall below the minimum threshold.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm feature-card">
                        <div class="card-body text-center p-4">
                            <div class="rounded-circle bg-secondary bg-opacity-10 p-3 d-inline-block mb-3">
                                <i class="bi bi-phone text-secondary fs-3"></i>
                            </div>
                            <h4>Mobile Friendly</h4>
                            <p class="text-muted">Access your inventory system from anywhere on any device.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section id="categories" class="py-5 bg-light">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold mb-0">Product Categories</h2>
                <a href="/products/all" class="btn btn-outline-primary">View All Products</a>
            </div>
            <div class="row g-4">
                @forelse($categories as $category)
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm category-card">
                            <div class="card-body p-4 text-center">
                                <div class="rounded-circle bg-primary bg-opacity-10 p-3 d-inline-block mb-3">
                                    <i class="bi bi-tag text-primary fs-3"></i>
                                </div>
                                <h5>{{ $category->name }}</h5>
                                <p class="text-muted mb-2">{{ $category->products->where('is_active', true)->where('current_stock', '>', 0)->count() }} Products</p>
                                <a href="{{ route('public.category.products', $category->id) }}" class="btn btn-sm btn-outline-secondary">Browse</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info">
                            No categories available at this time.
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section id="products" class="py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold mb-0">Featured Products</h2>
                <a href="/products/all" class="btn btn-outline-primary">View All Products</a>
            </div>
            <div class="row g-4">
                @forelse($featuredProducts as $product)
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm product-card">
                            <div class="position-relative">
                                <img src="{{ asset('storage/' . $product->image)  }}" class="card-img-top" alt="{{ $product->name }}">
                                <span class="badge bg-success stock-badge">In Stock: {{ $product->current_stock }}</span>
                            </div>
                            <div class="card-body p-4">
                                <h5 class="card-title">{{ $product->name }}</h5>
                                <p class="card-text text-muted">{{ Str::limit($product->description, 100) }}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-primary">${{ number_format($product->selling_price, 2) }}</span>
                                    <a href="{{ route('public.product.details', $product->id) }}" class="btn btn-sm btn-primary">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info">
                            No featured products available at this time.
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-5 bg-light">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <img src="{{ asset('img/back.jpeg') }}" alt="About Us" class="img-fluid rounded shadow">
                </div>
                <div class="col-lg-6">
                    <h2 class="fw-bold mb-4">About Our Stock Management System</h2>
                    <p class="mb-4">Our inventory management system is designed to help businesses of all sizes efficiently track and manage their stock. With our easy-to-use interface, you can streamline your inventory processes, reduce errors, and save time.</p>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-primary me-2"></i> Real-time inventory tracking</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-primary me-2"></i> Seamless sales management</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-primary me-2"></i> Detailed reporting and analytics</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-primary me-2"></i> Low stock alerts and notifications</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-primary me-2"></i> Multi-user access with role-based permissions</li>
                    </ul>
                    <a href="{{ route('public.signup', ['redirect' => url()->current()]) }}" class="btn btn-primary mt-3">Get Started Today</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-5 bg-primary text-white">
        <div class="container text-center">
            <h2 class="fw-bold mb-3">Ready to Transform Your Inventory Management?</h2>
            <p class="lead mb-4">Join thousands of businesses that have improved their operations with our system.</p>
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
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none">Products</a></li>
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none">Categories</a></li>
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none">About</a></li>
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