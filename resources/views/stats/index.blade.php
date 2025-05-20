@extends('layouts.app')

@section('title', 'Statistics Dashboard')

@section('styles')
<style>
    .stat-card {
        transition: all 0.3s ease;
        border-radius: 10px;
        overflow: hidden;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .chart-container {
        position: relative;
        height: 300px;
        margin-bottom: 20px;
    }
    .top-item {
        transition: all 0.2s ease;
        padding: 12px;
        border-radius: 8px;
    }
    .top-item:hover {
        background-color: rgba(0,0,0,0.03);
    }
    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #000000;
    }
    .stat-label {
        color: #000000;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
    }
    .chart-title {
        font-weight: 600;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #eee;
        color: #000000;
    }
    /* Dark text for all elements */
    h1, h2, h3, h4, h5, h6, p, span, div, small, .text-muted {
        color: #000000 !important;
    }
    /* Exception for colored backgrounds */
    .bg-gradient-primary, 
    .bg-gradient-success, 
    .bg-gradient-info, 
    .bg-gradient-warning,
    .bg-primary,
    .bg-success,
    .bg-info,
    .bg-warning,
    .bg-danger,
    .badge {
        color: #ffffff !important;
    }
    /* Ensure white text on colored backgrounds */
    .bg-gradient-primary .stat-value,
    .bg-gradient-primary .stat-label,
    .bg-gradient-primary span,
    .bg-gradient-success .stat-value,
    .bg-gradient-success .stat-label,
    .bg-gradient-success span,
    .bg-gradient-info .stat-value,
    .bg-gradient-info .stat-label,
    .bg-gradient-info span,
    .bg-gradient-warning .stat-value,
    .bg-gradient-warning .stat-label,
    .bg-gradient-warning span {
        color: #ffffff !important;
        text-shadow: 0 1px 2px rgba(0,0,0,0.2);
    }
    /* Override any default text colors */
    .text-white-50 {
        color: #ffffff !important;
    }
    .card-header h5 {
        color: #000000 !important;
    }
    .list-group-item h6 {
        color: #000000 !important;
    }
    /* Chart text */
    canvas {
        color: #000000 !important;
    }
    /* Alert text */
    .alert {
        color: #000000 !important;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Statistics Dashboard</h1>
        <div>
            <button class="btn btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer"></i> Print Report
            </button>
            <button class="btn btn-outline-primary" id="refreshStats">
                <i class="bi bi-arrow-clockwise"></i> Refresh Data
            </button>
        </div>
    </div>

    <!-- Stats Overview Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0 shadow-sm bg-gradient-primary text-black ">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-value">{{ number_format($stats['totalSales']) }}</div>
                            <div class="stat-label">Total Sales</div>
                        </div>
                        <div class="rounded-circle bg-black bg-opacity-25 p-3">
                            <i class="bi bi-cart-check text-black fs-3"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-black-50">
                            {{ number_format($stats['monthlySales']) }} sales in last 30 days
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0 shadow-sm bg-gradient-success text-black">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-value">${{ number_format($stats['monthlyRevenue'], 2) }}</div>
                            <div class="stat-label">Monthly Revenue</div>
                        </div>
                        <div class="rounded-circle bg-black bg-opacity-25 p-3">
                            <i class="bi bi-cash-stack text-black fs-3"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-black-50">
                            Last 30 days revenue
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0 shadow-sm bg-gradient-info text-black">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-value">{{ number_format($stats['totalProducts']) }}</div>
                            <div class="stat-label">Total Products</div>
                        </div>
                        <div class="rounded-circle bg-black bg-opacity-25 p-3">
                            <i class="bi bi-box-seam text-black fs-3"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-black-50">
                            {{ number_format($stats['activeProducts']) }} active products
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0 shadow-sm bg-gradient-warning text-black">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-value">${{ number_format($stats['inventoryValue'], 2) }}</div>
                            <div class="stat-label">Inventory Value</div>
                        </div>
                        <div class="rounded-circle bg-black bg-opacity-25 p-3">
                            <i class="bi bi-currency-dollar text-black fs-3"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-black-50">
                            Based on current buying prices
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Sales & Revenue Trend (Last 7 Days)</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="salesRevenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Category Distribution</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="categoryDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Products & Customers -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Top Selling Products</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @forelse($topProducts as $index => $product)
                            <div class="list-group-item top-item p-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="d-flex align-items-center">
                                        <div class="badge bg-primary me-3">{{ $index + 1 }}</div>
                                        <h6 class="mb-0">{{ $product->name }}</h6>
                                    </div>
                                </div>
                                <div>
                                    <span class="badge bg-success">{{ number_format($product->total_sold) }} sold</span>
                                </div>
                            </div>
                        @empty
                            <div class="alert alert-info">No product sales data available.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Top Customers</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @forelse($topCustomers as $index => $customer)
                            <div class="list-group-item top-item p-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="d-flex align-items-center">
                                        <div class="badge bg-primary me-3">{{ $index + 1 }}</div>
                                        <h6 class="mb-0">{{ $customer->name }}</h6>
                                    </div>
                                    <small class="text-muted">{{ $customer->total_orders }} orders</small>
                                </div>
                                <div>
                                    <span class="badge bg-success">${{ number_format($customer->total_spent, 2) }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="alert alert-info">No customer data available.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Trends -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Monthly Sales & Revenue Trends (Last 12 Months)</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="monthlyTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Chart colors
        const colors = {
            primary: '#4e73df',
            success: '#1cc88a',
            info: '#36b9cc',
            warning: '#f6c23e',
            danger: '#e74a3b',
            secondary: '#858796',
            light: '#f8f9fc',
            dark: '#000000'  // Changed to black
        };
        
        // Chart text color options - make all labels black
        Chart.defaults.color = '#000000';
        
        // Sales and Revenue Chart - Last 7 Days
        const salesRevenueCtx = document.getElementById('salesRevenueChart').getContext('2d');
        const salesRevenueChart = new Chart(salesRevenueCtx, {
            type: 'bar',
            data: {
                labels: Object.keys({!! json_encode($salesLast7Days) !!}),
                datasets: [
                    {
                        label: 'Sales Count',
                        backgroundColor: colors.primary,
                        borderColor: colors.primary,
                        data: Object.values({!! json_encode($salesLast7Days) !!}),
                        order: 2
                    },
                    {
                        label: 'Revenue ($)',
                        backgroundColor: 'rgba(0,0,0,0)',
                        borderColor: colors.success,
                        data: Object.values({!! json_encode($revenueLast7Days) !!}),
                        type: 'line',
                        order: 1,
                        yAxisID: 'revenue'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Sales Count',
                            color: '#000000'
                        },
                        ticks: {
                            color: '#000000'
                        }
                    },
                    revenue: {
                        beginAtZero: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Revenue ($)',
                            color: '#000000'
                        },
                        ticks: {
                            color: '#000000'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#000000'
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            color: '#000000'
                        }
                    }
                }
            }
        });
        
        // Category Distribution Chart
        const categoryData = {!! json_encode($categoryDistribution) !!};
        const categoryNames = categoryData.map(item => item.name);
        const categoryCounts = categoryData.map(item => item.count);
        
        const categoryCtx = document.getElementById('categoryDistributionChart').getContext('2d');
        const categoryChart = new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: categoryNames,
                datasets: [{
                    data: categoryCounts,
                    backgroundColor: [
                        colors.primary,
                        colors.success,
                        colors.info,
                        colors.warning,
                        colors.danger,
                        colors.secondary
                    ],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#000000',
                            font: {
                                weight: 'bold'
                            }
                        }
                    }
                }
            }
        });
        
        // Monthly Trends Chart
        const monthlyData = {!! json_encode($monthlySalesTrend) !!};
        const months = monthlyData.map(item => item.month);
        const salesCounts = monthlyData.map(item => item.count);
        const revenues = monthlyData.map(item => item.revenue);
        
        const monthlyCtx = document.getElementById('monthlyTrendChart').getContext('2d');
        const monthlyChart = new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [
                    {
                        label: 'Sales Count',
                        borderColor: colors.primary,
                        backgroundColor: colors.primary + '20',
                        data: salesCounts,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Revenue ($)',
                        borderColor: colors.success,
                        backgroundColor: colors.success + '20',
                        data: revenues,
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'revenue'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Sales Count',
                            color: '#000000'
                        },
                        ticks: {
                            color: '#000000'
                        }
                    },
                    revenue: {
                        beginAtZero: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Revenue ($)',
                            color: '#000000'
                        },
                        ticks: {
                            color: '#000000'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#000000'
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            color: '#000000',
                            font: {
                                weight: 'bold'
                            }
                        }
                    }
                }
            }
        });
        
        // Refresh button
        document.getElementById('refreshStats').addEventListener('click', function() {
            window.location.reload();
        });
    });
</script>
@endsection 