@extends('layouts.employee')

@section('title', 'Pending Orders')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Pending Orders</h1>
        <div>
            <span class="text-muted">Today: {{ date('F d, Y') }}</span>
        </div>
    </div>

    @if($pendingOrders->isEmpty())
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            No pending orders found. All orders have been processed.
        </div>
    @else
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Orders Awaiting Processing</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Total Amount</th>
                                <th>Payment Method</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingOrders as $order)
                                <tr>
                                    <td>{{ $order->invoice_number }}</td>
                                    <td>
                                        <strong>{{ $order->customer ? $order->customer->name : 'N/A' }}</strong>
                                        @if($order->customer && $order->customer->phone)
                                            <br><small>{{ $order->customer->phone }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $order->created_at->format('M d, Y g:i A') }}</td>
                                    <td class="text-center">{{ $order->items->count() }}</td>
                                    <td>${{ number_format($order->total_amount, 2) }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('employee.order-details', $order->id) }}" class="btn btn-sm btn-info text-white" title="View Details">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                            <form action="{{ route('employee.process-order', $order->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" title="Mark as Completed" onclick="return confirm('Are you sure you want to mark this order as completed?')">
                                                    <i class="bi bi-check-circle"></i> Complete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-center">
                    {{ $pendingOrders->links() }}
                </div>
            </div>
        </div>
    @endif
</div>
@endsection 