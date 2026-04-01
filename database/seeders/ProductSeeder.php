<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::insert([
            ['name' => 'Beras Premium 5kg', 'price' => 75000],
            ['name' => 'Gula Pasir 1kg', 'price' => 15000],
            ['name' => 'Minyak Goreng 2L', 'price' => 32000],
            ['name' => 'Telur Ayam 1kg', 'price' => 28000],
        ]);
    }
}
