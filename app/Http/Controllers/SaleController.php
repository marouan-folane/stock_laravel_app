<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class SaleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin,employee')->except('store');
    }

    /**
     * Display a listing of the sales.
     */
    public function index(Request $request)
    {
        $query = Sale::with(['customer', 'user']);

        // Filter by customer
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by date range
        if ($request->filled('date_range')) {
            $dates = explode(' - ', $request->date_range);
            if (count($dates) == 2) {
                $startDate = Carbon::createFromFormat('m/d/Y', $dates[0])->startOfDay();
                $endDate = Carbon::createFromFormat('m/d/Y', $dates[1])->endOfDay();
                $query->whereBetween('date', [$startDate, $endDate]);
            }
        }

        $sales = $query->latest()->paginate(10);
        $customers = Customer::active()->orderBy('name')->get();

        return view('sales.index', compact('sales', 'customers'));
    }

    /**
     * Show the form for creating a new sale.
     */
    public function create()
    {
        $customers = Customer::active()->orderBy('name')->get();
        $products = Product::active()->where('current_stock', '>', 0)->get();
        $invoiceNumber = Sale::generateInvoiceNumber();

        return view('sales.create', compact('customers', 'products', 'invoiceNumber'));
    }

    /**
     * Store a newly created sale in storage.
     */
    public function store(Request $request)
    {
        // Check if this is a client order from the cart
        if ($request->has('products') && Auth::user()->role === 'client') {
            return $this->processClientOrder($request);
        }
        
        $request->validate([
            'invoice_number' => 'required|string|unique:sales',
            'customer_id' => 'required|exists:customers,id',
            'date' => 'required|date',
            'status' => 'required|in:pending,completed,canceled',
            'product_id' => 'required|array',
            'product_id.*' => 'required|exists:products,id',
            'quantity' => 'required|array',
            'quantity.*' => 'required|integer|min:1',
            'price' => 'required|array',
            'price.*' => 'required|numeric|min:0',
            'discount' => 'nullable|array',
            'discount.*' => 'nullable|numeric|min:0',
            'tax' => 'nullable|array',
            'tax.*' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'required_with:paid_amount',
            'notes' => 'nullable|string',
        ]);

        // Check stock availability
        foreach ($request->product_id as $key => $productId) {
            $product = Product::findOrFail($productId);
            $quantity = $request->quantity[$key];
            
            if ($product->current_stock < $quantity) {
                return back()->withInput()->withErrors([
                    'quantity.'.$key => "Not enough stock. Available: {$product->current_stock}"
                ]);
            }
        }

        try {
            DB::beginTransaction();

            // Calculate payment status
            $paymentStatus = 'unpaid';
            if ($request->paid_amount > 0) {
                if ($request->paid_amount >= $request->total_amount) {
                    $paymentStatus = 'paid';
                } else {
                    $paymentStatus = 'partial';
                }
            }

            // Create sale
            $sale = Sale::create([
                'invoice_number' => $request->invoice_number,
                'customer_id' => $request->customer_id,
                'user_id' => auth()->id(),
                'date' => $request->date,
                'total_amount' => $request->total_amount,
                'paid_amount' => $request->paid_amount ?? 0,
                'discount' => $request->discount_input ?? 0,
                'tax' => $request->tax_input ?? 0,
                'status' => $request->status,
                'payment_status' => $paymentStatus,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
            ]);

            // Create sale items and update stock
            foreach ($request->product_id as $key => $productId) {
                $product = Product::findOrFail($productId);
                $quantity = $request->quantity[$key];
                $price = $request->price[$key];
                $discount = $request->discount[$key] ?? 0;
                $tax = $request->tax[$key] ?? 0;
                $total = $request->total[$key];

                // Create sale item
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price' => $price,
                    'discount' => $discount,
                    'tax' => $tax,
                    'total' => $total,
                ]);

                // Only deduct stock if the sale is completed
                if ($request->status == 'completed') {
                    // Update product stock
                    $product->current_stock -= $quantity;
                    $product->save();

                    // Record stock movement
                    StockMovement::create([
                        'product_id' => $productId,
                        'user_id' => auth()->id(),
                        'reference_id' => $sale->id,
                        'reference_type' => 'sale',
                        'quantity' => -$quantity,
                        'type' => 'out',
                        'date' => now(),
                        'notes' => 'Sale #' . $sale->invoice_number,
                    ]);
                }
            }

            // Create payment record if payment was made
            if ($request->paid_amount > 0 && $request->payment_method) {
                Payment::create([
                    'sale_id' => $sale->id,
                    'amount' => $request->paid_amount,
                    'method' => $request->payment_method,
                    'date' => now(),
                    'user_id' => auth()->id(),
                    'notes' => 'Initial payment for sale #' . $sale->invoice_number,
                ]);
            }

            DB::commit();

            return redirect()->route('sales.show', $sale)
                ->with('success', 'Sale created successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Sale creation failed: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Error creating sale. ' . $e->getMessage());
        }
    }
    
    /**
     * Process client order from cart
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function processClientOrder(Request $request)
    {
        $request->validate([
            'products' => 'required|array',
            'payment_method' => 'required|string|in:cash,credit_card,bank_transfer',
            'notes' => 'nullable|string',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Get client's customer record or create one if it doesn't exist
            $user = Auth::user();
            $customer = Customer::where('email', $user->email)->first();
            
            if (!$customer) {
                // Create a customer record based on the user account
                $customer = Customer::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $request->phone ?? '',
                    'address' => $request->address ?? '',
                    'city' => $request->city ?? '',
                    'state' => $request->state ?? '',
                    'postal_code' => $request->postal_code ?? '',
                    'country' => $request->country ?? '',
                    'status' => 'active',
                    'notes' => 'Auto-created from client user account',
                ]);
            }
            
            // Generate invoice number
            $invoiceNumber = 'INV-' . date('Ymd') . '-' . rand(1000, 9999);
            
            // Calculate total amount
            $totalAmount = 0;
            $taxRate = 0.1; // 10% tax
            
            // Verify stock availability first
            foreach ($request->products as $productData) {
                $product = Product::findOrFail($productData['id']);
                $requestedQty = $productData['quantity'];
                
                // Check stock
                if ($product->current_stock < $requestedQty) {
                    DB::rollBack();
                    return redirect()->route('client.cart')
                        ->with('error', "Not enough stock for {$product->name}. Available: {$product->current_stock}");
                }
                
                $totalAmount += $product->selling_price * $requestedQty;
            }
            
            // Add tax
            $taxAmount = $totalAmount * $taxRate;
            $grandTotal = $totalAmount + $taxAmount;
            
            // Create sale
            $sale = Sale::create([
                'invoice_number' => $invoiceNumber,
                'customer_id' => $customer->id,
                'user_id' => Auth::id(),
                'processed_by' => null, // No employee processed it yet
                'date' => now(),
                'total_amount' => $grandTotal,
                'paid_amount' => 0, // Mark as unpaid initially
                'discount' => 0,
                'tax' => $taxAmount,
                'status' => 'pending', // Orders start as pending
                'payment_status' => 'unpaid',
                'payment_method' => $request->payment_method,
                'notes' => 'Online order: ' . ($request->notes ?? ''),
            ]);
            
            // Create sale items and deduct stock
            foreach ($request->products as $productData) {
                $product = Product::findOrFail($productData['id']);
                $quantity = $productData['quantity'];
                $price = $product->selling_price;
                $total = $price * $quantity;
                
                // Create sale item
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'discount' => 0,
                    'tax' => $price * $taxRate,
                    'total' => $total + ($total * $taxRate),
                ]);
                
                // Update product stock
                $product->current_stock -= $quantity;
                $product->save();
                
                // Record stock movement
                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => Auth::id(),
                    'reference_id' => $sale->id,
                    'reference_type' => 'sale',
                    'quantity' => -$quantity,
                    'type' => 'out',
                    'date' => now(),
                    'notes' => 'Client Order #' . $invoiceNumber,
                ]);
            }
            
            DB::commit();
            
            // Clear cart
            session()->forget('cart');
            
            return redirect()->route('client.orders.details', $sale->id)
                ->with('success', 'Order placed successfully! Your order number is ' . $invoiceNumber);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Client order failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Provide more specific error messages for common issues
            $errorMessage = 'Error creating order. ';
            if (strpos($e->getMessage(), 'current_stock') !== false) {
                $errorMessage .= 'Stock issue: ' . $e->getMessage();
            } elseif (strpos($e->getMessage(), 'customer_id') !== false) {
                $errorMessage .= 'Customer profile issue: ' . $e->getMessage();
            } else {
                $errorMessage .= $e->getMessage();
            }
            
            return redirect()->route('client.cart')
                ->with('error', $errorMessage);
        }
    }

    /**
     * Display the specified sale.
     */
    public function show(Sale $sale)
    {
        $sale->load(['customer', 'user', 'items.product', 'payments']);
        return view('sales.show', compact('sale'));
    }

    /**
     * Show the form for editing the specified sale.
     */
    public function edit(Sale $sale)
    {
        if ($sale->status == 'completed') {
            return redirect()->route('sales.index')
                ->with('error', 'Completed sales cannot be edited.');
        }

        $sale->load(['customer', 'items.product']);
        $customers = Customer::active()->orderBy('name')->get();
        $products = Product::active()->get();

        return view('sales.edit', compact('sale', 'customers', 'products'));
    }

    /**
     * Update the specified sale in storage.
     */
    public function update(Request $request, Sale $sale)
    {
        if ($sale->status == 'completed') {
            return redirect()->route('sales.index')
                ->with('error', 'Completed sales cannot be updated.');
        }

        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'date' => 'required|date',
            'status' => 'required|in:pending,completed,canceled',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Update sale
            $sale->update([
                'customer_id' => $request->customer_id,
                'date' => $request->date,
                'status' => $request->status,
                'notes' => $request->notes,
            ]);

            // If changing to completed status, handle stock
            if ($request->status == 'completed' && $sale->status != 'completed') {
                foreach ($sale->items as $item) {
                    $product = $item->product;
                    
                    // Check stock
                    if ($product->current_stock < $item->quantity) {
                        DB::rollBack();
                        return back()->withInput()->with('error', 
                            "Not enough stock for {$product->name}. Available: {$product->current_stock}");
                    }
                    
                    // Update stock
                    $product->current_stock -= $item->quantity;
                    $product->save();
                    
                    // Record stock movement
                    StockMovement::create([
                        'product_id' => $product->id,
                        'user_id' => auth()->id(),
                        'reference_id' => $sale->id,
                        'reference_type' => 'sale',
                        'quantity' => -$item->quantity,
                        'type' => 'out',
                        'date' => now(),
                        'notes' => 'Sale #' . $sale->invoice_number,
                    ]);
                }
            }
            
            // If changing from completed to another status, restore stock
            if ($sale->status == 'completed' && $request->status != 'completed') {
                foreach ($sale->items as $item) {
                    $product = $item->product;
                    
                    // Update stock
                    $product->current_stock += $item->quantity;
                    $product->save();
                    
                    // Record stock movement
                    StockMovement::create([
                        'product_id' => $product->id,
                        'user_id' => auth()->id(),
                        'reference_id' => $sale->id,
                        'reference_type' => 'sale',
                        'quantity' => $item->quantity,
                        'type' => 'in',
                        'date' => now(),
                        'notes' => 'Canceled Sale #' . $sale->invoice_number,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('sales.show', $sale)
                ->with('success', 'Sale updated successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Sale update failed: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Error updating sale. ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified sale from storage.
     */
    public function destroy(Sale $sale)
    {
        try {
            DB::beginTransaction();

            // If the sale was completed, restore stock
            if ($sale->status == 'completed') {
                foreach ($sale->items as $item) {
                    $product = $item->product;
                    
                    // Update stock
                    $product->current_stock += $item->quantity;
                    $product->save();
                    
                    // Record stock movement
                    StockMovement::create([
                        'product_id' => $product->id,
                        'user_id' => auth()->id(),
                        'reference_id' => $sale->id,
                        'reference_type' => 'sale',
                        'quantity' => $item->quantity,
                        'type' => 'in',
                        'date' => now(),
                        'notes' => 'Deleted Sale #' . $sale->invoice_number,
                    ]);
                }
            }

            // Delete sale items, payments, and the sale itself
            $sale->items()->delete();
            $sale->payments()->delete();
            $sale->delete();

            DB::commit();

            return redirect()->route('sales.index')
                ->with('success', 'Sale deleted successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Sale deletion failed: ' . $e->getMessage());
            return back()->with('error', 'Error deleting sale. ' . $e->getMessage());
        }
    }

    /**
     * Generate PDF for the sale.
     */
    public function generatePdf(Sale $sale)
    {
        $sale->load(['customer', 'user', 'items.product', 'payments']);
        
        // Use Laravel's PDF library
        $pdf = Pdf::loadView('sales.pdf', compact('sale'));
        
        return $pdf->stream('Invoice-'.$sale->invoice_number.'.pdf');
    }
} 