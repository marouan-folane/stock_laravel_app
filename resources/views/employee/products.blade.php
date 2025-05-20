@extends('layouts.employee')

@section('title', 'Products')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Product Management</h1>
        <div>
            <!-- Admin-only buttons are removed from employee view -->
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('employee.products') }}" method="GET" class="row g-3">
                <div class="col-md-5">
                    <label for="search" class="form-label">Search Products</label>
                    <input type="text" name="search" id="search" class="form-control" placeholder="Search by name or code..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
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
                <div class="col-md-2">
                    <label for="stock" class="form-label">Stock Status</label>
                    <select name="stock" id="stock" class="form-select">
                        <option value="">All</option>
                        <option value="low" {{ request('stock') == 'low' ? 'selected' : '' }}>Low Stock</option>
                        <option value="out" {{ request('stock') == 'out' ? 'selected' : '' }}>Out of Stock</option>
                        <option value="in" {{ request('stock') == 'in' ? 'selected' : '' }}>In Stock</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">Products List</h5>
        </div>
        <div class="card-body">
            @if($products->isEmpty())
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    No products found. Please try different search criteria or add new products.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Cost Price</th>
                                <th>Selling Price</th>
                                <th>Stock</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                                <tr>
                                    <td>{{ $product->code }}</td>
                                    <td>
                                        <strong>{{ $product->name }}</strong>
                                        @if($product->description)
                                            <br><small class="text-muted">{{ Str::limit($product->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $product->category->name }}</td>
                                    <td>${{ number_format($product->cost_price, 2) }}</td>
                                    <td>${{ number_format($product->selling_price, 2) }}</td>
                                    <td class="text-center">
                                        @if($product->current_stock <= $product->min_stock && $product->current_stock > 0)
                                            <span class="badge bg-warning text-dark">{{ $product->current_stock }}</span>
                                        @elseif($product->current_stock <= 0)
                                            <span class="badge bg-danger">{{ $product->current_stock }}</span>
                                        @else
                                            <span class="badge bg-success">{{ $product->current_stock }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($product->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('products.show', $product->id) }}" class="btn btn-sm btn-info text-white" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                                                                       
                                            </a>
                                            <a href="{{ route('employee.products.adjust-stock', $product->id) }}" class="btn btn-sm btn-warning" title="Adjust Stock">
                                                <i class="bi bi-boxes"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $products->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 