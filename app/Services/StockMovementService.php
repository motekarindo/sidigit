<?php

namespace App\Services;

use App\Models\StockMovement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class StockMovementService
{
    public function query(): Builder
    {
        return StockMovement::query()->with('material');
    }

    public function queryByType(string $type, bool $manualOnly = true): Builder
    {
        $query = $this->query()->where('type', $type);

        if ($manualOnly) {
            $query->where(function ($q) {
                $q->whereNull('ref_type')
                    ->orWhere('ref_type', 'manual');
            });
        }

        return $query;
    }

    public function getPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return $this->query()->paginate($perPage);
    }

    public function store(array $data): StockMovement
    {
        $payload = $this->payload($data);
        $payload['ref_type'] = $payload['ref_type'] ?? 'manual';

        return StockMovement::create($payload);
    }

    public function update(int $id, array $data): StockMovement
    {
        $movement = StockMovement::findOrFail($id);
        $this->ensureManual($movement);
        $movement->update($this->payload($data));

        return $movement;
    }

    public function destroy(int $id): void
    {
        $movement = StockMovement::findOrFail($id);
        $this->ensureManual($movement);
        $movement->delete();
    }

    public function destroyMany(array $ids): void
    {
        $ids = array_values(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        $movements = StockMovement::query()->whereIn('id', $ids)->get();
        $manualIds = $movements
            ->filter(fn ($movement) => empty($movement->ref_type) || $movement->ref_type === 'manual')
            ->pluck('id')
            ->all();

        if (!empty($manualIds)) {
            StockMovement::query()->whereIn('id', $manualIds)->delete();
        }
    }

    public function find(int $id): StockMovement
    {
        return StockMovement::findOrFail($id);
    }

    protected function payload(array $data): array
    {
        return [
            'material_id' => $data['material_id'] ?? null,
            'type' => $data['type'] ?? 'in',
            'qty' => $data['qty'] ?? 0,
            'ref_type' => $data['ref_type'] ?? null,
            'ref_id' => $data['ref_id'] ?? null,
            'notes' => $data['notes'] ?? null,
        ];
    }

    protected function ensureManual(StockMovement $movement): void
    {
        if (!empty($movement->ref_type) && $movement->ref_type !== 'manual') {
            throw new \RuntimeException('Pergerakan stok otomatis tidak dapat diubah.');
        }
    }
}
