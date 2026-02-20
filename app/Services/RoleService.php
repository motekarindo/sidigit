<?php

namespace App\Services;

use App\Models\Role;
use App\Repositories\RoleRepository;
use Illuminate\Database\Eloquent\Builder;

class RoleService
{
    public function __construct(
        protected RoleRepository $repository
    ) {}

    public function query(): Builder
    {
        return $this->repository->query();
    }

    public function all()
    {
        return $this->repository->query()->orderBy('name')->get();
    }

    public function find(int $id): Role
    {
        return $this->repository->findOrFail($id);
    }

    public function findWithRelations(int $id): Role
    {
        return $this->repository->query()->with(['permissions', 'menus'])->findOrFail($id);
    }

    public function store(array $data): Role
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Role
    {
        $role = $this->repository->findOrFail($id);
        return $this->repository->update($role, $data);
    }

    public function syncPermissionsMenus(int $id, array $permissions, array $menus): void
    {
        $role = $this->repository->findOrFail($id);
        $role->permissions()->sync($permissions);
        $role->menus()->sync($menus);
    }

    public function destroy(int $id): void
    {
        $role = $this->repository->findOrFail($id);
        $this->repository->delete($role);
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
