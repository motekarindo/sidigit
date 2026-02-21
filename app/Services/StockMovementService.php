<?php

namespace App\Services;

use App\Models\StockMovement;
use App\Models\Material;
use App\Repositories\StockMovementRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class StockMovementService
{
    protected StockMovementRepository $repository;

    public function __construct(StockMovementRepository $repository)
    {
        $this->repository = $repository;
    }

    public function query(): Builder
    {
        return $this->repository->query()->with('material');
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
        $payload['qty'] = $this->convertToBaseQty($data);

        return $this->repository->create($payload);
    }

    public function update(int $id, array $data): StockMovement
    {
        $movement = $this->repository->findOrFail($id);
        $this->ensureManual($movement);
        $payload = $this->payload($data);
        $payload['qty'] = $this->convertToBaseQty($data);
        return $this->repository->update($movement, $payload);
    }

    public function destroy(int $id): void
    {
        $movement = $this->repository->findOrFail($id);
        $this->ensureManual($movement);
        $this->repository->delete($movement);
    }

    public function destroyMany(array $ids): void
    {
        $ids = array_values(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        $movements = $this->repository->query()->whereIn('id', $ids)->get();
        $manualIds = $movements
            ->filter(fn ($movement) => empty($movement->ref_type) || $movement->ref_type === 'manual')
            ->pluck('id')
            ->all();

        if (!empty($manualIds)) {
            $this->repository->query()->whereIn('id', $manualIds)->delete();
        }
    }

    public function find(int $id): StockMovement
    {
        return $this->repository->findOrFail($id);
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
            'branch_id' => $data['branch_id'] ?? null,
        ];
    }

    protected function convertToBaseQty(array $data): float
    {
        $qty = (float) ($data['qty'] ?? 0);
        $materialId = $data['material_id'] ?? null;
        if (!$materialId) {
            return $qty;
        }

        $material = Material::find($materialId);
        if (!$material) {
            return $qty;
        }

        $unitId = $data['unit_id'] ?? null;
        if ($unitId && $unitId === $material->unit_id) {
            return $qty;
        }

        if ($unitId && $material->purchase_unit_id && $unitId === $material->purchase_unit_id) {
            $conversion = (float) ($material->conversion_qty ?: 1);
            return $qty * $conversion;
        }

        return $qty;
    }

    protected function ensureManual(StockMovement $movement): void
    {
        if (!empty($movement->ref_type) && $movement->ref_type !== 'manual') {
            throw new \RuntimeException('Pergerakan stok otomatis tidak dapat diubah.');
        }
    }
}
