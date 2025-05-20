@extends('layouts.employee')

@section('title', 'Order Details')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Order #{{ $order->invoice_number }}</h1>
        <div class="d-flex">
            <a href="{{ route('employee.pending-orders') }}" class="btn btn-outline-primary me-2">
                <i class="bi bi-arrow-left"></i> Back to Orders
            </a>
            @if($order->status == 'pending')
                <form action="{{ route('employee.process-order', $order->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to mark this order as completed?')">
                        <i class="bi bi-check-circle"></i> Mark as Completed
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="row mb-4">
        <!-- Order Information -->
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Order Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="35%">Order Number:</th>
                            <td>{{ $order->invoice_number }}</td>
                        </tr>
                        <tr>
                            <th>Date:</th>
                            <td>{{ $order->created_at->format('M d, Y g:i A') }}</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
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
                        </tr>
                        <tr>
                            <th>Payment Method:</th>
                            <td>{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</td>
                        </tr>
                        <tr>
                            <th>Payment Status:</th>
                            <td>
                                @if($order->payment_status == 'paid')
                                    <span class="badge bg-success">Paid</span>
                                @elseif($order->payment_status == 'partial')
                                    <span class="badge bg-info">Partial</span>
                                @else
                                    <span class="badge bg-danger">Unpaid</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Total Amount:</th>
                            <td>${{ number_format($order->total_amount, 2) }}</td>
                        </tr>
                        @if($order->notes)
                        <tr>
                            <th>Notes:</th>
                            <td>{{ $order->notes }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Customer Information -->
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Customer Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="35%">Name:</th>
                            <td>{{ $order->customer ? $order->customer->name : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td>{{ $order->customer ? $order->customer->email : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Phone:</th>
                            <td>{{ $order->customer && $order->customer->phone ? $order->customer->phone : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Address:</th>
                            <td>{{ $order->customer && $order->customer->address ? $order->customer->address : 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="card-footer bg-light">
                    @if($order->customer)
                        <a href="{{ route('customers.show', $order->customer->id) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-person"></i> View Customer Profile
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Order Items -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Order Items</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th class="text-center">Unit Price</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-center">Tax</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $item->product->name }}</strong>
                                    @if($item->product->code)
                                        <br><small class="text-muted">{{ $item->product->code }}</small>
                                    @endif
                                </td>
                                <td class="text-center">${{ number_format($item->price, 2) }}</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-center">${{ number_format($item->tax, 2) }}</td>
                                <td class="text-end">${{ number_format($item->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="5" class="text-end">Subtotal:</th>
                            <th class="text-end">${{ number_format($order->total_amount - $order->tax, 2) }}</th>
                        </tr>
                        <tr>
                            <th colspan="5" class="text-end">Tax:</th>
                            <th class="text-end">${{ number_format($order->tax, 2) }}</th>
                        </tr>
                        <tr>
                            <th colspan="5" class="text-end">Total:</th>
                            <th class="text-end">${{ number_format($order->total_amount, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection 