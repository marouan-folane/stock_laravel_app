<!-- Sidebar Toggle on small screens -->
<button class="btn btn-link d-lg-none position-fixed" id="mobile-sidebar-toggle" style="top: 10px; right: 10px; z-index: 1050;">
    <i class="fas fa-bars"></i>
</button>

<div class="bg-white sidebar shadow" id="sidebar-wrapper">
    <div class="sidebar-heading border-bottom bg-light d-flex justify-content-between align-items-center py-3 px-3">
        <a href="{{ route('dashboard') }}" class="text-decoration-none">
            <span class="fs-4 fw-bold text-primary">StockManager</span>
        </a>
        <button class="btn btn-sm btn-link d-md-none" id="sidebarToggle">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="list-group list-group-flush">
        <a href="{{ route('dashboard') }}" class="list-group-item list-group-item-action {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
        </a>
        <a href="{{ route('products.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('products.*') && !request()->routeIs('products.expiring') ? 'active' : '' }}">
            <i class="fas fa-box me-2"></i> Products
        </a>
        <a href="{{ route('products.expiring') }}" class="list-group-item list-group-item-action {{ request()->routeIs('products.expiring') ? 'active' : '' }}">
            <i class="fas fa-calendar-times me-2"></i> Expiring Products
            @php
                $expiringCount = \App\Models\Product::whereNotNull('expiry_date')
                    ->where('expiry_date', '>=', now())
                    ->where('expiry_date', '<=', now()->addDays(30))
                    ->where('current_stock', '>', 0)
                    ->count();
            @endphp
            @if($expiringCount > 0)
                <span class="badge bg-warning float-end">{{ $expiringCount }}</span>
            @endif
        </a>
        <a href="{{ route('categories.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('categories.*') ? 'active' : '' }}">
            <i class="fas fa-tags me-2"></i> Categories
        </a>
        <a href="{{ route('sensible-categories.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('sensible-categories.*') ? 'active' : '' }}">
            <i class="fas fa-bell-exclamation me-2"></i> Category Monitoring
        </a>
        <a href="{{ route('suppliers.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
            <i class="fas fa-truck me-2"></i> Suppliers
        </a>
        <a href="{{ route('customers.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('customers.*') ? 'active' : '' }}">
            <i class="fas fa-users me-2"></i> Customers
        </a>
        <a href="{{ route('sales.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('sales.*') ? 'active' : '' }}">
            <i class="fas fa-shopping-cart me-2"></i> Sales
        </a>
        {{-- <a href="{{ route('stock.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('stock.*') ? 'active' : '' }}">
            <i class="fas fa-warehouse me-2"></i> Stock Management
        </a> --}}
        
        <!-- Stock Management submenu -->
        <div class="list-group-item">
            <a href="#stockSubmenu" data-bs-toggle="collapse" aria-expanded="{{ request()->routeIs('stock.*') ? 'true' : 'false' }}" class="dropdown-toggle text-decoration-none text-dark">
                <i class="fas fa-warehouse me-2"></i> Stock Management
            </a>
            <ul class="collapse list-unstyled {{ request()->routeIs('stock.*') ? 'show' : '' }}" id="stockSubmenu">
                <li class="ms-3 mt-2">
                    <a href="{{ route('stock.index') }}" class="text-decoration-none {{ request()->routeIs('stock.index') ? 'text-primary' : 'text-dark' }}">
                        <i class="fas fa-cubes me-2"></i> Stock Overview
                    </a>
                </li>
                <li class="ms-3 mt-2">
                    <a href="{{ route('stock.supplier-movements') }}" class="text-decoration-none {{ request()->routeIs('stock.supplier-movements') ? 'text-primary' : 'text-dark' }}">
                        <i class="fas fa-truck-loading me-2"></i> Supplier Movements
                    </a>
                </li>
            </ul>
        </div>
        
        <a href="{{ route('reports.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <i class="fas fa-chart-bar me-2"></i> Reports
        </a>
        
        <a href="{{ route('stats.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('stats.*') ? 'active' : '' }}">
            <i class="fas fa-chart-line me-2"></i> Statistics Dashboard
        </a>
        
        <!-- Alerts Menu -->
        <a href="{{ route('alerts.index') }}" class="list-group-item list-group-item-action position-relative {{ request()->routeIs('alerts.*') ? 'active' : '' }}">
            <i class="fas fa-bell me-2"></i> Alerts
            @php
                $unreadAlertsCount = \App\Models\Alert::where('is_read', false)->count();
            @endphp
            @if($unreadAlertsCount > 0)
                <span class="position-absolute top-50 end-0 translate-middle badge rounded-pill bg-danger me-3">
                    {{ $unreadAlertsCount }}
                </span>
            @endif
        </a>
        
        @can('manage-users')
        <div class="sidebar-heading px-3 mt-2 mb-1 text-muted small">
            Administration
        </div>
        <a href="{{ route('users.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('users.*') ? 'active' : '' }}">
            <i class="fas fa-user-cog me-2"></i> User Management
        </a>
        <a href="{{ route('settings.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('settings.*') ? 'active' : '' }}">
            <i class="fas fa-cogs me-2"></i> Settings
        </a>
      
        @endcan
    </div>
</div> 