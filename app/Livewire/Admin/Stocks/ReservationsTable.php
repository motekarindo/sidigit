<?php

namespace App\Livewire\Admin\Stocks;

use App\Livewire\BaseTable;
use App\Models\StockMovement;
use App\Support\UnitFormatter;

class ReservationsTable extends BaseTable
{
    protected function query()
    {
        $query = StockMovement::query()
            ->select('stock_movements.*', 'o.order_no as order_no', 'm.name as material_name')
            ->leftJoin('orders as o', function ($join) {
                $join->on('o.id', '=', 'stock_movements.ref_id')
                    ->where('stock_movements.ref_type', '=', 'order');
            })
            ->leftJoin('mst_materials as m', 'm.id', '=', 'stock_movements.material_id')
            ->where('stock_movements.type', 'reserve')
            ->with(['material', 'material.unit']);

        return $this->applySearch($query, ['o.order_no', 'm.name', 'stock_movements.notes']);
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
            ['label' => 'Order No', 'view' => 'livewire.admin.stocks.columns.order-link', 'sortable' => false],
            ['label' => 'Bahan', 'field' => 'material.name', 'sortable' => false],
            [
                'label' => 'Qty',
                'field' => 'qty',
                'sortable' => false,
                'format' => fn ($row) => UnitFormatter::quantity(
                    (float) $row->qty,
                    $row->material?->unit?->name
                ),
            ],
            ['label' => 'Catatan', 'field' => 'notes', 'sortable' => false],
            ['label' => 'Dibuat Pada', 'field' => 'created_at', 'sortable' => false],
        ];
    }

    protected function selectionColumnCheckbox(): bool
    {
        return false;
    }
}
