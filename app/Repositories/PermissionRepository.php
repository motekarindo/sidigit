<?php

namespace App\Repositories;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Builder;

class PermissionRepository
{
    public function query(): Builder
    {
        return Permission::query();
    }

    public function findOrFail(int $id): Permission
    {
        return Permission::query()->findOrFail($id);
    }

    public function create(array $data): Permission
    {
        return Permission::query()->create($data);
    }

    public function update(Permission $permission, array $data): Permission
    {
        $permission->update($data);

        return $permission;
    }

    public function delete(Permission $permission): void
    {
        $permission->delete();
    }
}
