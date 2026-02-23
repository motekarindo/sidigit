<?php

namespace App\Repositories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Builder;

class BranchRepository
{
    public function query(): Builder
    {
        return Branch::query();
    }

    public function findOrFail(int $id): Branch
    {
        return Branch::query()->findOrFail($id);
    }

    public function create(array $data): Branch
    {
        return Branch::query()->create($data);
    }

    public function update(Branch $branch, array $data): Branch
    {
        $branch->update($data);

        return $branch;
    }

    public function delete(Branch $branch): void
    {
        $branch->delete();
    }
}
