<?php

namespace App\Services;

use App\Models\EmployeeLoan;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EmployeeLoanService
{
    public function query(): Builder
    {
        return EmployeeLoan::query()->with('employee');
    }

    public function getPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return $this->query()->paginate($perPage);
    }

    public function store(array $data): EmployeeLoan
    {
        return EmployeeLoan::create($data);
    }

    public function update(int $id, array $data): EmployeeLoan
    {
        $loan = EmployeeLoan::findOrFail($id);
        $loan->update($data);

        return $loan;
    }

    public function destroy(int $id): void
    {
        $loan = EmployeeLoan::findOrFail($id);
        $loan->delete();
    }

    public function destroyMany(array $ids): void
    {
        $ids = array_values(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        EmployeeLoan::query()->whereIn('id', $ids)->delete();
    }

    public function find(int $id): EmployeeLoan
    {
        return EmployeeLoan::with('employee')->findOrFail($id);
    }
}
