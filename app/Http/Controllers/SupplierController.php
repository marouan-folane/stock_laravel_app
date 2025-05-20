<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $suppliers = Supplier::withCount('products')->paginate(10);
        return view('suppliers.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('suppliers.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $supplier = Supplier::create($request->all());
        return redirect()->route('suppliers.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $supplier = Supplier::with(['products.category'])->findOrFail($id);
        
        // Get recent stock movements (last 5 entries)
        $recentAdjustments = StockMovement::with(['product', 'user'])
            ->where('supplier_id', $id)
            ->where('type', 'in')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        return view('suppliers.show', compact('supplier', 'recentAdjustments'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $supplier = Supplier::find($id);
        return view('suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $supplier = Supplier::find($id);
        $supplier->update($request->all());
        return redirect()->route('suppliers.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $supplier = Supplier::find($id);
        $supplier->delete();
        return redirect()->route('suppliers.index');
    }
    
    /**
     * Display the order history for the specified supplier.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function orderHistory($id, Request $request)
    {
        $supplier = Supplier::findOrFail($id);
        
        $query = StockMovement::with(['product', 'user'])
            ->where('supplier_id', $id)
            ->where('type', 'in');
            
        // Filter by date range if provided
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Filter by product if provided
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        
        // Get a clone of the query for statistics
        $statsQuery = clone $query;
        
        // Get order statistics
        $stats = [
            'total_orders' => $statsQuery->count(),
            'total_quantity' => $statsQuery->sum('quantity'),
            'total_value' => $statsQuery->join('products', 'stock_movements.product_id', '=', 'products.id')
                ->select(DB::raw('SUM(stock_movements.quantity * products.cost_price) as total_value'))
                ->first()->total_value ?? 0,
        ];
        
        // Get paginated results
        $adjustments = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();
        
        // Get all products from this supplier for filtering
        $products = Product::where('supplier_id', $id)->orderBy('name')->get();
        
        return view('suppliers.order-history', compact('supplier', 'adjustments', 'products', 'stats'));
    }
}
