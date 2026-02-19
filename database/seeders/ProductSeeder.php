<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = DB::table('mst_categories')
            ->select('id', 'name')
            ->get();

        $units = DB::table('mst_units')
            ->select('id', 'name')
            ->get()
            ->keyBy(fn ($row) => strtolower($row->name));

        foreach ($categories as $category) {
            $categoryName = strtolower($category->name);
            $sku = 'SKU-' . Str::upper(Str::substr($categoryName, 0, 3));

            $exists = DB::table('mst_products')
                ->where('category_id', $category->id)
                ->exists();

            if ($exists) {
                continue;
            }

            $unit = $units['cm'] ?? $units['pcs'] ?? null;
            if (!$unit) {
                continue;
            }

            $productId = DB::table('mst_products')->insertGetId([
                'sku' => $sku,
                'name' => 'Produk ' . ucfirst($category->name),
                'base_price' => 15000,
                'sale_price' => 20000,
                'length_cm' => $unit->name === 'CM' ? 100 : null,
                'width_cm' => $unit->name === 'CM' ? 50 : null,
                'unit_id' => $unit->id,
                'category_id' => $category->id,
                'description' => 'Produk contoh untuk kategori ' . $category->name . '.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $material = DB::table('mst_materials')
                ->where('category_id', $category->id)
                ->orderBy('name')
                ->first();

            if ($material) {
                DB::table('mst_product_materials')->insert([
                    'product_id' => $productId,
                    'material_id' => $material->id,
                    'quantity' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
