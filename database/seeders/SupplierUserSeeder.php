<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SupplierUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create user account
        $user = User::create([
            'name' => 'Test Supplier',
            'email' => 'supplier@example.com',
            'password' => Hash::make('password'),
            'role' => 'supplier',
            'phone_number' => '1234567890',
        ]);

        // Create supplier record
   
} }