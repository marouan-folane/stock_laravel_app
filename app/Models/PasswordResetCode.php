<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PasswordResetCode extends Model
{
    use HasFactory;

    protected $table = 'password_resets_codes';

    protected $fillable = [
        'email',
        'code',
        'used',
        'expires_at',
    ];

    protected $casts = [
        'used' => 'boolean',
        'expires_at' => 'datetime',
    ];

    /**
     * Check if the code has expired.
     *
     * @return bool
     */
    public function isExpired()
    {
        return Carbon::now()->isAfter($this->expires_at);
    }

    /**
     * Generate a new password reset code.
     *
     * @param string $email
     * @return \App\Models\PasswordResetCode
     */
    public static function generateCode($email)
    {
        // First, invalidate any existing codes for this email
        self::where('email', $email)
            ->update(['used' => true]);

        // Generate a random 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Create and return a new code
        return self::create([
            'email' => $email,
            'code' => $code,
            'used' => false,
            'expires_at' => Carbon::now()->addMinutes(15), // Code expires in 15 minutes
        ]);
    }
}
