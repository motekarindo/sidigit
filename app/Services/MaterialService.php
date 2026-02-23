<?php

namespace App\Services;

use App\Models\Material;
use App\Repositories\MaterialRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class MaterialService
{
    protected $repository;
    public function __construct(MaterialRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getPaginated(): LengthAwarePaginator
    {
        return $this->repository->paginate();
    }

    public function query(): Builder
    {
        return $this->repository->query()->with(['category', 'unit']);
    }

    public function queryTrashed(): Builder
    {
        return $this->repository->query()->onlyTrashed()->with(['category', 'unit']);
    }

    public function store(array $data): Material
    {
        $data['reorder_level'] = isset($data['reorder_level']) && $data['reorder_level'] !== null
            ? $data['reorder_level']
            : 0;
        $data['conversion_qty'] = isset($data['conversion_qty']) && $data['conversion_qty'] !== null
            ? $data['conversion_qty']
            : 1;
        $data['roll_width_cm'] = isset($data['roll_width_cm']) && $data['roll_width_cm'] !== ''
            ? $data['roll_width_cm']
            : null;
        $data['roll_waste_percent'] = isset($data['roll_waste_percent']) && $data['roll_waste_percent'] !== null && $data['roll_waste_percent'] !== ''
            ? $data['roll_waste_percent']
            : 0;

        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Material
    {
        $material = $this->repository->findOrFail($id);

        $data['reorder_level'] = isset($data['reorder_level']) && $data['reorder_level'] !== null
            ? $data['reorder_level']
            : 0;
        $data['conversion_qty'] = isset($data['conversion_qty']) && $data['conversion_qty'] !== null
            ? $data['conversion_qty']
            : 1;
        $data['roll_width_cm'] = isset($data['roll_width_cm']) && $data['roll_width_cm'] !== ''
            ? $data['roll_width_cm']
            : null;
        $data['roll_waste_percent'] = isset($data['roll_waste_percent']) && $data['roll_waste_percent'] !== null && $data['roll_waste_percent'] !== ''
            ? $data['roll_waste_percent']
            : 0;

        return $this->repository->update($material, $data);
    }

    public function destroy(int $id): void
    {
        $material = $this->repository->findOrFail($id);

        $this->repository->delete($material);
    }

    public function destroyMany(array $ids): void
    {
        $ids = array_values(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        $this->repository->query()->whereIn('id', $ids)->delete();
    }

    public function restore(int $id): void
    {
        $material = $this->repository->query()->withTrashed()->findOrFail($id);
        $material->restore();
    }

    public function restoreMany(array $ids): void
    {
        $ids = array_values(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        $this->repository->query()->withTrashed()->whereIn('id', $ids)->restore();
    }

    public function find(int $id): Material
    {
        return $this->repository->findOrFail($id);
    }
}
