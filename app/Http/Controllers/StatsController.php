<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatsController extends Controller
{
    /**
     * Display statistics dashboard for admins and employees
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // General statistics
        $stats = [
            'totalProducts' => Product::count(),
            'activeProducts' => Product::where('is_active', true)->count(),
            'lowStockProducts' => Product::where('current_stock', '<=', 10)->where('is_active', true)->count(),
            'totalSales' => Sale::count(),
            'totalCustomers' => Customer::count(),
            'totalCategories' => Category::count(),
            'totalSuppliers' => Supplier::count(),
        ];
        
        // Sales statistics for the past 30 days
        $thirtyDaysAgo = Carbon::now()->subDays(30);
        $stats['monthlySales'] = Sale::where('created_at', '>=', $thirtyDaysAgo)->count();
        $stats['monthlyRevenue'] = Sale::where('created_at', '>=', $thirtyDaysAgo)->sum('total_amount');
        
        // Sales data for chart - last 7 days
        $salesToday = Sale::whereDate('created_at', Carbon::today())->count();
        $salesYesterday = Sale::whereDate('created_at', Carbon::yesterday())->count();
        $salesLast7Days = Sale::where('created_at', '>=', Carbon::now()->subDays(7))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();
            
        // Revenue data for chart - last 7 days
        $revenueLast7Days = Sale::where('created_at', '>=', Carbon::now()->subDays(7))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('sum(total_amount) as revenue'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->pluck('revenue', 'date')
            ->toArray();
                
        // Top 5 best selling products
        $topProducts = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->select('products.name', DB::raw('SUM(sale_items.quantity) as total_sold'))
            ->groupBy('products.name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();
            
        // Top 5 customers
        $topCustomers = DB::table('sales')
            ->join('customers', 'sales.customer_id', '=', 'customers.id')
            ->select('customers.name', DB::raw('COUNT(sales.id) as total_orders'), DB::raw('SUM(sales.total_amount) as total_spent'))
            ->groupBy('customers.name')
            ->orderByDesc('total_spent')
            ->limit(5)
            ->get();
            
        // Category distribution
        $categoryDistribution = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('count(*) as count'))
            ->groupBy('categories.name')
            ->orderByDesc('count')
            ->get();
            
        // Monthly sales trend for the past 12 months
        $monthlySalesTrend = Sale::where('created_at', '>=', Carbon::now()->subMonths(12))
            ->select(DB::raw('MONTH(created_at) as month'), DB::raw('YEAR(created_at) as year'), DB::raw('count(*) as count'), DB::raw('sum(total_amount) as revenue'))
            ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                $monthName = Carbon::createFromDate($item->year, $item->month, 1)->format('M Y');
                return [
                    'month' => $monthName,
                    'count' => $item->count,
                    'revenue' => $item->revenue
                ];
            });
            
        // Get inventory value
        $inventoryValue = Product::sum(DB::raw('current_stock * selling_price'));
        $stats['inventoryValue'] = $inventoryValue;
        
        // Return the view with all statistics
        return view('stats.index', compact(
            'stats', 
            'salesLast7Days', 
            'revenueLast7Days', 
            'topProducts', 
            'topCustomers', 
            'categoryDistribution',
            'monthlySalesTrend'
        ));
    }
} 