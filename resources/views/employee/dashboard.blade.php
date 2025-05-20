@extends('layouts.employee')

@section('title', 'Dashboard')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Employee Dashboard</h1>
        <div>
            <span class="text-muted">Today: {{ date('F d, Y') }}</span>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">Total Products</h5>
                            <h2 class="mt-2 mb-0">{{ $totalProducts }}</h2>
                        </div>
                        <div>
                            <i class="bi bi-box-seam fs-1"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="{{ route('employee.products') }}">View All Products</a>
                    <div class="small text-white"><i class="bi bi-chevron-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-dark mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">Low Stock Products</h5>
                            <h2 class="mt-2 mb-0">{{ $lowStockProducts }}</h2>
                        </div>
                        <div>
                            <i class="bi bi-exclamation-triangle fs-1"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-dark stretched-link" href="{{ route('employee.products') }}">View Low Stock</a>
                    <div class="small text-dark"><i class="bi bi-chevron-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">Total Customers</h5>
                            <h2 class="mt-2 mb-0">{{ $totalCustomers }}</h2>
                        </div>
                        <div>
                            <i class="bi bi-people fs-1"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="{{ route('employee.customers') }}">View All Customers</a>
                    <div class="small text-white"><i class="bi bi-chevron-right"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Sales -->
        <div class="col-lg-7">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <i class="bi bi-receipt me-1"></i>
                    Recent Sales
                </div>
                <div class="card-body">
                    @if($recentSales->isEmpty())
                        <p class="text-muted">No recent sales found.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Invoice</th>
                                        <th>Customer</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentSales as $sale)
                                    <tr>
                                        <td>#{{ $sale->invoice_number }}</td>
                                        <td>{{ $sale->customer ? $sale->customer->name : 'N/A' }}</td>
                                        <td>{{ $sale->created_at->format('M d, Y') }}</td>
                                        <td>${{ number_format($sale->total_amount, 2) }}</td>
                                        <td>
                                            @if($sale->status == 'completed')
                                                <span class="badge bg-success">Completed</span>
                                            @elseif($sale->status == 'pending')
                                                <span class="badge bg-warning">Pending</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($sale->status) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                <div class="card-footer bg-light">
                    <a href="{{ route('employee.sales') }}" class="btn btn-sm btn-primary">View All Sales</a>
                </div>
            </div>
        </div>

        <!-- Low Stock Products -->
        <div class="col-lg-5">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Low Stock Products
                </div>
                <div class="card-body">
                    @if($lowStockProductsList->isEmpty())
                        <p class="text-muted">No low stock products found.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Current Stock</th>
                                        <th>Min. Stock</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lowStockProductsList as $product)
                                    <tr>
                                        <td>{{ $product->name }}</td>
                                        <td>
                                            <span class="badge bg-danger">{{ $product->current_stock }}</span>
                                        </td>
                                        <td>{{ $product->min_stock }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                <div class="card-footer bg-light">
                    <a href="{{ route('employee.products') }}" class="btn btn-sm btn-primary">View All Products</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 