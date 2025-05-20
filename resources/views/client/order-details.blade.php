@extends('layouts.client')

@section('title', 'Order Details')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Order #{{ $order->invoice_number }}</h1>
        <a href="{{ route('client.orders') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-2"></i>Back to Orders
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Order Details -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Order Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6>Order Details</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Order Number:</th>
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
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Payment Information</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Payment Status:</th>
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
                                <tr>
                                    <th>Amount Paid:</th>
                                    <td>${{ number_format($order->paid_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Balance Due:</th>
                                    <td>${{ number_format($order->total_amount - $order->paid_amount, 2) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    @if($order->notes)
                        <div class="alert alert-info">
                            <h6>Order Notes:</h6>
                            <p class="mb-0">{{ $order->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Order Timeline</h5>
                </div>
                <div class="card-body">
                    <ul class="timeline">
                        <li class="timeline-item mb-4">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="mb-0">Order Placed</h6>
                                <small class="text-muted">{{ $order->created_at->format('M d, Y g:i A') }}</small>
                                <p class="mt-2">Your order has been received and is being processed.</p>
                            </div>
                        </li>
                        
                        @if($order->status == 'pending')
                            <li class="timeline-item mb-4">
                                <div class="timeline-marker bg-warning"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-0">Processing</h6>
                                    <small class="text-muted">Pending</small>
                                    <p class="mt-2">Your order is being processed by our team.</p>
                                </div>
                            </li>
                        @elseif($order->status == 'completed')
                            <li class="timeline-item mb-4">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-0">Order Completed</h6>
                                    <small class="text-muted">{{ $order->updated_at->format('M d, Y g:i A') }}</small>
                                    <p class="mt-2">Your order has been completed and is ready for pickup/delivery.</p>
                                </div>
                            </li>
                        @elseif($order->status == 'canceled')
                            <li class="timeline-item mb-4">
                                <div class="timeline-marker bg-danger"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-0">Order Canceled</h6>
                                    <small class="text-muted">{{ $order->updated_at->format('M d, Y g:i A') }}</small>
                                    <p class="mt-2">This order has been canceled.</p>
                                </div>
                            </li>
                        @endif
                        
                        @if($order->payment_status == 'paid')
                            <li class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-0">Payment Received</h6>
                                    <small class="text-muted">{{ $order->updated_at->format('M d, Y g:i A') }}</small>
                                    <p class="mt-2">Payment has been received for this order.</p>
                                </div>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Items -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Ordered Items</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th class="text-center">Price</th>
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
                                        <br><small class="text-muted">SKU: {{ $item->product->code }}</small>
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
                            <th colspan="5" class="text-end">Tax (10%):</th>
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

    <!-- Call to Action Buttons -->
    <div class="d-flex justify-content-between">
        <a href="{{ route('client.orders') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Orders
        </a>
        
        <div>
            @if($order->status == 'pending')
                <button class="btn btn-outline-danger me-2" onclick="cancelOrder()">
                    <i class="bi bi-x-circle me-2"></i>Cancel Order
                </button>
            @endif
            
            <a href="{{ route('client.products') }}" class="btn btn-primary">
                <i class="bi bi-cart-plus me-2"></i>Place New Order
            </a>
        </div>
    </div>
</div>

@push('styles')
<style>
    .timeline {
        position: relative;
        padding-left: 30px;
        list-style: none;
    }
    
    .timeline-item {
        position: relative;
    }
    
    .timeline-marker {
        position: absolute;
        left: -30px;
        width: 15px;
        height: 15px;
        border-radius: 50%;
        margin-top: 5px;
    }
    
    .timeline-content {
        padding-bottom: 15px;
        border-bottom: 1px dashed #dee2e6;
    }
    
    .timeline-item:last-child .timeline-content {
        border-bottom: none;
        padding-bottom: 0;
    }

    /* Cancel confirmation styles */
    .cancel-confirm-input.is-invalid {
        border-color: #dc3545;
    }
</style>
@endpush

<!-- Cancel Order Confirmation Modal -->
<div class="modal fade" id="cancelOrderModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="cancelOrderModalLabel">Confirm Order Cancellation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cancelOrderForm" action="{{ route('client.orders.cancel', $order->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Warning:</strong> This action cannot be undone.
                    </div>
                    <p>To confirm cancellation of order <strong>#{{ $order->invoice_number }}</strong>, please type <strong>CONFIRM</strong> in the box below:</p>
                    <div class="mb-3">
                        <input type="text" id="cancelConfirmInput" class="form-control" placeholder="Type CONFIRM to proceed">
                        <div class="invalid-feedback">
                            Please type CONFIRM to proceed with cancellation
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" id="cancelOrderButton" class="btn btn-danger" disabled>Cancel Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Wait for document to be ready
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize event listeners for the confirmation input
        var confirmInput = document.getElementById('cancelConfirmInput');
        var cancelForm = document.getElementById('cancelOrderForm');
        var confirmButton = document.getElementById('cancelOrderButton');
        
        if (confirmInput && confirmButton) {
            confirmInput.addEventListener('input', function() {
                var inputValue = this.value.trim();
                var isValid = inputValue === 'CONFIRM';
                
                // Add or remove invalid class
                if (inputValue && !isValid) {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                }
                
                // Enable/disable the button
                confirmButton.disabled = !isValid;
            });
        }
    });

    function cancelOrder() {
        // Get the modal element
        var modalElement = document.getElementById('cancelOrderModal');
        
        // Reset the input field
        var confirmInput = document.getElementById('cancelConfirmInput');
        if (confirmInput) {
            confirmInput.value = '';
        }
        
        // Disable the button
        var confirmButton = document.getElementById('cancelOrderButton');
        if (confirmButton) {
            confirmButton.disabled = true;
        }
        
        // Show the modal using jQuery (which is usually available in Laravel)
        try {
            // First try the Bootstrap 5 way
            if (typeof bootstrap !== 'undefined') {
                var modal = new bootstrap.Modal(modalElement);
                modal.show();
            } 
            // Fall back to jQuery 
            else if (typeof $ !== 'undefined') {
                $(modalElement).modal('show');
            } 
            // Basic JS fallback if all else fails
            else {
                modalElement.style.display = 'block';
                modalElement.classList.add('show');
                document.body.classList.add('modal-open');
            }
        } catch (e) {
            console.error("Error showing modal:", e);
            
            // If modal fails, use basic confirmation
            if (confirm("Are you sure you want to cancel order #{{ $order->invoice_number }}? This action cannot be undone.")) {
                document.getElementById('cancelOrderForm').submit();
            }
        }
    }
</script>
@endpush
@endsection 