<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Report;
use App\Models\StockAdjustment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Display the reports dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get recent reports - last 10
        $recentReports = Report::with('user')
            ->latest()
            ->take(10)
            ->get();
            
        // Get categories and suppliers for filter dropdowns
        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
            
        return view('reports.index', compact('recentReports', 'categories', 'suppliers'));
    }

    /**
     * Generate a report based on the specified type.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function generate(Request $request)
    {
        $type = $request->input('type');
        $data = [];
        
        // Redirect to index if no type is specified
        if (!$type) {
            return redirect()->route('reports.index')
                ->with('error', 'Please select a report type.');
        }
        
        // Store report parameters for reuse
        $parameters = $request->except(['_token', 'pdf', 'csv']);
        
        // Create a new report record
        $report = Report::create([
            'user_id' => auth()->id(),
            'type' => $type,
            'parameters' => $parameters,
        ]);
        
        switch ($type) {
            case 'sales':
                $data = $this->generateSalesReport($request);
                $view = 'reports.sales';
                break;
                
            case 'inventory':
                $data = $this->generateInventoryReport($request);
                $view = 'reports.inventory';
                break;
                
            case 'purchase':
                $data = $this->generatePurchaseReport($request);
                $view = 'reports.purchase';
                break;
                
            case 'profit_loss':
                $data = $this->generateProfitLossReport($request);
                $view = 'reports.profit_loss';
                break;
                
            case 'customer':
                $data = $this->generateCustomerReport($request);
                $view = 'reports.customer';
                break;
                
            case 'product':
                $data = $this->generateProductReport($request);
                $view = 'reports.product';
                break;
                
            case 'expiry':
                $data = $this->generateExpiryReport($request);
                $view = 'reports.expiry';
                break;
                
            default:
                return redirect()->route('reports.index')
                    ->with('error', 'Invalid report type selected.');
        }
        
        $data['report'] = $report;
        
        // Check if PDF download was requested
        if ($request->has('pdf')) {
            return $this->download($report->id);
        }
        
        // Check if CSV download was requested
        if ($request->has('csv')) {
            return $this->downloadCsv($report->id);
        }
        
        return view($view, $data);
    }

    /**
     * Download a report as PDF.
     *
     * @param int|Request $reportIdOrRequest
     * @return \Illuminate\Http\Response
     */
    public function download($reportIdOrRequest = null)
    {
        // Handle the case when called from a POST route with no parameters
        if ($reportIdOrRequest === null && request()->has('report_id')) {
            $reportId = request()->input('report_id');
        } elseif ($reportIdOrRequest instanceof Request) {
            // Create a new report if the method is called with a Request object
            $request = $reportIdOrRequest;
            
            $type = $request->input('type');
            $parameters = $request->except(['_token', 'pdf']);
            
            $report = Report::create([
                'user_id' => auth()->id(),
                'type' => $type,
                'parameters' => $parameters,
            ]);
            
            $reportId = $report->id;
        } else {
            // Normal case - a report ID was passed directly
            $reportId = $reportIdOrRequest;
        }
        
        // Ensure we have a report ID by this point
        if (empty($reportId)) {
            return redirect()->route('reports.index')
                ->with('error', 'No report specified for download.');
        }
        
        $report = Report::findOrFail($reportId);
        $data = [];
        
        // Reconstruct data based on report type and parameters
        switch ($report->type) {
            case 'sales':
                $data = $this->generateSalesReport(new Request($report->parameters));
                $view = 'reports.sales_pdf';
                $title = 'Sales Report';
                break;
                
            case 'inventory':
                $data = $this->generateInventoryReport(new Request($report->parameters));
                $view = 'reports.inventory_pdf';
                $title = 'Inventory Report';
                break;
                
            case 'purchase':
                $data = $this->generatePurchaseReport(new Request($report->parameters));
                $view = 'reports.purchase_pdf';
                $title = 'Purchase Report';
                break;
                
            case 'profit_loss':
                $data = $this->generateProfitLossReport(new Request($report->parameters));
                $view = 'reports.profit_loss_pdf';
                $title = 'Profit & Loss Report';
                break;
                
            case 'customer':
                $data = $this->generateCustomerReport(new Request($report->parameters));
                $view = 'reports.customer_pdf';
                $title = 'Customer Report';
                break;
                
            case 'product':
                $data = $this->generateProductReport(new Request($report->parameters));
                $view = 'reports.product_pdf';
                $title = 'Product Report';
                break;
                
            case 'expiry':
                $data = $this->generateExpiryReport(new Request($report->parameters));
                $view = 'reports.expiry_pdf';
                $title = 'Product Expiry Report';
                break;
                
            default:
                return redirect()->route('reports.index')
                    ->with('error', 'Invalid report type.');
        }
        
        $data['report'] = $report;
        
        $pdf = PDF::loadView($view, $data);
        return $pdf->download($title . '_' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Generate a sales report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    private function generateSalesReport(Request $request)
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->startOfMonth();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : Carbon::now()->endOfDay();
        
        $query = Sale::with(['customer', 'user', 'items.product'])
            ->whereBetween('date', [$startDate, $endDate]);
        
        // Filter by customer if provided
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }
        
        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        
        // Filter by payment status if provided
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }
        
        $sales = $query->latest()->get();
        
        // Calculate totals
        $totalAmount = $sales->sum('total_amount');
        $totalPaid = $sales->sum('paid_amount');
        $totalDue = $totalAmount - $totalPaid;
        $totalSales = $sales->count();
        
        // Calculate product counts
        $productSales = [];
        $salesByDate = [];
        
        foreach ($sales as $sale) {
            $saleDate = Carbon::parse($sale->date)->format('Y-m-d');
            
            if (!isset($salesByDate[$saleDate])) {
                $salesByDate[$saleDate] = [
                    'count' => 0,
                    'amount' => 0
                ];
            }
            
            $salesByDate[$saleDate]['count']++;
            $salesByDate[$saleDate]['amount'] += $sale->total_amount;
            
            foreach ($sale->items as $item) {
                $productId = $item->product_id;
                $productName = $item->product->name;
                
                if (!isset($productSales[$productId])) {
                    $productSales[$productId] = [
                        'name' => $productName,
                        'quantity' => 0,
                        'amount' => 0
                    ];
                }
                
                $productSales[$productId]['quantity'] += $item->quantity;
                $productSales[$productId]['amount'] += $item->total;
            }
        }
        
        // Sort products by amount
        uasort($productSales, function($a, $b) {
            return $b['amount'] <=> $a['amount'];
        });
        
        // Prepare date labels for chart
        $dateRange = [];
        $current = clone $startDate;
        while ($current <= $endDate) {
            $dateKey = $current->format('Y-m-d');
            $dateRange[$dateKey] = [
                'count' => $salesByDate[$dateKey]['count'] ?? 0,
                'amount' => $salesByDate[$dateKey]['amount'] ?? 0,
                'label' => $current->format('M d')
            ];
            $current->addDay();
        }
        
        return [
            'sales' => $sales,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_amount' => $totalAmount,
            'total_paid' => $totalPaid,
            'total_due' => $totalDue,
            'total_sales' => $totalSales,
            'product_sales' => $productSales,
            'sales_by_date' => $dateRange,
            'parameters' => $request->all()
        ];
    }

    /**
     * Generate an inventory report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    private function generateInventoryReport(Request $request)
    {
        $query = Product::with(['category', 'supplier']);
        
        // Filter by category if provided
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }
        
        // Filter by supplier if provided
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->input('supplier_id'));
        }
        
        // Filter by status if provided
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->input('is_active') == '1');
        }
        
        // Filter by stock level if provided
        if ($request->filled('stock_filter')) {
            switch ($request->input('stock_filter')) {
                case 'in_stock':
                    $query->where('current_stock', '>', 0);
                    break;
                case 'low_stock':
                    $query->whereRaw('current_stock <= min_stock AND current_stock > 0');
                    break;
                case 'out_of_stock':
                    $query->where('current_stock', 0);
                    break;
                case 'overstock':
                    $query->whereRaw('current_stock > max_stock AND max_stock > 0');
                    break;
            }
        }
        
        $products = $query->get();
        
        // Calculate totals
        $totalInventoryValue = $products->sum(function ($product) {
            return $product->current_stock * $product->cost_price;
        });
        
        $totalRetailValue = $products->sum(function ($product) {
            return $product->current_stock * $product->selling_price;
        });
        
        $potentialProfit = $totalRetailValue - $totalInventoryValue;
        
        return [
            'products' => $products,
            'total_inventory_value' => $totalInventoryValue,
            'total_retail_value' => $totalRetailValue,
            'potential_profit' => $potentialProfit,
            'total_products' => $products->count(),
            'low_stock_count' => $products->filter(function($product) {
                return $product->current_stock <= $product->min_stock && $product->current_stock > 0;
            })->count(),
            'out_of_stock_count' => $products->where('current_stock', 0)->count(),
            'parameters' => $request->all()
        ];
    }

    /**
     * Generate a purchase report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    private function generatePurchaseReport(Request $request)
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->startOfMonth();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : Carbon::now()->endOfDay();
        
        $query = Purchase::with(['supplier', 'user', 'items.product'])
            ->whereBetween('date', [$startDate, $endDate]);
        
        // Filter by supplier if provided
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->input('supplier_id'));
        }
        
        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        
        // Filter by payment status if provided
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }
        
        $purchases = $query->latest()->get();
        
        // Calculate totals
        $totalAmount = $purchases->sum('total_amount');
        $totalPaid = $purchases->sum('paid_amount');
        $totalDue = $totalAmount - $totalPaid;
        $totalPurchases = $purchases->count();
        
        return [
            'purchases' => $purchases,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_amount' => $totalAmount,
            'total_paid' => $totalPaid,
            'total_due' => $totalDue,
            'total_purchases' => $totalPurchases,
            'parameters' => $request->all()
        ];
    }

    /**
     * Generate a profit and loss report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    private function generateProfitLossReport(Request $request)
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->startOfMonth();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : Carbon::now()->endOfDay();
        
        // Get sales in the date range
        $sales = Sale::with(['items.product'])
            ->whereBetween('date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->get();
        
        // Calculate revenue (total sales)
        $totalRevenue = $sales->sum('total_amount');
        
        // Calculate cost of goods sold
        $costOfGoodsSold = 0;
        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $costOfGoodsSold += $item->quantity * $item->product->cost_price;
            }
        }
        
        // Calculate gross profit
        $grossProfit = $totalRevenue - $costOfGoodsSold;
        
        // Get expenses data 
        $expenses = [];
        
        // Get stock adjustments as expenses (stock removals)
        $stockAdjustments = StockAdjustment::where('type', 'removal')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('product')
            ->get();
            
        $stockLossValue = 0;
        foreach ($stockAdjustments as $adjustment) {
            $stockLossValue += $adjustment->quantity * $adjustment->product->cost_price;
        }
        
        if ($stockLossValue > 0) {
            $expenses['Stock Adjustments'] = $stockLossValue;
        }
        
        // Add other expense categories here as needed
        
        $totalExpenses = array_sum($expenses);
        
        // Calculate net profit
        $netProfit = $grossProfit - $totalExpenses;
        
        // Calculate profit margin
        $profitMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;
        
        // Calculate monthly data for charts
        $monthlyData = [];
        $current = clone $startDate;
        $current->startOfMonth();
        $end = clone $endDate;
        $end->startOfMonth();
        
        while ($current <= $end) {
            $monthKey = $current->format('Y-m');
            $monthLabel = $current->format('M Y');
            
            $monthStart = clone $current;
            $monthEnd = clone $current;
            $monthEnd->endOfMonth();
            
            // Monthly sales
            $monthlySales = Sale::whereBetween('date', [$monthStart, $monthEnd])
                ->where('status', 'completed')
                ->sum('total_amount');
                
            // Monthly cost calculation
            $monthlyCost = 0;
            Sale::with(['items.product'])
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->where('status', 'completed')
                ->get()
                ->each(function($sale) use (&$monthlyCost) {
                    foreach ($sale->items as $item) {
                        $monthlyCost += $item->quantity * $item->product->cost_price;
                    }
                });
            
            $monthlyProfit = $monthlySales - $monthlyCost;
            
            $monthlyData[$monthKey] = [
                'label' => $monthLabel,
                'revenue' => $monthlySales,
                'cost' => $monthlyCost,
                'profit' => $monthlyProfit
            ];
            
            $current->addMonth();
        }
        
        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_revenue' => $totalRevenue,
            'cost_of_goods_sold' => $costOfGoodsSold,
            'gross_profit' => $grossProfit,
            'expenses' => $expenses,
            'total_expenses' => $totalExpenses,
            'net_profit' => $netProfit,
            'profit_margin' => $profitMargin,
            'monthly_data' => $monthlyData,
            'parameters' => $request->all()
        ];
    }

    /**
     * Generate a customer report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    private function generateCustomerReport(Request $request)
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->startOfMonth();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : Carbon::now()->endOfDay();
        
        // Get all customers with their sales in the date range
        $customers = Customer::withCount(['sales' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            }])
            ->withSum(['sales' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            }], 'total_amount')
            ->withSum(['sales' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            }], 'paid_amount')
            ->get();
            
        // Calculate last purchase date for each customer
        foreach ($customers as $customer) {
            $lastSale = Sale::where('customer_id', $customer->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->latest('date')
                ->first();
                
            $customer->last_purchase_date = $lastSale ? $lastSale->date : null;
            $customer->total_sales = $customer->sales_sum_total_amount ?? 0;
            $customer->total_paid = $customer->sales_sum_paid_amount ?? 0;
            $customer->total_due = $customer->total_sales - $customer->total_paid;
        }
        
        // Calculate totals
        $totalSales = $customers->sum('total_sales');
        $totalDue = $customers->sum('total_due');
        $totalCustomers = $customers->count();
        $averagePurchase = $totalCustomers > 0 ? $totalSales / $totalCustomers : 0;
        
        // Get top customers by total sales
        $topCustomers = $customers->sortByDesc('total_sales')->take(10);
        
        // Calculate percentage of total sales for top customers
        foreach ($topCustomers as $customer) {
            $customer->percentage_of_sales = $totalSales > 0 
                ? ($customer->total_sales / $totalSales) * 100 
                : 0;
        }
        
        return [
            'customers' => $customers,
            'top_customers' => $topCustomers,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_sales' => $totalSales,
            'total_due' => $totalDue,
            'total_customers' => $totalCustomers,
            'average_purchase' => $averagePurchase,
            'parameters' => $request->all()
        ];
    }
    
    /**
     * Generate a product report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    private function generateProductReport(Request $request)
    {
        // Get all products with their categories and suppliers
        $query = Product::with(['category', 'supplier']);
        
        // Filter by category if provided
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }
        
        // Filter by supplier if provided
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->input('supplier_id'));
        }
        
        // Get products with sales count
        $products = $query->get();
        
        // Calculate sales count for each product
        foreach ($products as $product) {
            $salesItems = DB::table('sale_items')
                ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                ->where('sale_items.product_id', $product->id)
                ->where('sales.status', 'completed')
                ->select(DB::raw('SUM(sale_items.quantity) as total_sold'))
                ->first();
                
            $product->sales_count = $salesItems ? $salesItems->total_sold : 0;
        }
        
        // Calculate totals
        $totalProducts = $products->count();
        $totalInventoryValue = $products->sum(function ($product) {
            return $product->quantity * $product->cost;
        });
        
        // Get low stock and out of stock counts
        $lowStockCount = $products->filter(function($product) {
            return $product->quantity <= $product->alert_quantity && $product->quantity > 0;
        })->count();
        
        $outOfStockCount = $products->filter(function($product) {
            return $product->quantity <= 0;
        })->count();
        
        // Get top selling products
        $topSelling = $products->sortByDesc('sales_count')->take(10);
        
        return [
            'products' => $products,
            'top_selling' => $topSelling,
            'total_products' => $totalProducts,
            'total_inventory_value' => $totalInventoryValue,
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
            'parameters' => $request->all()
        ];
    }

    /**
     * Generate an expiry report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    private function generateExpiryReport(Request $request)
    {
        $days = $request->input('days', 30);
        
        $query = Product::with(['category', 'supplier'])
            ->whereNotNull('expiry_date')
            ->where('current_stock', '>', 0);
            
        // Filter by expiry range
        if ($request->filled('expiry_range')) {
            switch ($request->input('expiry_range')) {
                case 'expired':
                    $query->where('expiry_date', '<', now());
                    $title = "Expired Products Report";
                    break;
                case '7_days':
                    $query->where('expiry_date', '>=', now())
                          ->where('expiry_date', '<=', now()->addDays(7));
                    $title = "Products Expiring Within 7 Days";
                    break;
                case '30_days':
                    $query->where('expiry_date', '>=', now())
                          ->where('expiry_date', '<=', now()->addDays(30));
                    $title = "Products Expiring Within 30 Days";
                    break;
                case '90_days':
                    $query->where('expiry_date', '>=', now())
                          ->where('expiry_date', '<=', now()->addDays(90));
                    $title = "Products Expiring Within 90 Days";
                    break;
                case 'custom':
                    if ($request->filled('days')) {
                        $query->where('expiry_date', '>=', now())
                              ->where('expiry_date', '<=', now()->addDays($request->input('days')));
                        $title = "Products Expiring Within {$request->input('days')} Days";
                    }
                    break;
                default:
                    $title = "All Products With Expiry Dates";
                    break;
            }
        } else {
            // Default to 30 days
            $query->where('expiry_date', '>=', now())
                  ->where('expiry_date', '<=', now()->addDays($days));
            $title = "Products Expiring Within {$days} Days";
        }
        
        // Filter by category if provided
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
            $category = Category::find($request->input('category_id'));
            if ($category) {
                $title .= " - Category: {$category->name}";
            }
        }
        
        // Filter by supplier if provided
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->input('supplier_id'));
            $supplier = Supplier::find($request->input('supplier_id'));
            if ($supplier) {
                $title .= " - Supplier: {$supplier->name}";
            }
        }
        
        // Sort by expiry date by default
        $products = $query->orderBy('expiry_date', 'asc')->get();
        
        // Calculate total value of expiring products
        $totalValue = $products->sum(function ($product) {
            return $product->cost_price * $product->current_stock;
        });
        
        // Group products by expiry timeline
        $groupedProducts = [
            'expired' => $products->filter(function ($product) {
                return $product->expiry_date->isPast();
            }),
            'within_7_days' => $products->filter(function ($product) {
                return !$product->expiry_date->isPast() && $product->expiry_date->diffInDays(now()) <= 7;
            }),
            'within_30_days' => $products->filter(function ($product) {
                $diff = $product->expiry_date->diffInDays(now());
                return !$product->expiry_date->isPast() && $diff > 7 && $diff <= 30;
            }),
            'within_90_days' => $products->filter(function ($product) {
                $diff = $product->expiry_date->diffInDays(now());
                return !$product->expiry_date->isPast() && $diff > 30 && $diff <= 90;
            }),
            'beyond_90_days' => $products->filter(function ($product) {
                return !$product->expiry_date->isPast() && $product->expiry_date->diffInDays(now()) > 90;
            }),
        ];
        
        // Calculate value for each group
        $groupValues = [
            'expired' => $groupedProducts['expired']->sum(function ($product) {
                return $product->cost_price * $product->current_stock;
            }),
            'within_7_days' => $groupedProducts['within_7_days']->sum(function ($product) {
                return $product->cost_price * $product->current_stock;
            }),
            'within_30_days' => $groupedProducts['within_30_days']->sum(function ($product) {
                return $product->cost_price * $product->current_stock;
            }),
            'within_90_days' => $groupedProducts['within_90_days']->sum(function ($product) {
                return $product->cost_price * $product->current_stock;
            }),
            'beyond_90_days' => $groupedProducts['beyond_90_days']->sum(function ($product) {
                return $product->cost_price * $product->current_stock;
            }),
        ];
        
        // Calculate quantity for each group
        $groupQuantities = [
            'expired' => $groupedProducts['expired']->sum('current_stock'),
            'within_7_days' => $groupedProducts['within_7_days']->sum('current_stock'),
            'within_30_days' => $groupedProducts['within_30_days']->sum('current_stock'),
            'within_90_days' => $groupedProducts['within_90_days']->sum('current_stock'),
            'beyond_90_days' => $groupedProducts['beyond_90_days']->sum('current_stock'),
        ];
        
        return [
            'title' => $title,
            'products' => $products,
            'total_products' => $products->count(),
            'total_value' => $totalValue,
            'grouped_products' => $groupedProducts,
            'group_values' => $groupValues,
            'group_quantities' => $groupQuantities,
            'days' => $days,
            'current_date' => now(),
            'categories' => Category::all(),
            'suppliers' => Supplier::all(),
            'category_id' => $request->input('category_id'),
            'supplier_id' => $request->input('supplier_id'),
            'expiry_range' => $request->input('expiry_range', '30_days'),
        ];
    }

    /**
     * Download a report as CSV.
     *
     * @param int|Request $reportIdOrRequest
     * @return \Illuminate\Http\Response
     */
    public function downloadCsv($reportIdOrRequest = null)
    {
        // Handle the case when called from a POST route with no parameters
        if ($reportIdOrRequest === null && request()->has('report_id')) {
            $reportId = request()->input('report_id');
        } elseif ($reportIdOrRequest instanceof Request) {
            // Create a new report if the method is called with a Request object
            $request = $reportIdOrRequest;
            
            $type = $request->input('type');
            $parameters = $request->except(['_token', 'csv']);
            
            $report = Report::create([
                'user_id' => auth()->id(),
                'type' => $type,
                'parameters' => $parameters,
            ]);
            
            $reportId = $report->id;
        } else {
            // Normal case - a report ID was passed directly
            $reportId = $reportIdOrRequest;
        }
        
        // Ensure we have a report ID by this point
        if (empty($reportId)) {
            return redirect()->route('reports.index')
                ->with('error', 'No report specified for download.');
        }
        
        $report = Report::findOrFail($reportId);
        $data = [];
        
        // Generate CSV data based on report type
        switch ($report->type) {
            case 'sales':
                return $this->generateSalesCsv(new Request($report->parameters));
                
            case 'inventory':
                return $this->generateInventoryCsv(new Request($report->parameters));
                
            case 'purchase':
                return $this->generatePurchaseCsv(new Request($report->parameters));
                
            case 'profit_loss':
                return $this->generateProfitLossCsv(new Request($report->parameters));
                
            case 'customer':
                return $this->generateCustomerCsv(new Request($report->parameters));
                
            case 'product':
                return $this->generateProductCsv(new Request($report->parameters));
                
            case 'expiry':
                return $this->generateExpiryCsv(new Request($report->parameters));
                
            default:
                return redirect()->route('reports.index')
                    ->with('error', 'Invalid report type for CSV download.');
        }
    }
    
    /**
     * Generate Sales Report CSV
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    private function generateSalesCsv(Request $request)
    {
        // Get data using the same logic as the report
        $reportData = $this->generateSalesReport($request);
        $sales = $reportData['sales'];
        $filename = 'sales_report_' . date('Y-m-d') . '.csv';
        
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];
        
        $columns = [
            'Invoice #', 'Date', 'Customer', 'Status', 'Payment Method', 'Items', 'Total', 'Tax', 'Discount', 'Grand Total'
        ];
        
        $callback = function() use ($sales, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($sales as $sale) {
                fputcsv($file, [
                    $sale->invoice_number,
                    $sale->date->format('Y-m-d'),
                    $sale->customer ? $sale->customer->name : 'Walk-in Customer',
                    ucfirst($sale->status),
                    ucfirst($sale->payment_method),
                    $sale->items->count(),
                    number_format($sale->subtotal, 2),
                    number_format($sale->tax_amount, 2),
                    number_format($sale->discount_amount, 2),
                    number_format($sale->total_amount, 2),
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Generate Inventory Report CSV
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    private function generateInventoryCsv(Request $request)
    {
        // Get data using the same logic as the report
        $reportData = $this->generateInventoryReport($request);
        $products = $reportData['products'];
        $filename = 'inventory_report_' . date('Y-m-d') . '.csv';
        
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];
        
        $columns = [
            'Code', 'Name', 'Category', 'Current Stock', 'Min Stock', 'Unit Price', 'Value', 'Status'
        ];
        
        $callback = function() use ($products, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($products as $product) {
                fputcsv($file, [
                    $product->code,
                    $product->name,
                    $product->category ? $product->category->name : 'Uncategorized',
                    $product->current_stock,
                    $product->min_stock,
                    number_format($product->unit_price, 2),
                    number_format($product->current_stock * $product->unit_price, 2),
                    $product->is_active ? 'Active' : 'Inactive',
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Generate Purchase Report CSV
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    private function generatePurchaseCsv(Request $request)
    {
        // Get data using the same logic as the report
        $reportData = $this->generatePurchaseReport($request);
        $purchases = $reportData['purchases'];
        $filename = 'purchase_report_' . date('Y-m-d') . '.csv';
        
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];
        
        $columns = [
            'Reference #', 'Date', 'Supplier', 'Status', 'Payment Status', 'Items Count', 'Total Amount'
        ];
        
        $callback = function() use ($purchases, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($purchases as $purchase) {
                fputcsv($file, [
                    $purchase->reference_no,
                    $purchase->date->format('Y-m-d'),
                    $purchase->supplier ? $purchase->supplier->name : 'Unknown Supplier',
                    ucfirst($purchase->status),
                    ucfirst($purchase->payment_status),
                    $purchase->items ? $purchase->items->count() : 0,
                    number_format($purchase->total_amount, 2)
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Generate Profit Loss Report CSV
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    private function generateProfitLossCsv(Request $request)
    {
        // Get data using the same logic as the report
        $reportData = $this->generateProfitLossReport($request);
        $summary = $reportData['summary'];
        $sales = $reportData['sales'];
        $purchases = $reportData['purchases'];
        $expenses = $reportData['expenses'];
        $filename = 'profit_loss_report_' . date('Y-m-d') . '.csv';
        
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];
        
        $callback = function() use ($summary, $sales, $purchases, $expenses) {
            $file = fopen('php://output', 'w');
            
            // Summary Section
            fputcsv($file, ['PROFIT & LOSS SUMMARY']);
            fputcsv($file, ['']);
            fputcsv($file, ['Period', $summary['start_date'] . ' to ' . $summary['end_date']]);
            fputcsv($file, ['']);
            fputcsv($file, ['Revenue', number_format($summary['total_sales'], 2)]);
            fputcsv($file, ['Cost of Goods Sold', number_format($summary['total_cogs'], 2)]);
            fputcsv($file, ['Gross Profit', number_format($summary['gross_profit'], 2)]);
            fputcsv($file, ['Expenses', number_format($summary['total_expenses'], 2)]);
            fputcsv($file, ['Net Profit', number_format($summary['net_profit'], 2)]);
            fputcsv($file, ['Profit Margin', number_format($summary['profit_margin'], 2) . '%']);
            
            fputcsv($file, ['']);
            fputcsv($file, ['']);
            
            // Sales Section
            fputcsv($file, ['SALES DETAILS']);
            fputcsv($file, ['']);
            fputcsv($file, ['Invoice #', 'Date', 'Customer', 'Total', 'Cost', 'Profit']);
            
            foreach ($sales as $sale) {
                fputcsv($file, [
                    $sale->invoice_number,
                    $sale->date->format('Y-m-d'),
                    $sale->customer ? $sale->customer->name : 'Walk-in Customer',
                    number_format($sale->total_amount, 2),
                    number_format($sale->total_cost, 2),
                    number_format($sale->total_amount - $sale->total_cost, 2)
                ]);
            }
            
            fputcsv($file, ['']);
            fputcsv($file, ['']);
            
            // Purchases Section
            fputcsv($file, ['PURCHASE DETAILS']);
            fputcsv($file, ['']);
            fputcsv($file, ['Reference #', 'Date', 'Supplier', 'Total Amount']);
            
            foreach ($purchases as $purchase) {
                fputcsv($file, [
                    $purchase->reference_no,
                    $purchase->date->format('Y-m-d'),
                    $purchase->supplier ? $purchase->supplier->name : 'Unknown Supplier',
                    number_format($purchase->total_amount, 2)
                ]);
            }
            
            // Expenses Section if available
            if (count($expenses) > 0) {
                fputcsv($file, ['']);
                fputcsv($file, ['']);
                fputcsv($file, ['EXPENSE DETAILS']);
                fputcsv($file, ['']);
                fputcsv($file, ['Date', 'Category', 'Amount', 'Notes']);
                
                foreach ($expenses as $expense) {
                    fputcsv($file, [
                        $expense->date->format('Y-m-d'),
                        $expense->category,
                        number_format($expense->amount, 2),
                        $expense->notes
                    ]);
                }
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Generate Customer Report CSV
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    private function generateCustomerCsv(Request $request)
    {
        // Get data using the same logic as the report
        $reportData = $this->generateCustomerReport($request);
        $customers = $reportData['customers'];
        $filename = 'customer_report_' . date('Y-m-d') . '.csv';
        
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];
        
        $columns = [
            'ID', 'Name', 'Email', 'Phone', 'Address', 'Total Sales', 'Total Amount', 'Last Purchase Date'
        ];
        
        $callback = function() use ($customers, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($customers as $customer) {
                fputcsv($file, [
                    $customer->id,
                    $customer->name,
                    $customer->email,
                    $customer->phone ?? 'N/A',
                    $customer->full_address ?? 'N/A',
                    $customer->sales_count,
                    number_format($customer->total_sales, 2),
                    $customer->last_purchase ? $customer->last_purchase->format('Y-m-d') : 'N/A'
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Generate Product Report CSV
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    private function generateProductCsv(Request $request)
    {
        // Get data using the same logic as the report
        $reportData = $this->generateProductReport($request);
        $products = $reportData['products'];
        $filename = 'product_report_' . date('Y-m-d') . '.csv';
        
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];
        
        $columns = [
            'Code', 'Name', 'Category', 'Total Sales', 'Quantity Sold', 'Revenue', 'Cost', 'Profit', 'Margin'
        ];
        
        $callback = function() use ($products, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($products as $product) {
                fputcsv($file, [
                    $product->code,
                    $product->name,
                    $product->category ? $product->category->name : 'Uncategorized',
                    $product->sales_count,
                    $product->quantity_sold,
                    number_format($product->total_revenue, 2),
                    number_format($product->total_cost, 2),
                    number_format($product->total_profit, 2),
                    number_format($product->profit_margin, 2) . '%'
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Generate Expiry Report CSV
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    private function generateExpiryCsv(Request $request)
    {
        // Get data using the same logic as the report
        $reportData = $this->generateExpiryReport($request);
        $products = $reportData['products'];
        $filename = 'expiry_report_' . date('Y-m-d') . '.csv';
        
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];
        
        $columns = [
            'Code', 'Name', 'Batch Number', 'Expiry Date', 'Days Remaining', 'Quantity', 'Status'
        ];
        
        $callback = function() use ($products, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($products as $product) {
                $expiryDate = $product->expiry_date ? Carbon::parse($product->expiry_date) : null;
                $daysRemaining = $expiryDate ? $expiryDate->diffInDays(Carbon::now()) : null;
                $status = 'N/A';
                
                if ($expiryDate) {
                    if ($expiryDate->isPast()) {
                        $status = 'Expired';
                    } elseif ($daysRemaining <= 30) {
                        $status = 'Critical';
                    } elseif ($daysRemaining <= 90) {
                        $status = 'Warning';
                    } else {
                        $status = 'Good';
                    }
                }
                
                fputcsv($file, [
                    $product->code,
                    $product->name,
                    $product->batch_number ?? 'N/A',
                    $expiryDate ? $expiryDate->format('Y-m-d') : 'N/A',
                    $daysRemaining ?? 'N/A',
                    $product->current_stock,
                    $status
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
} 