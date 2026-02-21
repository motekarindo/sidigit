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
            //indoor
                ['name' => 'Albatros', 'category' => 'indoor', 'unit' => 'cm', 'purchase_unit' => 'roll', 'conversion_qty' => 500000, 'cost_price' => 12000],
                ['name' => 'Backlite', 'category' => 'indoor', 'unit' => 'cm', 'purchase_unit' => 'roll', 'conversion_qty' => 500000, 'cost_price' => 12000],

            //outdoor
                ['name' => 'Flexy Cina 240 GSM', 'category' => 'outdoor', 'unit' => 'cm', 'purchase_unit' => 'roll', 'conversion_qty' => 2100000, 'cost_price' => 12000],
                ['name' => 'Flexy Cina 280 GSM', 'category' => 'outdoor', 'unit' => 'cm', 'purchase_unit' => 'roll', 'conversion_qty' => 2100000, 'cost_price' => 15000],
                ['name' => 'Flexy Korea 380 GSM', 'category' => 'outdoor', 'unit' => 'cm', 'purchase_unit' => 'roll', 'conversion_qty' => 2100000, 'cost_price' => 23000],

            //mesin a3+
                ['name' => 'Art Paper 150 GSM', 'category' => 'mesin a3+', 'unit' => 'lembar', 'purchase_unit' => 'rim', 'conversion_qty' => 500, 'cost_price' => 1500,
                ['name' => 'Art Paper 120 GSM', 'category' => 'mesin a3+', 'unit' => 'lembar', 'purchase_unit' => 'rim', 'conversion_qty' => 500, 'cost_price' => 1500,
                ['name' => 'Art Cartoon 210 GSM', 'category' => 'mesin a3+', 'unit' => 'lembar', 'purchase_unit' => 'rim', 'conversion_qty' => 500, 'cost_price' => 1500,
                ['name' => 'Art Cartoon 230 GSM', 'category' => 'mesin a3+', 'unit' => 'lembar', 'purchase_unit' => 'rim', 'conversion_qty' => 500, 'cost_price' => 1500,
                ['name' => 'Art Cartoon 250 GSM', 'category' => 'mesin a3+', 'unit' => 'lembar', 'purchase_unit' => 'rim', 'conversion_qty' => 500, 'cost_price' => 1500,
                ['name' => 'Art Cartoon 310 GSM', 'category' => 'mesin a3+', 'unit' => 'lembar', 'purchase_unit' => 'rim', 'conversion_qty' => 500, 'cost_price' => 1500,
                ['name' => 'BW 250 GSM', 'category' => 'mesin a3+', 'unit' => 'lembar', 'purchase_unit' => 'rim', 'conversion_qty' => 500, 'cost_price' => 1500,
                ['name' => 'HVS 80 GSM', 'category' => 'mesin a3+', 'unit' => 'lembar', 'purchase_unit' => 'rim', 'conversion_qty' => 500, 'cost_price' => 1500,
                ['name' => 'Sticker Cromo', 'category' => 'mesin a3+', 'unit' => 'lembar', 'purchase_unit' => 'rim', 'conversion_qty' => 500, 'cost_price' => 1500,
                ['name' => 'Sticker Vinyl', 'category' => 'mesin a3+', 'unit' => 'lembar', 'purchase_unit' => 'rim', 'conversion_qty' => 500, 'cost_price' => 1500,

            //merchandise
                ['name' => 'Mug', 'category' => 'merchandise', 'unit' => 'pcs', 'purchase_unit' => 'box', 'conversion_qty' => 10, 'cost_price' => 1500,
                ['name' => 'Gantungan Kunci Bulat 2.5', 'category' => 'merchandise', 'unit' => 'pcs', 'purchase_unit' => 'pack', 'conversion_qty' => 50, 'cost_price' => 1500,
                ['name' => 'Gantungan Kunci Bulat 3.2', 'category' => 'merchandise', 'unit' => 'pcs', 'purchase_unit' => 'pack', 'conversion_qty' => 50, 'cost_price' => 1500,
                ['name' => 'Gantungan Kunci Bulat 4.4', 'category' => 'merchandise', 'unit' => 'pcs', 'purchase_unit' => 'pack', 'conversion_qty' => 50, 'cost_price' => 1500,
                ['name' => 'Gantungan Kunci Bulat 5.8', 'category' => 'merchandise', 'unit' => 'pcs', 'purchase_unit' => 'pack', 'conversion_qty' => 50, 'cost_price' => 1500,
                ['name' => 'Tumbler Stainless 330ML', 'category' => 'merchandise', 'unit' => 'pcs', 'purchase_unit' => 'box', 'conversion_qty' => 50, 'cost_price' => 20000,
                ['name' => 'Tumbler Stainless 500ML', 'category' => 'merchandise', 'unit' => 'pcs', 'purchase_unit' => 'box', 'conversion_qty' => 50, 'cost_price' => 25000,

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
