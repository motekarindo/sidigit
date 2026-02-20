<?php

namespace App\Repositories;

use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Builder;

class StockMovementRepository
{
    public function query(): Builder
    {
        return StockMovement::query();
    }

    public function findOrFail(int $id): StockMovement
    {
        return StockMovement::query()->findOrFail($id);
    }

    public function create(array $data): StockMovement
    {
        return StockMovement::query()->create($data);
    }

    public function update(StockMovement $movement, array $data): StockMovement
    {
        $movement->update($data);

        return $movement;
    }

    public function delete(StockMovement $movement): void
    {
        $movement->delete();
    }
}
