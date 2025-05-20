@extends('layouts.app')

@section('title', isset($filtered) && $filtered ? "Products in {$filterName}" : 'Products')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        @if(isset($filtered) && $filtered)
            @if($filterType == 'category')
                Products in Category: {{ $filterName }}
            @elseif($filterType == 'supplier')
                Products from Supplier: {{ $filterName }}
            @elseif($filterType == 'stock')
                {{ $filterName }} Products
            @elseif($filterType == 'expiry')
                {{ $filterName }} Products
            @endif
        @else
            Products
        @endif
    </h1>
    <div>
        <a href="{{ route('products.create') }}" class="btn btn-primary btn-rounded">
            <i class="fas fa-plus fa-sm me-2"></i> Add New Product
        </a>
        @if(isset($filtered) && $filtered)
            <a href="{{ route('products.index') }}" class="btn btn-secondary btn-rounded">
                <i class="fas fa-times fa-sm me-2"></i> Clear Filter
            </a>
        @endif
    </div>
</div>

<!-- Search and Filter Form -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Search & Filter Products</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('products.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" placeholder="Search by name, code or description" value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label for="category_id" class="form-label">Category</label>
                <select class="form-select" id="category_id" name="category_id">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="supplier_id" class="form-label">Supplier</label>
                <select class="form-select" id="supplier_id" name="supplier_id">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="stock_status" class="form-label">Stock Status</label>
                <select class="form-select" id="stock_status" name="stock_status">
                    <option value="">All Stock Status</option>
                    <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                    <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                    <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="expiry_status" class="form-label">Expiry Status</label>
                <select class="form-select" id="expiry_status" name="expiry_status">
                    <option value="">All Expiry Status</option>
                    <option value="expiring_30" {{ request('expiry_status') == 'expiring_30' ? 'selected' : '' }}>Expiring in 30 days</option>
                    <option value="expiring_60" {{ request('expiry_status') == 'expiring_60' ? 'selected' : '' }}>Expiring in 60 days</option>
                    <option value="expiring_90" {{ request('expiry_status') == 'expiring_90' ? 'selected' : '' }}>Expiring in 90 days</option>
                    <option value="expired" {{ request('expiry_status') == 'expired' ? 'selected' : '' }}>Expired</option>
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">
            @if(isset($filtered) && $filtered)
                Filtered Products
            @else
                All Products
            @endif
        </h6>
        <div class="dropdown no-arrow">
            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                <div class="dropdown-header">Export Options:</div>
                <a class="dropdown-item" href="#">
                    <i class="fas fa-file-csv fa-sm fa-fw me-2 text-gray-400"></i>
                    Export CSV
                </a>
                <a class="dropdown-item" href="#">
                    <i class="fas fa-file-pdf fa-sm fa-fw me-2 text-gray-400"></i>
                    Export PDF
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">
                    <i class="fas fa-print fa-sm fa-fw me-2 text-gray-400"></i>
                    Print
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="productsTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Category</th>
                        <th>Stock</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td class="text-center">
                            @if($product->image)
                          
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="img-fluid rounded" style="max-height: 70px; max-width: 70px; object-fit: cover;">
                        @else
                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-box text-secondary"></i>
                            </div>
                        @endif
                        </td>
                        <td>{{ $product->name }}</td>
                        <td><span class="badge bg-secondary">{{ $product->code }}</span></td>
                        <td>{{ $product->category->name }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                {{ $product->current_stock }} {{ $product->unit }}
                                @if($product->current_stock <= 0)
                                    <span class="badge bg-danger ms-2">Out of Stock</span>
                                @elseif($product->current_stock < $product->min_stock)
                                    <span class="badge bg-warning ms-2">Low Stock</span>
                                @endif
                            </div>
                        </td>
                        <td>${{ number_format($product->selling_price, 2) }}</td>
                        <td>
                            @if($product->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        <td class="action-buttons">
                            <a href="{{ route('products.show', $product) }}" class="btn btn-info btn-sm" data-bs-toggle="tooltip" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('products.edit', $product) }}" class="btn btn-primary btn-sm" data-bs-toggle="tooltip" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="{{ route('products.stock.create', $product->id) }}" class="btn btn-success btn-sm" data-bs-toggle="tooltip" title="Add Stock">
                                <i class="fas fa-plus"></i>
                            </a>
                            <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" data-bs-toggle="tooltip" title="Delete" onclick="return confirm('Are you sure you want to delete this product?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">No products found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-4">
                {{ $products->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>
@endpush 