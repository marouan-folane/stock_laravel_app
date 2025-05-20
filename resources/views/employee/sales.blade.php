@extends('layouts.employee')

@section('title', 'My Sales')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>My Sales History</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('employee.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Sales History</li>
            </ol>
        </nav>
    </div>

    <!-- Search and Filter -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form action="{{ route('employee.sales') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="client_search" class="form-label">Client Name/Email</label>
                    <input type="text" name="client_search" id="client_search" class="form-control" placeholder="Search by name or email" value="{{ request('client_search') }}">
                </div>
                <div class="col-md-2">
                    <label for="date_from" class="form-label">Date From</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label">Date To</label>
                    <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('employee.sales') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Sales Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0">Sales List</h5>
        </div>
        <div class="card-body">
            @if($sales->isEmpty())
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    No sales records found. Please adjust your search criteria.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice #</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th class="text-end">Amount</th>
                                <th>Payment Method</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sales as $sale)
                                <tr>
                                    <td><strong>{{ $sale->invoice_number }}</strong></td>
                                    <td>{{ $sale->created_at->format('M d, Y g:i A') }}</td>
                                    <td>
                                        @if($sale->customer)
                                            {{ $sale->customer->name }}
                                            <br><small class="text-muted">{{ $sale->customer->email }}</small>
                                        @else
                                            <span class="text-muted">No customer linked</span>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $sale->items->count() }}</td>
                                    <td class="text-end">${{ number_format($sale->total_amount, 2) }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $sale->payment_method)) }}</td>
                                    <td>
                                        @if($sale->status == 'completed')
                                            <span class="badge bg-success">Completed</span>
                                        @elseif($sale->status == 'pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @elseif($sale->status == 'cancelled')
                                            <span class="badge bg-danger">Cancelled</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($sale->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('employee.order-details', $sale->id) }}" class="btn btn-sm btn-info text-white" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @if($sale->status == 'pending')
                                                <form action="{{ route('employee.process-order', $sale->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" title="Process Order" onclick="return confirm('Are you sure you want to mark this order as completed?')">
                                                        <i class="bi bi-check2"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <a href="{{ route('employee.sales.pdf', $sale->id) }}" class="btn btn-sm btn-secondary" title="Download Invoice" target="_blank">
                                                <i class="bi bi-file-pdf"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $sales->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
    
    <!-- Sales Summary Card -->
    <div class="row mt-4">
        <div class="col-md-6 offset-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Sales Summary</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>Total Sales:</th>
                            <td class="text-end">{{ $sales->total() }}</td>
                        </tr>
                        <tr>
                            <th>Total Amount:</th>
                            <td class="text-end">${{ number_format($sales->sum('total_amount'), 2) }}</td>
                        </tr>
                        <tr>
                            <th>Completed Sales:</th>
                            <td class="text-end">{{ $sales->where('status', 'completed')->count() }}</td>
                        </tr>
                        <tr>
                            <th>Pending Sales:</th>
                            <td class="text-end">{{ $sales->where('status', 'pending')->count() }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 