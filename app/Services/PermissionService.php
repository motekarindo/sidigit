<?php

namespace App\Services;

use App\Models\Permission;
use App\Repositories\PermissionRepository;
use Illuminate\Database\Eloquent\Builder;

class PermissionService
{
    public function __construct(
        protected PermissionRepository $repository
    ) {}

    public function query(): Builder
    {
        return $this->repository->query()->with('menu');
    }

    public function find(int $id): Permission
    {
        return $this->repository->findOrFail($id);
    }

    public function store(array $data): Permission
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Permission
    {
        $permission = $this->repository->findOrFail($id);
        return $this->repository->update($permission, $data);
    }

    public function destroy(int $id): void
    {
        $permission = $this->repository->findOrFail($id);
        $this->repository->delete($permission);
    }

    public function destroyMany(array $ids): void
    {
        $ids = array_values(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        $this->repository->query()->whereIn('id', $ids)->delete();
    }
}
