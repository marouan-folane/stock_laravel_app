<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
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
     * Show the application dashboard based on user role.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();
        
        switch ($user->role) {
            case 'admin':
                return redirect()->route('dashboard');
            case 'employee':
                return redirect()->route('employee.dashboard');
            case 'client':
                return redirect()->route('client.dashboard');
            case 'supplier':
                return redirect()->route('supplier.profile');
            default:
                return view('home');
        }
    }
}
