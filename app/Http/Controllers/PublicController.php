<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PublicController extends Controller
{
    /**
     * Show the public home page
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function home()
    {
        // Get featured products (active products with stock)
        $featuredProducts = Product::where('is_active', true)
            ->where('current_stock', '>', 0)
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();
            
        // Get product categories
        $categories = Category::whereHas('products', function($query) {
            $query->where('is_active', true)
                ->where('current_stock', '>', 0);
        })->limit(8)->get();
        
        // Get some stock statistics
        $totalProducts = Product::where('is_active', true)->count();
        $inStockProducts = Product::where('is_active', true)
            ->where('current_stock', '>', 0)
            ->count();
        
        return view('public.home', compact(
            'featuredProducts',
            'categories',
            'totalProducts',
            'inStockProducts'
        ));
    }

    /**
     * Show product details page
     *
     * @param int $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function productDetails($id)
    {
        // Find the product
        $product = Product::with('category')->findOrFail($id);
        
        // Get related products from the same category
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->where('current_stock', '>', 0)
            ->limit(4)
            ->get();
            
        return view('public.product-details', compact('product', 'relatedProducts'));
    }

    /**
     * Show the signup form
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function showSignupForm(Request $request)
    {
        $redirect = $request->input('redirect');
        return view('public.signup', compact('redirect'));
    }

    /**
     * Handle client registration
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        // Validate form input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Begin transaction
            \DB::beginTransaction();

            // Create user with client role
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'client', // Auto-assign client role
            ]);

            // Create customer record linked to user
            $customer = Customer::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'status' => 'active',
                'user_id' => $user->id,
            ]);

            \DB::commit();

            // Auto-login the new user
            Auth::login($user);
            
            // Check if there's a redirect URL
            if ($request->filled('redirect')) {
                return redirect($request->redirect)
                    ->with('success', 'Registration successful! Welcome to our store.');
            }

            return redirect()->route('client.dashboard')
                ->with('success', 'Registration successful! Welcome to your dashboard.');

        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()
                ->with('error', 'Registration failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show all products page
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function allProducts(Request $request)
    {
        $query = Product::where('is_active', true)
            ->where('current_stock', '>', 0);
            
        // Handle search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('code', 'like', '%' . $search . '%');
            });
        }
        
        // Get products with pagination
        $products = $query->orderBy('created_at', 'desc')
            ->paginate(12)
            ->appends($request->all());
            
        // Get all categories for the filter
        $categories = Category::all();
        
        return view('public.all-products', compact('products', 'categories'));
    }
    
    /**
     * Show products by category
     *
     * @param int $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function categoryProducts($id, Request $request)
    {
        // Find the category
        $category = Category::findOrFail($id);
        
        // Query builder
        $query = Product::where('category_id', $id)
            ->where('is_active', true)
            ->where('current_stock', '>', 0);
            
        // Handle search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('code', 'like', '%' . $search . '%');
            });
        }
        
        // Get products with pagination
        $products = $query->orderBy('created_at', 'desc')
            ->paginate(12)
            ->appends($request->all());
            
        // Get all categories for the filter
        $categories = Category::all();
        
        return view('public.category-products', compact('products', 'category', 'categories'));
    }
} 