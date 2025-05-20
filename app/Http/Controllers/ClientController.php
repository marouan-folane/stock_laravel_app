<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\StockMovement;

class ClientController extends Controller
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
     * Show the client dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Get client's customer profile
        $customer = Customer::where('email', Auth::user()->email)->first();
        
        // Get client's order history
        $recentOrders = Sale::where('customer_id', $customer ? $customer->id : null)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        // Get total amount spent and outstanding balance
        $totalSpent = $customer ? $customer->getTotalPurchasesAttribute() : 0;
        $outstandingBalance = $customer ? $customer->getTotalDueAttribute() : 0;
            
        return view('client.dashboard', compact(
            'customer',
            'recentOrders',
            'totalSpent',
            'outstandingBalance'
        ));
    }
    
    /**
     * Show the list of available products for ordering.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function products(Request $request)
    {
        $query = Product::with('category')
            ->where('is_active', true)
            ->where('current_stock', '>', 0);
            
        // Apply category filter
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        
        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
            
        $products = $query->orderBy('name')
            ->paginate(10)
            ->withQueryString(); // Preserve query parameters in pagination links
            
        return view('client.products', compact('products'));
    }
    
    /**
     * Show the client's order history.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function orders(Request $request)
    {
        $customer = Customer::where('email', Auth::user()->email)->first();
        
        $query = Sale::where('customer_id', $customer ? $customer->id : null);
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        
        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Search by invoice number
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('invoice_number', 'like', "%{$search}%");
        }
        
        $orders = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString(); // Preserve query parameters in pagination links
            
        return view('client.orders', compact('orders'));
    }
    
    /**
     * Show order details.
     *
     * @param int $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function orderDetails($id)
    {
        $customer = Customer::where('email', Auth::user()->email)->first();
        
        $order = Sale::with('items.product')
            ->where('id', $id)
            ->where('customer_id', $customer ? $customer->id : null)
            ->firstOrFail();
            
        return view('client.order-details', compact('order'));
    }
    
    /**
     * Add a product to cart.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addToCart(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        
        // Initialize cart if it doesn't exist
        if (!session()->has('cart')) {
            session()->put('cart', []);
        }
        
        $cart = session()->get('cart');
        
        // Check if product is already in cart
        if (isset($cart[$product->id])) {
            // Update quantity if not exceeding available stock
            $newQuantity = $cart[$product->id]['quantity'] + $request->quantity;
            
            if ($newQuantity <= $product->current_stock) {
                $cart[$product->id]['quantity'] = $newQuantity;
            } else {
                return redirect()->back()->with('error', 'Not enough stock available.');
            }
        } else {
            // Add new product to cart
            if ($request->quantity <= $product->current_stock) {
                $cart[$product->id] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->selling_price,
                    'quantity' => $request->quantity,
                ];
            } else {
                return redirect()->back()->with('error', 'Not enough stock available.');
            }
        }
        
        session()->put('cart', $cart);
        
        return redirect()->back()->with('success', 'Product added to cart.');
    }
    
    /**
     * Show the cart contents.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function viewCart()
    {
        $cart = session()->get('cart', []);
        
        return view('client.cart', compact('cart'));
    }
    
    /**
     * Remove item from cart.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeFromCart($id)
    {
        $cart = session()->get('cart', []);
        
        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
        }
        
        return redirect()->back()->with('success', 'Item removed from cart.');
    }

    /**
     * Cancel a pending order.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancelOrder($id)
    {
        // Get client's customer profile
        $customer = Customer::where('email', Auth::user()->email)->first();
        
        if (!$customer) {
            return redirect()->back()->with('error', 'Customer profile not found.');
        }
        
        // Find the order, ensuring it belongs to this customer
        $order = Sale::where('id', $id)
            ->where('customer_id', $customer->id)
            ->where('status', 'pending') // Only pending orders can be canceled
            ->first();
            
        if (!$order) {
            return redirect()->back()->with('error', 'Order not found or cannot be canceled.');
        }
        
        try {
            DB::beginTransaction();
            
            // Update order status
            $order->status = 'canceled';
            $order->save();
            
            // Restore product stock quantities
            foreach ($order->items as $item) {
                $product = $item->product;
                
                // Only restore stock if it was previously deducted
                if ($product) {
                    $product->current_stock += $item->quantity;
                    $product->save();
                    
                    // Record stock movement
                    StockMovement::create([
                        'product_id' => $product->id,
                        'user_id' => Auth::id(),
                        'reference_id' => $order->id,
                        'reference_type' => 'sale_canceled',
                        'quantity' => $item->quantity,
                        'type' => 'in',
                        'date' => now(),
                        'notes' => 'Order canceled: ' . $order->invoice_number,
                    ]);
                }
            }
            
            DB::commit();
            
            return redirect()->route('client.orders.details', $order->id)
                ->with('success', 'Order has been canceled successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Order cancellation failed: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error canceling order: ' . $e->getMessage());
        }
    }
} 