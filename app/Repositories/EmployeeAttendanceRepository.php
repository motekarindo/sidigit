<?php

namespace App\Repositories;

use App\Models\EmployeeAttendance;
use Illuminate\Database\Eloquent\Builder;

class EmployeeAttendanceRepository
{
    public function query(): Builder
    {
        return EmployeeAttendance::query();
    }

    public function findOrFail(int $id): EmployeeAttendance
    {
        return EmployeeAttendance::query()->findOrFail($id);
    }

    public function create(array $data): EmployeeAttendance
    {
        return EmployeeAttendance::query()->create($data);
    }

    public function update(EmployeeAttendance $attendance, array $data): EmployeeAttendance
    {
        $attendance->update($data);

        return $attendance;
    }

    public function delete(EmployeeAttendance $attendance): void
    {
        $attendance->delete();
    }
}
