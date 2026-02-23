<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $now = now();

            $m2Id = DB::table('mst_units')
                ->whereRaw('LOWER(name) in (?, ?)', ['m2', 'm²'])
                ->value('id');

            if (!$m2Id) {
                $m2Id = DB::table('mst_units')->insertGetId([
                    'name' => 'M2',
                    'is_dimension' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $cmId = DB::table('mst_units')
                ->whereRaw('LOWER(name) = ?', ['cm'])
                ->value('id');

            $rollIds = DB::table('mst_units')
                ->whereRaw('LOWER(name) in (?, ?)', ['rol', 'roll'])
                ->pluck('id')
                ->all();

            if (empty($rollIds)) {
                return;
            }

            $materials = DB::table('mst_materials')
                ->select('id', 'unit_id', 'purchase_unit_id', 'conversion_qty', 'reorder_level')
                ->whereIn('purchase_unit_id', $rollIds)
                ->where(function ($query) use ($cmId, $m2Id) {
                    if ($cmId) {
                        $query->where('unit_id', $cmId)->orWhere('unit_id', $m2Id);
                    } else {
                        $query->where('unit_id', $m2Id);
                    }
                })
                ->get();

            $scaledMaterialIds = [];

            foreach ($materials as $material) {
                $currentUnitId = (int) $material->unit_id;
                $currentConversion = (float) ($material->conversion_qty ?? 0);
                $currentReorder = (float) ($material->reorder_level ?? 0);

                $wasCmBase = $cmId && $currentUnitId === (int) $cmId;
                $needsScale = $wasCmBase || $currentConversion > 1000;

                $newConversion = $needsScale
                    ? round(max($currentConversion / 10000, 0.0001), 4)
                    : ($currentConversion > 0 ? $currentConversion : 1);

                $payload = [
                    'unit_id' => $m2Id,
                    'conversion_qty' => $newConversion,
                    'updated_at' => $now,
                ];

                if ($wasCmBase) {
                    $payload['reorder_level'] = round($currentReorder / 10000, 4);
                }

                DB::table('mst_materials')
                    ->where('id', $material->id)
                    ->update($payload);

                if ($needsScale) {
                    $scaledMaterialIds[] = (int) $material->id;
                }
            }

            if (!empty($scaledMaterialIds)) {
                DB::table('stock_movements')
                    ->whereIn('material_id', $scaledMaterialIds)
                    ->where(function ($query) {
                        $query->whereNull('ref_type')
                            ->orWhereIn('ref_type', ['manual', 'expense']);
                    })
                    ->update([
                        'qty' => DB::raw('qty / 10000'),
                        'updated_at' => $now,
                    ]);
            }
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            $now = now();

            $m2Id = DB::table('mst_units')
                ->whereRaw('LOWER(name) in (?, ?)', ['m2', 'm²'])
                ->value('id');
            $cmId = DB::table('mst_units')
                ->whereRaw('LOWER(name) = ?', ['cm'])
                ->value('id');
            $rollIds = DB::table('mst_units')
                ->whereRaw('LOWER(name) in (?, ?)', ['rol', 'roll'])
                ->pluck('id')
                ->all();

            if (!$m2Id || !$cmId || empty($rollIds)) {
                return;
            }

            $materials = DB::table('mst_materials')
                ->select('id', 'conversion_qty', 'reorder_level')
                ->where('unit_id', $m2Id)
                ->whereIn('purchase_unit_id', $rollIds)
                ->get();

            $revertedMaterialIds = [];

            foreach ($materials as $material) {
                DB::table('mst_materials')
                    ->where('id', $material->id)
                    ->update([
                        'unit_id' => $cmId,
                        'conversion_qty' => round(max(((float) $material->conversion_qty) * 10000, 1), 2),
                        'reorder_level' => round(((float) $material->reorder_level) * 10000, 2),
                        'updated_at' => $now,
                    ]);

                $revertedMaterialIds[] = (int) $material->id;
            }

            if (!empty($revertedMaterialIds)) {
                DB::table('stock_movements')
                    ->whereIn('material_id', $revertedMaterialIds)
                    ->where(function ($query) {
                        $query->whereNull('ref_type')
                            ->orWhereIn('ref_type', ['manual', 'expense']);
                    })
                    ->update([
                        'qty' => DB::raw('qty * 10000'),
                        'updated_at' => $now,
                    ]);
            }
        });
    }
};
