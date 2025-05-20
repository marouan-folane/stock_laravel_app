<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SupplierProfileController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:supplier');
    }

    /**
     * Display the supplier profile dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        $supplier = Supplier::where('email', $user->email)->firstOrFail();
        
        // Get recent movements
        $recentMovements = StockMovement::with(['product', 'user'])
            ->where('supplier_id', $supplier->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        // Get movement statistics
        $stats = [
            'total_movements' => StockMovement::where('supplier_id', $supplier->id)->count(),
            'total_quantity' => StockMovement::where('supplier_id', $supplier->id)->sum('quantity'),
            'total_products' => $supplier->products()->count(),
        ];

        return view('supplier-profile.dashboard', compact('supplier', 'recentMovements', 'stats'));
    }

    /**
     * Display the supplier's movement history.
     *
     * @return \Illuminate\Http\Response
     */
    public function movements(Request $request)
    {
        $user = Auth::user();
        $supplier = Supplier::where('email', $user->email)->firstOrFail();
        
        $query = StockMovement::with(['product', 'user'])
            ->where('supplier_id', $supplier->id);
            
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
        
        // Filter by type if provided
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        // Get a clone of the query for statistics
        $statsQuery = clone $query;
        
        // Get statistics
        $stats = [
            'total_movements' => $statsQuery->count(),
            'total_quantity' => $statsQuery->sum('quantity'),
            'total_value' => $statsQuery->join('products', 'stock_movements.product_id', '=', 'products.id')
                ->select(DB::raw('SUM(stock_movements.quantity * products.cost_price) as total_value'))
                ->first()->total_value ?? 0,
        ];
        
        // Get paginated results
        $movements = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();
        
        // Get all products from this supplier for filtering
        $products = $supplier->products()->orderBy('name')->get();
        
        return view('supplier-profile.movements', compact('supplier', 'movements', 'products', 'stats'));
    }

    /**
     * Display the form to edit profile.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        $user = Auth::user();
        $supplier = Supplier::where('email', $user->email)->firstOrFail();
        
        return view('supplier-profile.edit', compact('supplier', 'user'));
    }

    /**
     * Update the supplier's profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $supplier = Supplier::where('email', $user->email)->firstOrFail();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'required|string|max:100',
        ]);
        
        $supplier->update($request->all());
        
        // Update user name if changed
        if ($user->name !== $request->name) {
            $user->name = $request->name;
            $user->save();
        }
        
        return redirect()->route('supplier.profile')->with('success', 'Profile updated successfully');
    }

    /**
     * Display the password change form.
     *
     * @return \Illuminate\Http\Response
     */
    public function changePassword()
    {
        return view('supplier-profile.change-password');
    }

    /**
     * Update the user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        $user = Auth::user();
        
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The provided password does not match your current password.']);
        }
        
        $user->password = Hash::make($request->password);
        $user->save();
        
        return redirect()->route('supplier.profile')->with('success', 'Password changed successfully');
    }
} 