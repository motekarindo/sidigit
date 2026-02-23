<?php

namespace App\Repositories;

use App\Models\Expense;
use Illuminate\Database\Eloquent\Builder;

class ExpenseRepository
{
    public function query(): Builder
    {
        return Expense::query();
    }

    public function findOrFail(int $id): Expense
    {
        return Expense::query()->findOrFail($id);
    }

    public function create(array $data): Expense
    {
        return Expense::query()->create($data);
    }

    public function update(Expense $expense, array $data): Expense
    {
        $expense->update($data);

        return $expense;
    }

    public function delete(Expense $expense): void
    {
        $expense->delete();
    }
}
