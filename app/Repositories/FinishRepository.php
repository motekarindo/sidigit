<?php

namespace App\Repositories;

use App\Models\Finish;
use Illuminate\Database\Eloquent\Builder;

class FinishRepository
{
    public function query(): Builder
    {
        return Finish::query();
    }

    public function findOrFail(int $id): Finish
    {
        return Finish::query()->findOrFail($id);
    }

    public function create(array $data): Finish
    {
        return Finish::query()->create($data);
    }

    public function update(Finish $finish, array $data): Finish
    {
        $finish->update($data);

        return $finish;
    }

    public function delete(Finish $finish): void
    {
        $finish->delete();
    }
}
