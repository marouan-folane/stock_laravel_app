<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\User;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a customer record for the client user
        $clientUser = User::where('role', 'client')->where('email', 'client@example.com')->first();
        
        if ($clientUser) {
            Customer::create([
                'name' => $clientUser->name,
                'email' => $clientUser->email,
                'phone' => '1234567890',
                'address' => '123 Client St',
                'city' => 'New York',
                'state' => 'NY',
                'postal_code' => '10001',
                'country' => 'USA',
                'contact_person' => $clientUser->name,
                'tax_number' => 'TAX-CLIENT-001',
                'status' => 'active',
                'notes' => 'Client user account',
            ]);
        }
        
        // Other customers
        Customer::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'address' => '123 Main St',
            'city' => 'New York',
            'state' => 'NY',
            'postal_code' => '10001',
            'country' => 'USA',
            'contact_person' => 'Jane Doe',
            'tax_number' => 'TAX123456',
            'status' => 'active',
            'notes' => 'Test customer',
        ]);
        
        Customer::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'phone' => '0987654321',
            'address' => '456 Oak Ave',
            'city' => 'Los Angeles',
            'state' => 'CA',
            'postal_code' => '90001',
            'country' => 'USA',
            'contact_person' => 'John Smith',
            'tax_number' => 'TAX654321',
            'status' => 'active',
            'notes' => 'VIP customer',
        ]);
    }
}
