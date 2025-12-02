<?php

namespace Database\Seeders;

use App\Traits\SecureNumeric;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductDemoSeeder extends Seeder
{
    use SecureNumeric;

    public function run(): void
    {
        $categoryId = DB::table('categories')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'name' => 'Mosquito Nets',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $productId = DB::table('products')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'name' => 'Mosquito Net',
            'category_id' => $categoryId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $variants = [
            [
                'name' => '4x6 Purple',
                'size' => '4x6',
                'color' => 'Purple',
                'sku' => 'NET-4X6-PURPLE',
                'cost' => 15.00,
                'selling_price' => 25.00,
                'initial_stock' => 20,
            ],
            [
                'name' => '4x6 Blue',
                'size' => '4x6',
                'color' => 'Blue',
                'sku' => 'NET-4X6-BLUE',
                'cost' => 15.00,
                'selling_price' => 25.00,
                'initial_stock' => 20,
            ],
            [
                'name' => '5x6 Purple',
                'size' => '5x6',
                'color' => 'Purple',
                'sku' => 'NET-5X6-PURPLE',
                'cost' => 17.00,
                'selling_price' => 28.00,
                'initial_stock' => 18,
            ],
            [
                'name' => '6x6 Green',
                'size' => '6x6',
                'color' => 'Green',
                'sku' => 'NET-6X6-GREEN',
                'cost' => 19.00,
                'selling_price' => 30.00,
                'initial_stock' => 15,
            ],
        ];

        foreach ($variants as $variant) {
            DB::table('product_variants')->insert([
                'uuid' => (string) Str::uuid(),
                'product_id' => $productId,
                'name' => $variant['name'],
                'size' => $variant['size'],
                'color' => $variant['color'],
                'sku' => $variant['sku'],
                'cost' => $this->encodeSecureNumeric($variant['cost']),
                'selling_price' => $this->encodeSecureNumeric($variant['selling_price']),
                'initial_stock' => $this->encodeSecureNumeric($variant['initial_stock']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
