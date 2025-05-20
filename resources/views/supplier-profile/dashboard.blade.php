@extends('layouts.app')

@section('title', 'Supplier Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Supplier Dashboard</h5>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Movements</h5>
                                    <h2 class="card-text">{{ $stats['total_movements'] }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Quantity</h5>
                                    <h2 class="card-text">{{ $stats['total_quantity'] }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Products</h5>
                                    <h2 class="card-text">{{ $stats['total_products'] }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Profile Information</h5>
                            <a href="{{ route('supplier.profile.edit') }}" class="btn btn-sm btn-primary">Edit Profile</a>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Name:</strong> {{ $supplier->name }}</p>
                                    <p><strong>Email:</strong> {{ $supplier->email }}</p>
                                    <p><strong>Phone:</strong> {{ $supplier->phone }}</p>
                                    <p><strong>Contact Person:</strong> {{ $supplier->contact_person }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Address:</strong> {{ $supplier->address }}</p>
                                    <p><strong>City:</strong> {{ $supplier->city }}</p>
                                    <p><strong>State/Province:</strong> {{ $supplier->state ?: 'N/A' }}</p>
                                    <p><strong>Country:</strong> {{ $supplier->country }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Recent Stock Movements</h5>
                            <a href="{{ route('supplier.movements') }}" class="btn btn-sm btn-primary">View All Movements</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Product</th>
                                            <th>Type</th>
                                            <th>Quantity</th>
                                            <th>Reference</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentMovements as $movement)
                                            <tr>
                                                <td>{{ $movement->created_at->format('Y-m-d H:i') }}</td>
                                                <td>{{ $movement->product->name }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $movement->type == 'in' ? 'success' : 'danger' }}">
                                                        {{ $movement->type == 'in' ? 'In' : 'Out' }}
                                                    </span>
                                                </td>
                                                <td>{{ $movement->quantity }}</td>
                                                <td>{{ $movement->reference_no ?: 'N/A' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">No recent movements found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 