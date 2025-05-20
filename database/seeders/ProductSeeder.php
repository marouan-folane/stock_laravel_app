<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use Carbon\Carbon;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get categories and supplier IDs
        $electronics = Category::where('name', 'Electronics')->first()->id;
        $officeSupplies = Category::where('name', 'Office Supplies')->first()->id;
        $furniture = Category::where('name', 'Furniture')->first()->id;
        $foodBeverage = Category::where('name', 'Food & Beverages')->first()->id;
        $healthcare = Category::where('name', 'Healthcare')->first()->id;
        
        $supplier = Supplier::first()->id;
        
        // Create products
        $products = [
            // Electronics
            [
                'name' => 'Laptop Dell XPS 13',
                'code' => 'ELEC-001',
                'description' => 'High-performance laptop with Intel Core i7 processor',
                'category_id' => $electronics,
                'supplier_id' => $supplier,
                'cost_price' => 899.99,
                'selling_price' => 1199.99,
                'current_stock' => 15,
                'min_stock' => 5,
                'max_stock' => 30,
                'expiry_date' => null,
                'unit' => 'piece',
            ],
            [
                'name' => 'Wireless Mouse',
                'code' => 'ELEC-002',
                'description' => 'Ergonomic wireless mouse with long battery life',
                'category_id' => $electronics,
                'supplier_id' => $supplier,
                'cost_price' => 15.99,
                'selling_price' => 24.99,
                'current_stock' => 45,
                'min_stock' => 10,
                'max_stock' => 100,
                'expiry_date' => null,
                'unit' => 'piece',
            ],
            [
                'name' => 'USB-C Hub',
                'code' => 'ELEC-003',
                'description' => '7-in-1 USB-C hub with HDMI, USB-A, and SD card reader',
                'category_id' => $electronics,
                'supplier_id' => $supplier,
                'cost_price' => 25.50,
                'selling_price' => 39.99,
                'current_stock' => 30,
                'min_stock' => 8,
                'max_stock' => 60,
                'expiry_date' => null,
                'unit' => 'piece',
            ],
            
            // Office Supplies
            [
                'name' => 'Premium Notebook',
                'code' => 'OFF-001',
                'description' => 'A5 hardcover notebook with 200 pages',
                'category_id' => $officeSupplies,
                'supplier_id' => $supplier,
                'cost_price' => 4.50,
                'selling_price' => 9.99,
                'current_stock' => 120,
                'min_stock' => 20,
                'max_stock' => 200,
                'expiry_date' => null,
                'unit' => 'piece',
            ],
            [
                'name' => 'Gel Pens (Pack of 12)',
                'code' => 'OFF-002',
                'description' => 'Smooth writing gel pens in assorted colors',
                'category_id' => $officeSupplies,
                'supplier_id' => $supplier,
                'cost_price' => 5.25,
                'selling_price' => 12.50,
                'current_stock' => 75,
                'min_stock' => 15,
                'max_stock' => 150,
                'expiry_date' => null,
                'unit' => 'pack',
            ],
            [
                'name' => 'Paper Clips (Box of 100)',
                'code' => 'OFF-003',
                'description' => 'Small metal paper clips for document organization',
                'category_id' => $officeSupplies,
                'supplier_id' => $supplier,
                'cost_price' => 1.20,
                'selling_price' => 3.99,
                'current_stock' => 200,
                'min_stock' => 30,
                'max_stock' => 300,
                'expiry_date' => null,
                'unit' => 'box',
            ],
            
            // Furniture
            [
                'name' => 'Ergonomic Office Chair',
                'code' => 'FURN-001',
                'description' => 'Adjustable office chair with lumbar support',
                'category_id' => $furniture,
                'supplier_id' => $supplier,
                'cost_price' => 129.99,
                'selling_price' => 199.99,
                'current_stock' => 10,
                'min_stock' => 3,
                'max_stock' => 20,
                'expiry_date' => null,
                'unit' => 'piece',
            ],
            [
                'name' => 'Standing Desk',
                'code' => 'FURN-002',
                'description' => 'Adjustable height standing desk with electric motor',
                'category_id' => $furniture,
                'supplier_id' => $supplier,
                'cost_price' => 299.99,
                'selling_price' => 449.99,
                'current_stock' => 5,
                'min_stock' => 2,
                'max_stock' => 15,
                'expiry_date' => null,
                'unit' => 'piece',
            ],
            
            // Food & Beverages
            [
                'name' => 'Premium Coffee Beans (1kg)',
                'code' => 'FOOD-001',
                'description' => 'Arabica coffee beans, medium roast',
                'category_id' => $foodBeverage,
                'supplier_id' => $supplier,
                'cost_price' => 12.50,
                'selling_price' => 24.99,
                'current_stock' => 40,
                'min_stock' => 10,
                'max_stock' => 80,
                'expiry_date' => Carbon::now()->addMonths(6),
                'unit' => 'kg',
            ],
            [
                'name' => 'Energy Bars (Box of 12)',
                'code' => 'FOOD-002',
                'description' => 'Protein-rich energy bars for quick snacks',
                'category_id' => $foodBeverage,
                'supplier_id' => $supplier,
                'cost_price' => 8.75,
                'selling_price' => 15.99,
                'current_stock' => 60,
                'min_stock' => 12,
                'max_stock' => 100,
                'expiry_date' => Carbon::now()->addMonths(8),
                'unit' => 'box',
            ],
            
            // Healthcare
            [
                'name' => 'First Aid Kit',
                'code' => 'HEALTH-001',
                'description' => 'Complete first aid kit for emergency situations',
                'category_id' => $healthcare,
                'supplier_id' => $supplier,
                'cost_price' => 18.99,
                'selling_price' => 29.99,
                'current_stock' => 25,
                'min_stock' => 5,
                'max_stock' => 50,
                'expiry_date' => Carbon::now()->addYears(2),
                'unit' => 'kit',
            ],
            [
                'name' => 'Hand Sanitizer (500ml)',
                'code' => 'HEALTH-002',
                'description' => 'Alcohol-based hand sanitizer',
                'category_id' => $healthcare,
                'supplier_id' => $supplier,
                'cost_price' => 3.25,
                'selling_price' => 7.99,
                'current_stock' => 150,
                'min_stock' => 30,
                'max_stock' => 200,
                'expiry_date' => Carbon::now()->addYears(1),
                'unit' => 'bottle',
            ],
            [
                'name' => 'Face Masks (Pack of 50)',
                'code' => 'HEALTH-003',
                'description' => 'Disposable protective face masks',
                'category_id' => $healthcare,
                'supplier_id' => $supplier,
                'cost_price' => 9.99,
                'selling_price' => 19.99,
                'current_stock' => 100,
                'min_stock' => 20,
                'max_stock' => 300,
                'expiry_date' => Carbon::now()->addYears(3),
                'unit' => 'pack',
            ],
        ];
        
        // Create all products
        foreach ($products as $productData) {
            Product::create([
                'name' => $productData['name'],
                'code' => $productData['code'],
                'description' => $productData['description'],
                'category_id' => $productData['category_id'],
                'supplier_id' => $productData['supplier_id'],
                'cost_price' => $productData['cost_price'],
                'selling_price' => $productData['selling_price'],
                'current_stock' => $productData['current_stock'],
                'min_stock' => $productData['min_stock'],
                'max_stock' => $productData['max_stock'],
                'expiry_date' => $productData['expiry_date'],
                'unit' => $productData['unit'],
                'is_active' => true
            ]);
        }
    }
} 