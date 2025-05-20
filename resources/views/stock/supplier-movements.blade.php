@extends('layouts.app')

@section('title', 'Supplier Stock Movements')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Supplier Stock Movements</h1>
</div>

<!-- Search and Filter Form -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Search & Filter</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('stock.supplier-movements') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="supplier_id" class="form-label">Supplier</label>
                <select class="form-select" id="supplier_id" name="supplier_id">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label for="product_id" class="form-label">Product</label>
                <select class="form-select" id="product_id" name="product_id">
                    <option value="">All Products</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label for="date_from" class="form-label">Date From</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
            </div>

            <div class="col-md-2">
                <label for="date_to" class="form-label">Date To</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
            </div>

            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>

            <div class="col-md-1 d-flex align-items-end">
                <a href="{{ route('stock.supplier-movements') }}" class="btn btn-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">All Supplier Stock Movements</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Product</th>
                        <th>Supplier</th>
                        <th>Quantity</th>
                        <th>Reference</th>
                        <th>Added By</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($adjustments as $adjustment)
                    <tr>
                        <td>{{ $adjustment->date ? \Carbon\Carbon::parse($adjustment->date)->format('M d, Y') : \Carbon\Carbon::parse($adjustment->created_at)->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('products.show', $adjustment->product_id) }}">
                                {{ $adjustment->product->name }}
                            </a>
                            <small class="d-block text-muted">{{ $adjustment->product->code }}</small>
                        </td>
                        <td>
                            <a href="{{ route('suppliers.show', $adjustment->supplier_id) }}">
                                {{ $adjustment->supplier->name }}
                            </a>
                        </td>
                        <td class="text-success font-weight-bold">+{{ $adjustment->quantity }}</td>
                        <td>{{ $adjustment->reference }}</td>
                        <td>{{ $adjustment->user ? $adjustment->user->name : 'System' }}</td>
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
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No supplier stock movements found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-4">
                {{ $adjustments->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Notes Modal -->
<div class="modal fade" id="notesModal" tabindex="-1" aria-labelledby="notesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notesModalLabel">Stock Movement Notes</h5>
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
        // Initialize select2 for dropdown menus if select2 is available
        if($.fn.select2) {
            $('#supplier_id, #product_id').select2({
                placeholder: 'Select an option',
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