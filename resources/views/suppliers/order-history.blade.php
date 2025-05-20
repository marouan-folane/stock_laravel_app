@extends('layouts.app')

@section('title', 'Supplier Order History')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Order History: {{ $supplier->name }}</h1>
    <div>
        <a href="{{ route('suppliers.show', $supplier->id) }}" class="btn btn-secondary btn-rounded">
            <i class="fas fa-arrow-left fa-sm me-2"></i> Back to Supplier
        </a>
    </div>
</div>

<!-- Order Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Orders</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_orders'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Total Value</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            ${{ number_format($stats['total_value'], 2) }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Total Items Ordered</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_quantity']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-boxes fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search and Filter Form -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Search & Filter Orders</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('suppliers.order-history', $supplier->id) }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="product_id" class="form-label">Filter by Product</label>
                <select class="form-select" id="product_id" name="product_id">
                    <option value="">All Products</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label for="date_from" class="form-label">Date From</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
            </div>

            <div class="col-md-3">
                <label for="date_to" class="form-label">Date To</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <div class="btn-group w-100">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('suppliers.order-history', $supplier->id) }}" class="btn btn-secondary">
                        <i class="fas fa-redo me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Orders Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">All Orders</h6>
        <a href="{{ route('products.create', ['supplier_id' => $supplier->id]) }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus fa-sm me-1"></i> Add New Product
        </a>
    </div>
    <div class="card-body">
        @if($adjustments->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Unit Cost</th>
                            <th>Total Cost</th>
                            <th>Reference</th>
                            <th>Added By</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($adjustments as $adjustment)
                        <tr>
                            <td>
                                <a href="{{ route('products.show', $adjustment->product_id) }}">
                                    {{ $adjustment->product->name }}
                                </a>
                                <small class="d-block text-muted">{{ $adjustment->product->code }}</small>
                            </td>
                            <td>{{ $adjustment->quantity }}</td>
                            <td>${{ number_format($adjustment->product->cost_price, 2) }}</td>
                            <td>${{ number_format($adjustment->quantity * $adjustment->product->cost_price, 2) }}</td>
                            <td>{{ $adjustment->reference_number ?? 'N/A' }}</td>
                            <td>{{ $adjustment->user->name ?? 'System' }}</td>
                            <td>
                                @if($adjustment->notes)
                                    <button type="button" class="btn btn-sm btn-info view-notes" data-bs-toggle="modal" data-bs-target="#notesModal" data-notes="{{ $adjustment->notes }}">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                @else
                                    <small class="text-muted">No notes</small>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                {{ $adjustments->withQueryString()->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-shopping-cart fa-4x text-gray-300 mb-3"></i>
                <p class="text-gray-500 mb-0">No order history found</p>
                @if(request()->has('product_id') || request()->has('date_from') || request()->has('date_to'))
                    <p class="text-muted">Try adjusting your search filters</p>
                @else
                    <a href="{{ route('products.index') }}?supplier_id={{ $supplier->id }}" class="btn btn-primary mt-2">
                        <i class="fas fa-plus fa-sm me-1"></i> Add Product Stock
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>

<!-- Notes Modal -->
<div class="modal fade" id="notesModal" tabindex="-1" aria-labelledby="notesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notesModalLabel">Order Notes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="modal-notes-content"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize select2 for dropdown menus if available
        if($.fn.select2) {
            $('#product_id').select2({
                placeholder: 'Select a product',
                allowClear: true
            });
        }
        
        // Show notes in modal
        $('.view-notes').click(function() {
            var notes = $(this).data('notes');
            $('#modal-notes-content').text(notes);
        });
    });
</script>
@endpush 