@extends('layouts.client')

@section('title', 'Products')

@section('content')
<div class="container">
    <h1 class="mb-4">Available Products</h1>

    <!-- Search and Category Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('client.products') }}" method="GET" class="row g-3">
                <div class="col-md-6">
                    <label for="search" class="form-label">Search Products</label>
                    <input type="text" name="search" id="search" class="form-control" placeholder="Search by name or code..." value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <label for="category" class="form-label">Filter by Category</label>
                    <select name="category" id="category" class="form-select">
                        <option value="">All Categories</option>
                        @foreach(App\Models\Category::orderBy('name')->get() as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    @if($products->isEmpty())
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            No products found. Please try different search criteria.
        </div>
    @else
        <div class="row row-cols-1 row-cols-md-3 g-4">
            @foreach($products as $product)
                <div class="col">
                    <div class="card h-100">
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" class="card-img-top" alt="{{ $product->name }}" style="height: 200px; object-fit: contain; padding: 15px;">
                        @else
                            <div class="text-center p-4 bg-light">
                                <i class="bi bi-box-seam" style="font-size: 5rem; color: #ccc;"></i>
                            </div>
                        @endif
                        <div class="card-body">
                            <h5 class="card-title">{{ $product->name }}</h5>
                            <p class="card-text">
                                <small class="text-muted">{{ $product->category->name }}</small>
                            </p>
                            <p class="card-text">{{ Str::limit($product->description, 100) }}</p>
                            
                            <!-- Stock Status -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-bold">${{ number_format($product->selling_price, 2) }}</span>
                                
                                @if($product->current_stock > 10)
                                    <span class="badge bg-success">In Stock ({{ $product->current_stock }})</span>
                                @elseif($product->current_stock > 0)
                                    <span class="badge bg-warning text-dark">Low Stock ({{ $product->current_stock }})</span>
                                @else
                                    <span class="badge bg-danger">Out of Stock</span>
                                @endif
                            </div>
                            
                            <!-- Add to Cart Form -->
                            <form action="{{ route('client.cart.add') }}" method="POST">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <div class="input-group mb-3">
                                    <input type="number" name="quantity" value="1" min="1" max="{{ $product->current_stock }}" class="form-control" {{ $product->current_stock <= 0 ? 'disabled' : '' }}>
                                    <button type="submit" class="btn btn-success" {{ $product->current_stock <= 0 ? 'disabled' : '' }}>
                                        <i class="bi bi-cart-plus"></i> Add to Cart
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $products->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection 