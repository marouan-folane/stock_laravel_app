<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callbackGoogle()
    {
        try {
            $google_user = Socialite::driver('google')->user();

            // 1. Cherche par google_id
            $user = User::where('google_id', $google_user->getId())->first();

            // 2. Sinon, cherche par email et lie le google_id
            if (!$user) {
                $user = User::where('email', $google_user->getEmail())->first();
                if ($user) {
                    $user->google_id = $google_user->getId();
                    $user->save();
                }
            }

            // 3. Sinon, crée un nouvel utilisateur
            if (!$user) {
                $user = User::create([
                    'name' => $google_user->getName(),
                    'email' => $google_user->getEmail(),
                    'role' => 'client',
                    'google_id' => $google_user->getId(),
                ]);
            }

            // Log the user in
            auth()->login($user);

            // Redirect to intended page
            return redirect()->route('client.dashboard');
        } catch (\Exception $e) {
            \Log::error('Google login error: ' . $e->getMessage());
            return redirect('/login')->with('error', 'Failed to login with Google: ' . $e->getMessage());
        }
    }
}
