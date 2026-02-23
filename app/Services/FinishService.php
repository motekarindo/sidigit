<?php

namespace App\Services;

use App\Models\Finish;
use App\Repositories\FinishRepository;
use Illuminate\Database\Eloquent\Builder;

class FinishService
{
    protected FinishRepository $repository;

    public function __construct(FinishRepository $repository)
    {
        $this->repository = $repository;
    }

    public function query(): Builder
    {
        return $this->repository->query();
    }

    public function find(int $id): Finish
    {
        return $this->repository->findOrFail($id);
    }

    public function store(array $data): Finish
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Finish
    {
        $finish = $this->repository->findOrFail($id);
        return $this->repository->update($finish, $data);
    }

    public function destroy(int $id): void
    {
        $finish = $this->repository->findOrFail($id);
        $this->repository->delete($finish);
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
