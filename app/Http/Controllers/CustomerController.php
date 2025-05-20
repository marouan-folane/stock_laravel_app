<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;

class CustomerController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin,employee');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $customers = Customer::paginate(10);
        return view('customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        return view('customers.create')->with('ref', $request->ref);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);
         
        $customer = Customer::create($request->all());
        
        // Check if the request came from employee area
        if ($request->has('ref') && $request->ref === 'employee') {
            return redirect()->route('employee.customers')->with('success', 'Customer created successfully');
        }
        
        return redirect()->route('customers.index')->with('success', 'Customer created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        return view('customers.show', compact('customer'))->with('ref', $request->ref);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        return view('customers.edit', compact('customer'))->with('ref', $request->ref);
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
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email,'.$id,
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);
        
        $customer = Customer::findOrFail($id);
        $customer->update($request->all());

        // Check if the request came from employee area
        if ($request->has('ref') && $request->ref === 'employee') {
            return redirect()->route('employee.customers')->with('success', 'Customer updated successfully');
        }

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        
        // Check if the customer has any sales before deleting
        if ($customer->sales()->count() > 0) {
            if ($request->has('ref') && $request->ref === 'employee') {
                return redirect()->route('employee.customers')
                    ->with('error', 'Cannot delete customer because they have sales records associated with them.');
            }
            
            return redirect()->route('customers.index')
                ->with('error', 'Cannot delete customer because they have sales records associated with them.');
        }
        
        $customer->delete();
        
        // Check if the request came from employee area
        if ($request->has('ref') && $request->ref === 'employee') {
            return redirect()->route('employee.customers')->with('success', 'Customer deleted successfully');
        }
        
        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully');
    }
}
