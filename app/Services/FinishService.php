<?php

namespace App\Services;

use App\Models\Finish;
use Illuminate\Database\Eloquent\Builder;

class FinishService
{
    public function query(): Builder
    {
        return Finish::query();
    }

    public function find(int $id): Finish
    {
        return Finish::findOrFail($id);
    }

    public function store(array $data): Finish
    {
        return Finish::create($data);
    }

    public function update(int $id, array $data): Finish
    {
        $finish = $this->find($id);
        $finish->update($data);

        return $finish;
    }

    public function destroy(int $id): void
    {
        $finish = $this->find($id);
        $finish->delete();
    }

    public function destroyMany(array $ids): void
    {
        $ids = array_values(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        Finish::query()->whereIn('id', $ids)->delete();
    }
}
