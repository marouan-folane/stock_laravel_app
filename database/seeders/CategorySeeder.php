<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            [
                'name' => 'Electronics',
                'description' => 'Electronic devices and gadgets',
                'icon' => 'bi-laptop'
            ],
            [
                'name' => 'Office Supplies',
                'description' => 'Stationery and office equipment',
                'icon' => 'bi-pencil'
            ],
            [
                'name' => 'Furniture',
                'description' => 'Office and home furniture',
                'icon' => 'bi-lamp'
            ],
            [
                'name' => 'Food & Beverages',
                'description' => 'Consumable products',
                'icon' => 'bi-cup-hot'
            ],
            [
                'name' => 'Healthcare',
                'description' => 'Health and medical supplies',
                'icon' => 'bi-heart-pulse'
            ],
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
                'description' => $category['description'],
                'icon' => $category['icon'],
                'is_active' => true
            ]);
        }
    }
} 