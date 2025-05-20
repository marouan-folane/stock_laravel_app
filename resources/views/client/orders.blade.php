@extends('layouts.client')

@section('title', 'My Orders')

@section('content')
<div class="container">
    <h1 class="mb-4">My Orders</h1>

    @if($orders->isEmpty() && !request()->anyFilled(['status', 'payment_status', 'date_from', 'date_to', 'search']))
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-receipt-cutoff" style="font-size: 4rem; color: #ccc;"></i>
                <h3 class="mt-3">No Orders Found</h3>
                <p class="text-muted">You haven't placed any orders yet.</p>
                <a href="{{ route('client.products') }}" class="btn btn-primary mt-3">
                    <i class="bi bi-cart-plus me-2"></i>Browse Products
                </a>
            </div>
        </div>
    @else
        <!-- Filters Card -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="bi bi-funnel me-2"></i>Filter Orders
                    @if(request()->anyFilled(['status', 'payment_status', 'date_from', 'date_to', 'search']))
                        <a href="{{ route('client.orders') }}" class="btn btn-sm btn-outline-secondary float-end">
                            <i class="bi bi-x-circle me-1"></i>Clear Filters
                        </a>
                    @endif
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('client.orders') }}" method="GET" class="row g-3">
                    <div class="col-md-6 col-lg-3">
                        <label for="status" class="form-label">Order Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="canceled" {{ request('status') == 'canceled' ? 'selected' : '' }}>Canceled</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6 col-lg-3">
                        <label for="payment_status" class="form-label">Payment Status</label>
                        <select name="payment_status" id="payment_status" class="form-select">
                            <option value="">All Payment Statuses</option>
                            <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                            <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6 col-lg-3">
                        <label for="date_from" class="form-label">Date From</label>
                        <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    
                    <div class="col-md-6 col-lg-3">
                        <label for="date_to" class="form-label">Date To</label>
                        <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    
                    <div class="col-md-8">
                        <label for="search" class="form-label">Search Order #</label>
                        <input type="text" name="search" id="search" class="form-control" placeholder="Search by order number" value="{{ request('search') }}">
                    </div>
                    
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-2"></i>Apply Filters
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        @if($orders->isEmpty())
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>No orders found matching your filters.
                <a href="{{ route('client.orders') }}" class="alert-link">Clear filters</a> to see all orders.
            </div>
        @else
            <div class="card">
                <div class="card-header bg-light">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">Order History ({{ $orders->total() }} orders)</h5>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('client.products') }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-cart-plus me-1"></i> New Order
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <td><strong>{{ $order->invoice_number }}</strong></td>
                                    <td>{{ $order->created_at->format('M d, Y') }}</td>
                                    <td>{{ $order->items->count() }}</td>
                                    <td>${{ number_format($order->total_amount, 2) }}</td>
                                    <td>
                                        @if($order->status == 'completed')
                                            <span class="badge bg-success">Completed</span>
                                        @elseif($order->status == 'pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @elseif($order->status == 'canceled')
                                            <span class="badge bg-danger">Canceled</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($order->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($order->payment_status == 'paid')
                                            <span class="badge bg-success">Paid</span>
                                        @elseif($order->payment_status == 'partial')
                                            <span class="badge bg-info">Partial</span>
                                        @else
                                            <span class="badge bg-danger">Unpaid</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('client.orders.details', $order->id) }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i> Details
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-center">
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
        @endif
    @endif
</div>
@endsection 