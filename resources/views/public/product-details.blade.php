<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ $product->name }} - {{ config('app.name', 'Stock Management System') }}</title>
    
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
        .product-img {
            max-height: 400px;
            object-fit: contain;
            width: 100%;
        }
        .stock-badge {
            font-size: 1rem;
        }
        .related-product-card {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 10px;
            overflow: hidden;
        }
        .related-product-card:hover {
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            transform: translateY(-5px);
        }
        .product-category {
            font-size: 0.9rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 1px;
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
                        <a class="nav-link active" href="/#products">Products</a>
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
                    <li class="breadcrumb-item"><a href="/#products">Products</a></li>
                    @if($product->category)
                        <li class="breadcrumb-item"><a href="/#categories">{{ $product->category->name }}</a></li>
                    @endif
                    <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Product Detail Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <!-- Product Image -->
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-0 text-center">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="product-img">
                            @else
                                <div class="bg-light p-5 d-flex justify-content-center align-items-center" style="height: 400px;">
                                    <i class="bi bi-image text-muted" style="font-size: 5rem;"></i>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Product Info -->
                <div class="col-lg-6">
                    <h1 class="mb-1">{{ $product->name }}</h1>
                    
                    @if($product->category)
                        <div class="product-category mb-3">
                            <i class="bi bi-tag me-1"></i> {{ $product->category->name }}
                        </div>
                    @endif
                    
                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-3">
                            <span class="h3 mb-0 fw-bold text-primary">${{ number_format($product->selling_price, 2) }}</span>
                            @if($product->current_stock > 0)
                                <span class="badge bg-success ms-3 stock-badge">In Stock: {{ $product->current_stock }}</span>
                            @else
                                <span class="badge bg-danger ms-3 stock-badge">Out of Stock</span>
                            @endif
                        </div>
                        
                        <div class="product-details">
                            @if($product->code)
                                <p><strong>Product Code:</strong> {{ $product->code }}</p>
                            @endif
                            
                            <p><strong>Description:</strong></p>
                            <p>{{ $product->description ?: 'No description available.' }}</p>
                        </div>
                    </div>
                    
                    @auth
                        @if(Auth::user()->role === 'client' && $product->current_stock > 0)
                            <form action="{{ route('client.cart.add') }}" method="POST" class="d-flex mb-3">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <div class="input-group me-3" style="width: 130px;">
                                    <span class="input-group-text">Qty</span>
                                    <input type="number" class="form-control" name="quantity" value="1" min="1" max="{{ $product->current_stock }}">
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-cart-plus me-2"></i>Add to Cart
                                </button>
                            </form>
                        @endif
                    @else
                        <a href="{{ route('public.signup', ['redirect' => url()->current()]) }}" class="btn btn-primary me-2">
                            <i class="bi bi-person-plus me-2"></i>Sign Up to Order
                        </a>
                        <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Login
                        </a>
                    @endauth
                    
                    <div class="mt-4">
                        <h5>Product Specifications</h5>
                        <table class="table">
                            <tbody>
                                @if($product->code)
                                    <tr>
                                        <th scope="row" style="width: 40%;">Code</th>
                                        <td>{{ $product->code }}</td>
                                    </tr>
                                @endif
                                @if($product->category)
                                    <tr>
                                        <th scope="row">Category</th>
                                        <td>{{ $product->category->name }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <th scope="row">Price</th>
                                    <td>${{ number_format($product->selling_price, 2) }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">Availability</th>
                                    <td>{{ $product->current_stock > 0 ? 'In Stock' : 'Out of Stock' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Related Products Section -->
    @if($relatedProducts->count() > 0)
        <section class="py-5 bg-light">
            <div class="container">
                <h2 class="mb-4">Related Products</h2>
                <div class="row g-4">
                    @foreach($relatedProducts as $relatedProduct)
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm related-product-card">
                                <div class="position-relative">
                                    @if($relatedProduct->image)
                                        <img src="{{ asset('storage/' . $relatedProduct->image) }}" class="card-img-top" alt="{{ $relatedProduct->name }}" style="height: 200px; object-fit: cover;">
                                    @else
                                        <div class="bg-light text-center p-4">
                                            <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                        </div>
                                    @endif
                                    <span class="badge bg-success position-absolute top-0 end-0 mt-2 me-2">In Stock: {{ $relatedProduct->current_stock }}</span>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title">{{ $relatedProduct->name }}</h5>
                                    <p class="card-text text-primary fw-bold">${{ number_format($relatedProduct->selling_price, 2) }}</p>
                                    <a href="{{ route('public.product.details', $relatedProduct->id) }}" class="btn btn-outline-primary">View Details</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

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
                        <li class="mb-2"><a href="/#products" class="text-white text-decoration-none">Products</a></li>
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