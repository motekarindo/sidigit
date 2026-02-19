<?php

namespace App\Livewire\Admin\Stocks;

use App\Livewire\BaseTable;
use App\Models\Material;
use Illuminate\Support\Facades\DB;

class BalanceTable extends BaseTable
{
    protected function query()
    {
        return $this->applySearch(
            Material::query()
                ->leftJoin('stock_movements as sm', function ($join) {
                    $join->on('sm.material_id', '=', 'mst_materials.id')
                        ->whereNull('sm.deleted_at');
                })
                ->select(
                    'mst_materials.id',
                    'mst_materials.name',
                    'mst_materials.category_id',
                    'mst_materials.unit_id',
                    'mst_materials.reorder_level'
                )
                ->selectRaw(
                    'COALESCE(SUM(CASE WHEN sm.type = "in" THEN sm.qty WHEN sm.type = "out" THEN -sm.qty WHEN sm.type = "opname" THEN sm.qty ELSE 0 END), 0) as stock_balance'
                )
                ->groupBy(
                    'mst_materials.id',
                    'mst_materials.name',
                    'mst_materials.category_id',
                    'mst_materials.unit_id',
                    'mst_materials.reorder_level'
                )
                ->with(['category', 'unit']),
            ['mst_materials.name']
        );
    }

    protected function applySorting($query)
    {
        return $query->orderBy('mst_materials.name');
    }

    protected function resetForm(): void
    {
        // No modal form.
    }

    protected function loadForm(int $id): void
    {
        // No modal form.
    }

    protected function formView(): ?string
    {
        return null;
    }

    protected function columns(): array
    {
        return [
            ['label' => 'Bahan', 'field' => 'name', 'sortable' => false],
            ['label' => 'Kategori', 'field' => 'category.name', 'sortable' => false],
            ['label' => 'Satuan', 'field' => 'unit.name', 'sortable' => false],
            [
                'label' => 'Saldo',
                'field' => 'stock_balance',
                'sortable' => false,
                'format' => fn ($row) => number_format((float) $row->stock_balance, 2, ',', '.'),
            ],
            [
                'label' => 'Reorder Level',
                'field' => 'reorder_level',
                'sortable' => false,
                'format' => fn ($row) => number_format((float) $row->reorder_level, 2, ',', '.'),
            ],
        ];
    }

    protected function selectionColumnCheckbox(): bool
    {
        return false;
    }
}
