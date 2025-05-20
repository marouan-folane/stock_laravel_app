@extends('layouts.employee')

@section('title', 'Customers')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Customer Management</h1>
        <div>
            <a href="{{ route('customers.create') }}?ref=employee" class="btn btn-success">
                <i class="bi bi-plus-circle me-2"></i>Add New Customer
            </a>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('employee.customers') }}" method="GET" class="row g-3">
                <div class="col-md-6">
                    <label for="search" class="form-label">Search Customers</label>
                    <input type="text" name="search" id="search" class="form-control" placeholder="Search by name, email or phone..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Customers Table -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">Customers List</h5>
        </div>
        <div class="card-body">
            @if($customers->isEmpty())
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    No customers found. Please try different search criteria or add new customers.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Address</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customers as $customer)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <strong>{{ $customer->name }}</strong>
                                        @if($customer->contact_person)
                                            <br><small class="text-muted">Contact: {{ $customer->contact_person }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $customer->email }}</td>
                                    <td>{{ $customer->phone ?? 'N/A' }}</td>
                                    <td>
                                        @if($customer->address)
                                            {{ $customer->address }}
                                            @if($customer->city)
                                                , {{ $customer->city }}
                                            @endif
                                            @if($customer->state)
                                                , {{ $customer->state }}
                                            @endif
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($customer->status == 'active')
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('customers.show', $customer->id) }}?ref=employee" class="btn btn-sm btn-info text-white" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('customers.edit', $customer->id) }}?ref=employee" class="btn btn-sm btn-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
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
                    {{ $customers->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 