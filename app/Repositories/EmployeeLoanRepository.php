<?php

namespace App\Repositories;

use App\Models\EmployeeLoan;
use Illuminate\Database\Eloquent\Builder;

class EmployeeLoanRepository
{
    public function query(): Builder
    {
        return EmployeeLoan::query();
    }

    public function findOrFail(int $id): EmployeeLoan
    {
        return EmployeeLoan::query()->findOrFail($id);
    }

    public function create(array $data): EmployeeLoan
    {
        return EmployeeLoan::query()->create($data);
    }

    public function update(EmployeeLoan $loan, array $data): EmployeeLoan
    {
        $loan->update($data);

        return $loan;
    }

    public function delete(EmployeeLoan $loan): void
    {
        $loan->delete();
    }
}
