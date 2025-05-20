@extends('layouts.client')

@section('title', 'Dashboard')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Welcome, {{ Auth::user()->name }}!</h1>
        <div>
            <span class="text-muted">Today: {{ date('F d, Y') }}</span>
        </div>
    </div>

    <!-- Client Info -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Account Overview</h5>
                </div>
                <div class="card-body">
                    @if(!$customer)
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            No customer profile linked to your account. Please contact support.
                        </div>
                    @else
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Customer Information</h6>
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="30%">Name:</th>
                                        <td>{{ $customer->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email:</th>
                                        <td>{{ $customer->email }}</td>
                                    </tr>
                                    <tr>
                                        <th>Phone:</th>
                                        <td>{{ $customer->phone ?: 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Address:</th>
                                        <td>{{ $customer->address ?: 'N/A' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Account Summary</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="card bg-success text-white">
                                            <div class="card-body">
                                                <h6 class="card-title">Total Orders</h6>
                                                <h3 class="mb-0">{{ $recentOrders->count() }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="card bg-info text-white">
                                            <div class="card-body">
                                                <h6 class="card-title">Total Spent</h6>
                                                <h3 class="mb-0">${{ number_format($totalSpent, 2) }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                    @if($outstandingBalance > 0)
                                    <div class="col-md-12">
                                        <div class="card bg-warning">
                                            <div class="card-body">
                                                <h6 class="card-title">Outstanding Balance</h6>
                                                <h3 class="mb-0">${{ number_format($outstandingBalance, 2) }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Orders</h5>
                    <a href="{{ route('client.orders') }}" class="btn btn-sm btn-primary">View All Orders</a>
                </div>
                <div class="card-body">
                    @if($recentOrders->isEmpty())
                        <p class="text-center text-muted my-4">You don't have any orders yet.</p>
                        <div class="text-center">
                            <a href="{{ route('client.products') }}" class="btn btn-success">
                                <i class="bi bi-cart-plus me-2"></i>Shop Now
                            </a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover border">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Date</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentOrders as $order)
                                    <tr>
                                        <td><strong>{{ $order->invoice_number }}</strong></td>
                                        <td>{{ $order->created_at->format('M d, Y') }}</td>
                                        <td>{{ $order->items->count() }}</td>
                                        <td>${{ number_format($order->total_amount, 2) }}</td>
                                        <td>
                                            @if($order->status == 'completed')
                                                <span class="badge bg-success">Completed</span>
                                            @elseif($order->status == 'pending')
                                                <span class="badge bg-warning">Pending</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($order->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('client.orders.details', $order->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Shop Section -->
    <div class="mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Shop Now</h2>
            <a href="{{ route('client.products') }}" class="btn btn-outline-primary">View All Products</a>
        </div>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-box-seam text-primary" style="font-size: 4rem;"></i>
                        <h4 class="mt-3">Browse Products</h4>
                        <p class="text-muted">View our complete catalog of products.</p>
                        <a href="{{ route('client.products') }}" class="btn btn-primary">Browse Now</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-cart-plus text-success" style="font-size: 4rem;"></i>
                        <h4 class="mt-3">Your Cart</h4>
                        <p class="text-muted">Check your cart and complete your purchase.</p>
                        <a href="{{ route('client.cart') }}" class="btn btn-success">View Cart</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-receipt text-info" style="font-size: 4rem;"></i>
                        <h4 class="mt-3">Order History</h4>
                        <p class="text-muted">Review your previous orders and purchases.</p>
                        <a href="{{ route('client.orders') }}" class="btn btn-info text-white">View Orders</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 