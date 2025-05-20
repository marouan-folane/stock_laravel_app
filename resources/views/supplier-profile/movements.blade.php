@extends('layouts.app')

@section('title', 'Stock Movements')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Stock Movements History</h5>
                    <a href="{{ route('supplier.profile') }}" class="btn btn-sm btn-light">Back to Dashboard</a>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">Filter Movements</h6>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('supplier.movements') }}">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="date_from">From Date</label>
                                            <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="date_to">To Date</label>
                                            <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="product_id">Product</label>
                                            <select class="form-control" id="product_id" name="product_id">
                                                <option value="">All Products</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                                        {{ $product->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="type">Type</label>
                                            <select class="form-control" id="type" name="type">
                                                <option value="">All Types</option>
                                                <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>In</option>
                                                <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>Out</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                    <a href="{{ route('supplier.movements') }}" class="btn btn-secondary">Reset</a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Movements</h5>
                                    <h2 class="card-text">{{ $stats['total_movements'] }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Quantity</h5>
                                    <h2 class="card-text">{{ $stats['total_quantity'] }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Value</h5>
                                    <h2 class="card-text">{{ number_format($stats['total_value'], 2) }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Movements Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Product</th>
                                    <th>Type</th>
                                    <th>Quantity</th>
                                    <th>Reference</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($movements as $movement)
                                    <tr>
                                        <td>{{ $movement->created_at->format('Y-m-d H:i') }}</td>
                                        <td>{{ $movement->product->name }}</td>
                                        <td>
                                            <span class="badge bg-{{ $movement->type == 'in' ? 'success' : 'danger' }}">
                                                {{ $movement->type == 'in' ? 'In' : 'Out' }}
                                            </span>
                                        </td>
                                        <td>{{ $movement->quantity }}</td>
                                        <td>{{ $movement->reference_no ?: 'N/A' }}</td>
                                        <td>{{ $movement->notes ?: 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No movements found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $movements->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 