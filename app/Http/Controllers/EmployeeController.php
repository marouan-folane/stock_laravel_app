<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the employee dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Get counts for statistics
        $totalProducts = Product::where('is_active', true)->count();
        $lowStockProducts = Product::where('is_active', true)
            ->whereRaw('current_stock <= min_stock')
            ->count();
        $totalCustomers = Customer::where('status', 'active')->count();
        
        // Get recent sales
        $recentSales = Sale::with('customer')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        // Get low stock products
        $lowStockProductsList = Product::where('is_active', true)
            ->whereRaw('current_stock <= min_stock')
            ->orderBy('current_stock', 'asc')
            ->limit(5)
            ->get();
            
        // Get pending orders count
        $pendingOrdersCount = Sale::where('status', 'pending')->count();
            
        return view('employee.dashboard', compact(
            'totalProducts',
            'lowStockProducts',
            'totalCustomers',
            'recentSales',
            'lowStockProductsList',
            'pendingOrdersCount'
        ));
    }
    
    /**
     * Show the list of products.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function products(Request $request)
    {
        $query = Product::with('category');
        
        // Filter by search term
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        
        // Filter by stock status
        if ($request->filled('stock')) {
            switch ($request->stock) {
                case 'low':
                    $query->whereRaw('current_stock <= min_stock AND current_stock > 0');
                    break;
                case 'out':
                    $query->where('current_stock', '<=', 0);
                    break;
                case 'in':
                    $query->whereRaw('current_stock > min_stock');
                    break;
            }
        }
        
        $products = $query->orderBy('name')->paginate(10)->withQueryString();
            
        return view('employee.products', compact('products'));
    }
    
    /**
     * Show the list of customers.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function customers(Request $request)
    {
        $query = Customer::query();
        
        // Filter by search term
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $customers = $query->orderBy('name')
            ->paginate(10)
            ->withQueryString();
            
        return view('employee.customers', compact('customers'))->with('ref', 'employee');
    }
    
    /**
     * Show employee sales history.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function sales(Request $request)
    {
        $query = Sale::with('customer')
            ->where('user_id', Auth::id());

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by client name or email
        if ($request->filled('client_search')) {
            $search = $request->client_search;
            $query->whereHas('customer', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
            
        $sales = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();
            
        return view('employee.sales', compact('sales'));
    }
    
    /**
     * Show pending orders for processing.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function pendingOrders()
    {
        $pendingOrders = Sale::with('customer', 'items.product')
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->paginate(10);
            
        return view('employee.pending-orders', compact('pendingOrders'));
    }
    
    /**
     * Show details of a specific order.
     *
     * @param int $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function orderDetails($id)
    {
        $order = Sale::with(['customer', 'items.product'])
            ->findOrFail($id);
            
        return view('employee.order-details', compact('order'));
    }
    
    /**
     * Process a pending order (mark as completed).
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processOrder($id, Request $request)
    {
        $order = Sale::findOrFail($id);
        
        // Can only process pending orders
        if ($order->status !== 'pending') {
            return redirect()->route('employee.pending-orders')
                ->with('error', 'Only pending orders can be processed.');
        }
        
        try {
            DB::beginTransaction();
            
            $order->status = 'completed';
            // Keep processed_by for when the column gets added
            $order->processed_by = Auth::id();
            // Also update user_id as a temporary solution
            $order->user_id = Auth::id();
            $order->save();
            
            DB::commit();
            
            return redirect()->route('employee.pending-orders')
                ->with('success', 'Order #' . $order->invoice_number . ' has been processed successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('employee.pending-orders')
                ->with('error', 'Error processing order: ' . $e->getMessage());
        }
    }

    /**
     * Show the stock adjustment form for a product.
     *
     * @param int $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function adjustStock($id)
    {
        $product = Product::with('category')->findOrFail($id);
        return view('employee.adjust-stock', compact('product'));
    }
    
    /**
     * Process the stock adjustment.
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStock($id, Request $request)
    {
        $request->validate([
            'quantity' => 'required|numeric|min:1',
            'adjustment_type' => 'required|in:add,subtract',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        $product = Product::findOrFail($id);
        
        // Save old stock for comparison
        $oldStock = $product->current_stock;
        
        try {
            DB::beginTransaction();
            
            $isAddition = $request->adjustment_type === 'add';
            $adjustmentType = $isAddition ? 'addition' : 'removal';
            $newStock = $isAddition 
                ? $product->current_stock + $request->quantity 
                : $product->current_stock - $request->quantity;
                
            // Check if removal would result in negative stock
            if (!$isAddition && $newStock < 0) {
                return back()->withErrors(['quantity' => 'Cannot remove more than current stock.'])->withInput();
            }
            
            // Create stock adjustment record
            $adjustment = \App\Models\StockAdjustment::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'type' => $adjustmentType,
                'quantity' => $request->quantity,
                'previous_stock' => $product->current_stock,
                'new_stock' => $newStock,
                'reference' => $request->reason,
                'notes' => $request->notes,
            ]);
            
            // Update product stock
            $product->current_stock = $newStock;
            $product->save();
            
            DB::commit();
            
            $action = $isAddition ? 'added to' : 'removed from';
            return redirect()->route('employee.products')
                ->with('success', "Successfully {$action} product stock: {$request->quantity} units of {$product->name}");
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error adjusting stock: ' . $e->getMessage())->withInput();
        }
    }
} 