<?php

namespace App\Repositories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;

class RoleRepository
{
    public function query(): Builder
    {
        return Role::query();
    }

    public function findOrFail(int $id): Role
    {
        return Role::query()->findOrFail($id);
    }

    public function create(array $data): Role
    {
        return Role::query()->create($data);
    }

    public function update(Role $role, array $data): Role
    {
        $role->update($data);

        return $role;
    }

    public function delete(Role $role): void
    {
        $role->delete();
    }
}
