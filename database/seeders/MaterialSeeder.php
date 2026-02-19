<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaterialSeeder extends Seeder
{
    public function run(): void
    {
        $categoryMap = DB::table('mst_categories')
            ->select('id', 'name')
            ->get()
            ->keyBy(fn ($row) => strtolower($row->name));

        $unitMap = DB::table('mst_units')
            ->select('id', 'name')
            ->get()
            ->keyBy(fn ($row) => strtolower($row->name));

        $materials = [
            ['name' => 'Flexy Cina 280 GSM', 'category' => 'outdoor', 'unit' => 'cm', 'cost_price' => 20000],
            ['name' => 'Flexy Korea 380 GSM', 'category' => 'outdoor', 'unit' => 'cm', 'cost_price' => 28000],
            [
                'name' => 'Art Paper 150 GSM',
                'category' => 'indoor',
                'unit' => 'pcs',
                'purchase_unit' => 'rim',
                'conversion_qty' => 500,
                'cost_price' => 1500,
            ],
        ];

        foreach ($materials as $material) {
            $category = $categoryMap[$material['category']] ?? null;
            $unit = $unitMap[$material['unit']] ?? null;
            $purchaseUnit = isset($material['purchase_unit'])
                ? ($unitMap[$material['purchase_unit']] ?? null)
                : null;

            if (!$category || !$unit) {
                continue;
            }

            $exists = DB::table('mst_materials')
                ->whereRaw('LOWER(name) = ?', [strtolower($material['name'])])
                ->exists();

            if (!$exists) {
                DB::table('mst_materials')->insert([
                    'name' => $material['name'],
                    'category_id' => $category->id,
                    'unit_id' => $unit->id,
                    'description' => null,
                    'cost_price' => $material['cost_price'],
                    'purchase_unit_id' => $purchaseUnit?->id,
                    'conversion_qty' => $material['conversion_qty'] ?? 1,
                    'reorder_level' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
