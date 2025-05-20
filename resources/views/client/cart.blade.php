@extends('layouts.client')

@section('title', 'Shopping Cart')

@section('content')
<div class="container">
    <h1 class="mb-4">Your Shopping Cart</h1>

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(empty($cart))
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-cart-x" style="font-size: 4rem; color: #ccc;"></i>
                <h3 class="mt-3">Your cart is empty</h3>
                <p class="text-muted">Looks like you haven't added any products to your cart yet.</p>
                <a href="{{ route('client.products') }}" class="btn btn-primary mt-3">
                    <i class="bi bi-cart-plus me-2"></i>Browse Products
                </a>
            </div>
        </div>
    @else
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Cart Items ({{ count($cart) }})</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="50%">Product</th>
                                        <th class="text-center">Price</th>
                                        <th class="text-center">Quantity</th>
                                        <th class="text-end">Subtotal</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $total = 0; @endphp
                                    @foreach($cart as $id => $item)
                                        @php 
                                            $subtotal = $item['price'] * $item['quantity'];
                                            $total += $subtotal;
                                        @endphp
                                        <tr>
                                            <td>
                                                <strong>{{ $item['name'] }}</strong>
                                            </td>
                                            <td class="text-center">${{ number_format($item['price'], 2) }}</td>
                                            <td class="text-center">{{ $item['quantity'] }}</td>
                                            <td class="text-end">${{ number_format($subtotal, 2) }}</td>
                                            <td class="text-end">
                                                <form action="{{ route('client.cart.remove', $id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to remove this item?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('client.products') }}" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-left me-2"></i>Continue Shopping
                            </a>
                            <form action="{{ route('client.cart') }}" method="POST">
                                @csrf
                                <button type="submit" name="update_cart" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise me-2"></i>Update Cart
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>${{ number_format($total, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax (10%):</span>
                            <span>${{ number_format($total * 0.1, 2) }}</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total:</strong>
                            <strong>${{ number_format($total * 1.1, 2) }}</strong>
                        </div>
                        <form action="{{ route('client.place.order') }}" method="POST">
                            @csrf
                            @foreach($cart as $id => $item)
                                <input type="hidden" name="products[{{ $id }}][id]" value="{{ $id }}">
                                <input type="hidden" name="products[{{ $id }}][quantity]" value="{{ $item['quantity'] }}">
                                <input type="hidden" name="products[{{ $id }}][price]" value="{{ $item['price'] }}">
                            @endforeach
                            
                            <div class="mb-3">
                                <label for="payment_method" class="form-label">Payment Method</label>
                                <select name="payment_method" id="payment_method" class="form-select" required>
                                    <option value="">Select Payment Method</option>
                                    <option value="cash">Cash</option>
                                    <option value="credit_card">Credit Card</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="notes" class="form-label">Order Notes (Optional)</label>
                                <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Special instructions for your order..."></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-check-circle me-2"></i>Place Order
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection 