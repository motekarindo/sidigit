<?php

namespace App\Services;

use App\Models\EmployeeLoan;
use App\Repositories\EmployeeLoanRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EmployeeLoanService
{
    protected EmployeeLoanRepository $repository;

    public function __construct(EmployeeLoanRepository $repository)
    {
        $this->repository = $repository;
    }

    public function query(): Builder
    {
        return $this->repository->query()->with('employee');
    }

    public function getPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return $this->query()->paginate($perPage);
    }

    public function store(array $data): EmployeeLoan
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): EmployeeLoan
    {
        $loan = $this->repository->findOrFail($id);
        return $this->repository->update($loan, $data);
    }

    public function destroy(int $id): void
    {
        $loan = $this->repository->findOrFail($id);
        $this->repository->delete($loan);
    }

    public function destroyMany(array $ids): void
    {
        $ids = array_values(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        $this->repository->query()->whereIn('id', $ids)->delete();
    }

    public function find(int $id): EmployeeLoan
    {
        return $this->repository->query()->with('employee')->findOrFail($id);
    }
}
