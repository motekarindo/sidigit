<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Material;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categoryMap = Category::query()
            ->select('id', 'name')
            ->get()
            ->keyBy(fn ($row) => strtolower(trim($row->name)));

        $unitMap = Unit::query()
            ->select('id', 'name')
            ->get()
            ->keyBy(fn ($row) => strtolower(trim($row->name)));

        $materialMap = Material::query()
            ->select('id', 'name')
            ->get()
            ->keyBy(fn ($row) => strtolower(trim($row->name)));

        $products = [
            [
                'sku' => 'SKU-IND-001',
                'name' => 'Banner Indoor',
                'product_type' => 'goods',
                'category' => 'Indoor',
                'unit' => 'CM',
                'base_price' => 15000,
                'sale_price' => 20000,
                'length_cm' => 100,
                'width_cm' => 50,
                'description' => 'Produk contoh untuk kategori Indoor.',
                'materials' => [
                    ['name' => 'Albatros', 'qty' => 1],
                    ['name' => 'Backlite', 'qty' => 1],
                ],
            ],
            [
                'sku' => 'SKU-OUT-001',
                'name' => 'Banner',
                'product_type' => 'goods',
                'category' => 'Outdoor',
                'unit' => 'CM',
                'base_price' => 18000,
                'sale_price' => 25000,
                'length_cm' => 100,
                'width_cm' => 50,
                'description' => 'Produk contoh untuk kategori Outdoor.',
                'materials' => [
                    ['name' => 'Flexy Cina 240 GSM', 'qty' => 1],
                    ['name' => 'Flexy Cina 280 GSM', 'qty' => 1],
                    ['name' => 'Flexy Korea 380 GSM', 'qty' => 1],
                ],
            ],
            [
                'sku' => 'SKU-MERCH-001',
                'name' => 'Ganci',
                'product_type' => 'goods',
                'category' => 'Merchandise',
                'unit' => 'PCS',
                'base_price' => 5000,
                'sale_price' => 7500,
                'length_cm' => 0,
                'width_cm' => 0,
                'description' => 'Produk contoh untuk kategori Merchandise.',
                'materials' => [
                    ['name' => 'Gantungan Kunci Bulat 2.5', 'qty' => 1],
                    ['name' => 'Gantungan Kunci Bulat 3.2', 'qty' => 1],
                    ['name' => 'Gantungan Kunci Bulat 4.4', 'qty' => 1],
                    ['name' => 'Gantungan Kunci Bulat 5.8', 'qty' => 1],
                ],
            ],
            [
                'sku' => 'SKU-MERCH-002',
                'name' => 'TUmbler',
                'product_type' => 'goods',
                'category' => 'Merchandise',
                'unit' => 'PCS',
                'base_price' => 5000,
                'sale_price' => 7500,
                'length_cm' => 0,
                'width_cm' => 0,
                'description' => 'Produk contoh untuk kategori Merchandise.',
                'materials' => [
                    ['name' => 'Tumbler Stainless 330ML', 'qty' => 1],
                    ['name' => 'Tumbler Stainless 500ML', 'qty' => 1],
                ],
            ],
            [
                'sku' => 'SKU-STAT-001',
                'name' => 'Stempel',
                'product_type' => 'goods',
                'category' => 'Stationery',
                'unit' => 'PCS',
                'base_price' => 7000,
                'sale_price' => 10000,
                'length_cm' => 0,
                'width_cm' => 0,
                'description' => 'Produk contoh untuk kategori Stationery.',
                'materials' => [
                    ['name' => 'Stempel Oval 45MM', 'qty' => 1],
                    ['name' => 'Stempel Oval 51MM', 'qty' => 1],
                    ['name' => 'Stempel Lingkaran 35MM', 'qty' => 1],
                    ['name' => 'Stempel Lingkaran 40MM', 'qty' => 1],
                    ['name' => 'Stempel Lingkaran 45MM', 'qty' => 1],
                    ['name' => 'Stempel Lingkaran 51MM', 'qty' => 1],
                    ['name' => 'Stempel Persegi 26MM', 'qty' => 1],
                    ['name' => 'Stempel Persegi 36MM', 'qty' => 1],
                    ['name' => 'Stempel Persegi Panjang 17x55 MM', 'qty' => 1],
                    ['name' => 'Stempel Persegi Panjang 17x67 MM', 'qty' => 1],
                    ['name' => 'Stempel Persegi Panjang 22x55 MM', 'qty' => 1],
                    ['name' => 'Stempel Persegi Panjang 22x67 MM', 'qty' => 1],
                ],
            ],
            [
                'sku' => 'SKU-STAT-002',
                'name' => 'Kartu Nama',
                'product_type' => 'goods',
                'category' => 'Stationery',
                'unit' => 'PCS',
                'base_price' => 7000,
                'sale_price' => 10000,
                'length_cm' => 0,
                'width_cm' => 0,
                'description' => 'Produk contoh untuk kategori Stationery.',
                'materials' => [
                    ['name' => 'Art Paper 120 GSM', 'qty' => 1],
                    ['name' => 'Art Paper 150 GSM', 'qty' => 1],
                    ['name' => 'Art Carton 210 GSM', 'qty' => 1],
                    ['name' => 'Art Carton 230 GSM', 'qty' => 1],
                    ['name' => 'Art Carton 250 GSM', 'qty' => 1],
                    ['name' => 'Art Carton 310 GSM', 'qty' => 1],
                ],
            ],
            [
                'sku' => 'SKU-A3P-001',
                'name' => 'Print A3+',
                'product_type' => 'goods',
                'category' => 'Mesin A3+',
                'unit' => 'Lembar',
                'base_price' => 500,
                'sale_price' => 2500,
                'length_cm' => 0,
                'width_cm' => 0,
                'description' => 'Produk contoh untuk kategori Mesin A3+.',
                'materials' => [
                    ['name' => 'Art Paper 120 GSM', 'qty' => 1],
                    ['name' => 'Art Paper 150 GSM', 'qty' => 1],
                    ['name' => 'Art Carton 210 GSM', 'qty' => 1],
                    ['name' => 'Art Carton 230 GSM', 'qty' => 1],
                    ['name' => 'Art Carton 250 GSM', 'qty' => 1],
                    ['name' => 'Art Carton 310 GSM', 'qty' => 1],
                    ['name' => 'BW 250 GSM', 'qty' => 1],
                    ['name' => 'HVS 80 GSM', 'qty' => 1],
                ],
            ],
        ];

        foreach ($products as $payload) {
            $categoryName = strtolower(trim($payload['category'] ?? ''));
            $unitName = strtolower(trim($payload['unit'] ?? ''));

            $category = $categoryMap[$categoryName] ?? null;
            $unit = $unitMap[$unitName] ?? null;

            if (! $category || ! $unit) {
                continue;
            }

            $data = Arr::only($payload, [
                'sku', 'name', 'product_type', 'base_price', 'sale_price', 'length_cm', 'width_cm', 'description',
            ]);
            $data['category_id'] = $category->id;
            $data['unit_id'] = $unit->id;
            $data['branch_id'] = 1;

            $product = Product::query()->updateOrCreate(
                ['sku' => $payload['sku']],
                $data
            );

            $materials = collect($payload['materials'] ?? [])
                ->map(function ($material) use ($materialMap, $product) {
                    $name = is_array($material) ? ($material['name'] ?? null) : $material;
                    $qty = is_array($material) ? ($material['qty'] ?? 1) : 1;
                    if (!$name) {
                        return null;
                    }
                    $materialModel = $materialMap[strtolower(trim($name))] ?? null;
                    if (! $materialModel) {
                        return null;
                    }
                    return [
                        'product_id' => $product->id,
                        'material_id' => $materialModel->id,
                        'quantity' => $qty,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })
                ->filter()
                ->values()
                ->all();

            DB::table('mst_product_materials')->where('product_id', $product->id)->delete();
            if (! empty($materials)) {
                DB::table('mst_product_materials')->insert($materials);
            }
        }
    }
}
