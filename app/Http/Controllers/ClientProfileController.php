<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Customer;

class ClientProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:client');
    }

    /**
     * Display the client profile edit form.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        $user = Auth::user();
        $customer = Customer::where('email', $user->email)->first();
        
        return view('client.profile.edit', compact('user', 'customer'));
    }

    /**
     * Update the client's profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        // Basic validation rules
        $rules = [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
        ];
        
        // Only add password validation rules if the user is trying to change their password
        if ($request->filled('password')) {
            $rules['current_password'] = 'required|string';
            $rules['password'] = 'required|string|min:8|confirmed';
            
            // Validate with password rules
            $validated = $request->validate($rules);
            
            // Check if current password matches
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'The provided password does not match your current password.']);
            }
            
            // Update password
            $user->password = Hash::make($validated['password']);
        } else {
            // Validate without password rules
            $validated = $request->validate($rules);
        }
        
        // Only update fields if they were provided in the request
        if ($request->filled('name')) {
            $user->name = $validated['name'];
        }
        
        if ($request->filled('email')) {
            $user->email = $validated['email'];
        }
        
        $user->save();
        
        // Update or create customer record
        $customer = Customer::where('email', $user->email)->first();
        
        if ($customer) {
            // Update customer details
            $customer->name = $user->name;
            $customer->email = $user->email;
            $customer->phone = $request->phone ?? $customer->phone;
            $customer->address = $request->address ?? $customer->address;
            $customer->city = $request->city ?? $customer->city;
            $customer->state = $request->state ?? $customer->state;
            $customer->postal_code = $request->postal_code ?? $customer->postal_code;
            $customer->country = $request->country ?? $customer->country;
            $customer->save();
        } else {
            // Create new customer record
            Customer::create([
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $request->phone ?? '',
                'address' => $request->address ?? '',
                'city' => $request->city ?? '',
                'state' => $request->state ?? '',
                'postal_code' => $request->postal_code ?? '',
                'country' => $request->country ?? '',
                'status' => 'active',
                'notes' => 'Created from client profile',
            ]);
        }
        
        return back()->with('success', 'Profile updated successfully.');
    }
} 