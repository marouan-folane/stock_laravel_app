@extends('layouts.employee')

@section('title', 'Adjust Stock')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Adjust Stock: {{ $product->name }}</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('employee.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('employee.products') }}">Products</a></li>
                <li class="breadcrumb-item active">Adjust Stock</li>
            </ol>
        </nav>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Stock Adjustment</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('employee.products.update-stock', $product->id) }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="adjustment_type" class="form-label">Adjustment Type</label>
                            <select name="adjustment_type" id="adjustment_type" class="form-select @error('adjustment_type') is-invalid @enderror" required>
                                <option value="">Select Type</option>
                                <option value="add" {{ old('adjustment_type') == 'add' ? 'selected' : '' }}>Add Stock</option>
                                <option value="subtract" {{ old('adjustment_type') == 'subtract' ? 'selected' : '' }}>Remove Stock</option>
                            </select>
                            @error('adjustment_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="quantity" class="form-label">Quantity</label>
                            <input type="number" name="quantity" id="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity') }}" min="1" required>
                            @error('quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="reason" class="form-label">Reason</label>
                            <select name="reason" id="reason" class="form-select @error('reason') is-invalid @enderror" required>
                                <option value="">Select Reason</option>
                                <optgroup label="Add Stock">
                                    <option value="Purchase" {{ old('reason') == 'Purchase' ? 'selected' : '' }}>Purchase</option>
                                    <option value="Return" {{ old('reason') == 'Return' ? 'selected' : '' }}>Return from Customer</option>
                                    <option value="Inventory Correction" {{ old('reason') == 'Inventory Correction' ? 'selected' : '' }}>Inventory Correction</option>
                                </optgroup>
                                <optgroup label="Remove Stock">
                                    <option value="Damaged" {{ old('reason') == 'Damaged' ? 'selected' : '' }}>Damaged/Defective</option>
                                    <option value="Lost" {{ old('reason') == 'Lost' ? 'selected' : '' }}>Lost/Theft</option>
                                    <option value="Expired" {{ old('reason') == 'Expired' ? 'selected' : '' }}>Expired</option>
                                    <option value="Used" {{ old('reason') == 'Used' ? 'selected' : '' }}>Used for Operations</option>
                                    <option value="Inventory Correction" {{ old('reason') == 'Inventory Correction' ? 'selected' : '' }}>Inventory Correction</option>
                                </optgroup>
                            </select>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes (Optional)</label>
                            <textarea name="notes" id="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('employee.products') }}" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Submit Adjustment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Product Information</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column align-items-center text-center mb-3">
                        @if($product->image)
                            <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" class="rounded mb-2" style="max-width: 100px; max-height: 100px;">
                        @else
                            <div class="bg-light rounded d-flex align-items-center justify-content-center mb-2" style="width: 100px; height: 100px;">
                                <i class="bi bi-box-seam fs-1 text-secondary"></i>
                            </div>
                        @endif
                        <h5>{{ $product->name }}</h5>
                    </div>
                    
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Category:</span>
                            <span>{{ $product->category->name ?? 'N/A' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>SKU/Code:</span>
                            <span>{{ $product->code ?? 'N/A' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Current Stock:</span>
                            <span class="badge bg-primary rounded-pill">{{ $product->current_stock }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Min. Stock:</span>
                            <span class="badge bg-secondary rounded-pill">{{ $product->min_stock }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Status:</span>
                            @if($product->current_stock <= 0)
                                <span class="badge bg-danger">Out of Stock</span>
                            @elseif($product->current_stock <= $product->min_stock)
                                <span class="badge bg-warning text-dark">Low Stock</span>
                            @else
                                <span class="badge bg-success">In Stock</span>
                            @endif
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 