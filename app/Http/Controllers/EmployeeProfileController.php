<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class EmployeeProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:employee,admin');
    }

    /**
     * Display the employee profile edit form.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        $user = Auth::user();
        return view('employee.profile.edit', compact('user'));
    }

    /**
     * Update the employee's profile.
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
            'phone_number' => 'nullable|string|max:20',
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
        
        $user->phone_number = $request->phone_number ?? $user->phone_number;
        $user->save();
        
        return back()->with('success', 'Profile updated successfully.');
    }
} 