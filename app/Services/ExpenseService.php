<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\StockMovement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ExpenseService
{
    public function query(): Builder
    {
        return Expense::query()->with(['material', 'supplier']);
    }

    public function queryByType(string $type): Builder
    {
        return $this->query()->where('type', $type);
    }

    public function getPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return $this->query()->paginate($perPage);
    }

    public function store(array $data): Expense
    {
        $payload = $this->normalize($data);

        return DB::transaction(function () use ($payload) {
            $expense = Expense::create($payload);
            $this->syncStock($expense, $payload);

            return $expense;
        });
    }

    public function update(int $id, array $data): Expense
    {
        $payload = $this->normalize($data);

        return DB::transaction(function () use ($id, $payload) {
            $expense = Expense::findOrFail($id);
            $expense->update($payload);

            StockMovement::query()
                ->where('ref_type', 'expense')
                ->where('ref_id', $expense->id)
                ->delete();

            $this->syncStock($expense, $payload);

            return $expense;
        });
    }

    public function destroy(int $id): void
    {
        DB::transaction(function () use ($id) {
            $expense = Expense::findOrFail($id);
            StockMovement::query()
                ->where('ref_type', 'expense')
                ->where('ref_id', $expense->id)
                ->delete();
            $expense->delete();
        });
    }

    public function destroyMany(array $ids): void
    {
        $ids = array_values(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        DB::transaction(function () use ($ids) {
            StockMovement::query()
                ->where('ref_type', 'expense')
                ->whereIn('ref_id', $ids)
                ->delete();
            Expense::query()->whereIn('id', $ids)->delete();
        });
    }

    public function find(int $id): Expense
    {
        return Expense::with(['material', 'supplier'])->findOrFail($id);
    }

    protected function syncStock(Expense $expense, array $payload): void
    {
        if (($payload['type'] ?? 'general') !== 'material') {
            return;
        }

        $qty = (float) ($payload['qty'] ?? 0);
        if ($qty <= 0) {
            return;
        }

        if (empty($payload['material_id'])) {
            return;
        }

        StockMovement::create([
            'material_id' => $payload['material_id'],
            'type' => 'in',
            'qty' => $qty,
            'ref_type' => 'expense',
            'ref_id' => $expense->id,
            'notes' => $payload['notes'] ?? 'Pembelian bahan',
        ]);
    }

    protected function normalize(array $data): array
    {
        $type = $data['type'] ?? 'general';
        $qty = $data['qty'] ?? null;
        $unitCost = $data['unit_cost'] ?? null;
        $amount = $data['amount'] ?? null;

        if ($amount === null && $qty !== null && $unitCost !== null) {
            $amount = (float) $qty * (float) $unitCost;
        }

        if ($type !== 'material') {
            $data['material_id'] = null;
            $data['supplier_id'] = null;
            $data['qty'] = null;
            $data['unit_cost'] = null;
        }

        return [
            'type' => $type,
            'material_id' => $data['material_id'] ?? null,
            'supplier_id' => $data['supplier_id'] ?? null,
            'qty' => $data['qty'] ?? null,
            'unit_cost' => $data['unit_cost'] ?? null,
            'amount' => $amount ?? 0,
            'payment_method' => $data['payment_method'] ?? 'cash',
            'expense_date' => $data['expense_date'] ?? now()->format('Y-m-d'),
            'notes' => $data['notes'] ?? null,
        ];
    }
}
