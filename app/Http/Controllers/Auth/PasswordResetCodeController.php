<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetCode;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PasswordResetCodeController extends Controller
{
    /**
     * Show the form to request a password reset code.
     *
     * @return \Illuminate\View\View
     */
    public function showRequestForm()
    {
        return view('auth.passwords.request-code');
    }

    /**
     * Send a password reset code to the user's email.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendResetCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $email = $request->email;
        $resetCode = PasswordResetCode::generateCode($email);

        // Always log the code for debugging purposes
        \Log::info('Password reset code for ' . $email . ': ' . $resetCode->code);

        try {
            // Try to send the verification code email
            Mail::send('emails.password-reset-code', ['code' => $resetCode->code], function ($message) use ($email) {
                $message->to($email)
                    ->subject('Your Password Reset Code');
            });
        } catch (\Exception $e) {
            \Log::error('Failed to send password reset email: ' . $e->getMessage());
            // Continue with the process even if email sending fails
        }

        return redirect()->route('password.code.verify', ['email' => $email])
            ->with('status', 'We have emailed your password reset code!');
    }

    /**
     * Show the form to verify the reset code.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function showVerifyForm(Request $request)
    {
        $email = $request->email;
        
        if (!$email) {
            return redirect()->route('password.code.request')
                ->withErrors(['email' => 'Email address is required.']);
        }

        return view('auth.passwords.verify-code', compact('email'));
    }

    /**
     * Verify the reset code.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string|size:6',
        ]);

        $email = $request->email;
        $code = $request->code;

        $resetCode = PasswordResetCode::where('email', $email)
            ->where('code', $code)
            ->where('used', false)
            ->first();

        if (!$resetCode || $resetCode->isExpired()) {
            return back()->withErrors(['code' => 'Invalid or expired verification code.']);
        }

        // Don't mark the code as used yet, we'll do that after password reset
        // Instead, we'll encrypt the ID and pass it as a token

        return redirect()->route('password.code.reset', [
            'email' => $email,
            'token' => encrypt($resetCode->id)
        ]);
    }

    /**
     * Show the form to reset the password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function showResetForm(Request $request)
    {
        $email = $request->email;
        $token = $request->token;

        if (!$email || !$token) {
            return redirect()->route('password.code.request')
                ->withErrors(['email' => 'Invalid password reset link.']);
        }

        return view('auth.passwords.reset-with-code', compact('email', 'token'));
    }

    /**
     * Reset the password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            // First log everything to debug
            \Log::info('Password reset attempt', [
                'email' => $request->email,
                'password_length' => strlen($request->password)
            ]);
            
            // Decrypt and validate token
            $resetCodeId = decrypt($request->token);
            $resetCode = PasswordResetCode::findOrFail($resetCodeId);

            if ($resetCode->email !== $request->email) {
                \Log::error('Password reset error: email mismatch', [
                    'reset_code_email' => $resetCode->email, 
                    'request_email' => $request->email
                ]);
                return back()->withErrors(['email' => 'Email address does not match the reset code.']);
            }
            
            // Check if the code was already used for a password reset
            if ($resetCode->used) {
                \Log::error('Password reset error: code already used');
                return back()->withErrors(['email' => 'This reset code has already been used.']);
            }
            
            if ($resetCode->isExpired()) {
                \Log::error('Password reset error: code expired', [
                    'expired_at' => $resetCode->expires_at
                ]);
                return back()->withErrors(['email' => 'This reset code has expired.']);
            }

            // Get user directly to modify the password
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                \Log::error('Password reset error: user not found');
                return back()->withErrors(['email' => 'User not found.']);
            }

            // Update password - directly to bypass the model's potential issues
            $success = DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'password' => Hash::make($request->password)
                ]);
            
            if (!$success) {
                \Log::error('Password reset error: failed to update password');
                return back()->withErrors(['email' => 'Failed to reset password. Please try again.']);
            }
            
            \Log::info('Password reset successful for user: ' . $user->email);

            // Now mark this specific reset code as used
            $resetCode->used = true;
            $resetCode->save();

            // Mark all other reset codes for this user as used as well
            PasswordResetCode::where('email', $request->email)
                ->where('id', '!=', $resetCode->id)
                ->update(['used' => true]);

            // Flash a message and redirect to login
            return redirect()->route('login')
                ->with('status', 'Your password has been reset successfully! You can now log in with your new password.');
        } catch (\Exception $e) {
            \Log::error('Password reset exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'An error occurred: ' . $e->getMessage()]);
        }
    }
}
