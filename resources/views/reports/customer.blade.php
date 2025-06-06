@extends('layouts.app')

@section('title', 'Customer Report')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Customer Report</h5>
                    <div>
                        <a href="{{ route('reports.download', $report->id) }}" class="btn btn-sm btn-success">
                            <i class="fas fa-download me-1"></i> Download PDF
                        </a>
                        <a href="{{ route('reports.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Reports
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Report Information</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="30%"><strong>Report Period:</strong></td>
                                    <td>{{ $start_date->format('M d, Y') }} - {{ $end_date->format('M d, Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Generated By:</strong></td>
                                    <td>{{ auth()->user()->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Generated On:</strong></td>
                                    <td>{{ now()->format('M d, Y H:i') }}</td>
                                </tr>
                                @if($report)
                                <tr>
                                    <td><strong>Report ID:</strong></td>
                                    <td>{{ $report->id }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Customer Summary</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="50%"><strong>Total Customers:</strong></td>
                                    <td>{{ $total_customers }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Total Sales:</strong></td>
                                    <td>${{ number_format($total_sales, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Total Due:</strong></td>
                                    <td>${{ number_format($total_due, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Avg. Purchase per Customer:</strong></td>
                                    <td>${{ number_format($average_purchase, 2) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h6>Customer List</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Total Orders</th>
                                            <th>Total Amount</th>
                                            <th>Total Due</th>
                                            <th>Last Purchase</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($customers as $customer)
                                        <tr>
                                            <td>{{ $customer->id }}</td>
                                            <td>{{ $customer->name }}</td>
                                            <td>{{ $customer->email ?? 'N/A' }}</td>
                                            <td>{{ $customer->phone ?? 'N/A' }}</td>
                                            <td class="text-end">{{ $customer->sales_count ?? 0 }}</td>
                                            <td class="text-end">${{ number_format($customer->total_sales ?? 0, 2) }}</td>
                                            <td class="text-end">${{ number_format($customer->total_due ?? 0, 2) }}</td>
                                            <td>{{ $customer->last_purchase_date ? $customer->last_purchase_date->format('M d, Y') : 'N/A' }}</td>
                                            <td>
                                                <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    @if(isset($top_customers) && $top_customers->count() > 0)
                    <div class="row">
                        <div class="col-md-12">
                            <h6>Top Customers</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Total Orders</th>
                                            <th>Total Amount</th>
                                            <th>% of Sales</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($top_customers as $customer)
                                        <tr>
                                            <td>{{ $customer->name }}</td>
                                            <td>{{ $customer->email ?? 'N/A' }}</td>
                                            <td>{{ $customer->phone ?? 'N/A' }}</td>
                                            <td class="text-end">{{ $customer->sales_count ?? 0 }}</td>
                                            <td class="text-end">${{ number_format($customer->total_sales ?? 0, 2) }}</td>
                                            <td class="text-end">{{ number_format($customer->percentage_of_sales ?? 0, 2) }}%</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 