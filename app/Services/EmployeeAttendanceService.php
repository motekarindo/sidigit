<?php

namespace App\Services;

use App\Models\EmployeeAttendance;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EmployeeAttendanceService
{
    public function query(): Builder
    {
        return EmployeeAttendance::query()->with('employee');
    }

    public function getPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return $this->query()->paginate($perPage);
    }

    public function store(array $data): EmployeeAttendance
    {
        return EmployeeAttendance::create($data);
    }

    public function update(int $id, array $data): EmployeeAttendance
    {
        $attendance = EmployeeAttendance::findOrFail($id);
        $attendance->update($data);

        return $attendance;
    }

    public function destroy(int $id): void
    {
        $attendance = EmployeeAttendance::findOrFail($id);
        $attendance->delete();
    }

    public function destroyMany(array $ids): void
    {
        $ids = array_values(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        EmployeeAttendance::query()->whereIn('id', $ids)->delete();
    }

    public function find(int $id): EmployeeAttendance
    {
        return EmployeeAttendance::with('employee')->findOrFail($id);
    }
}
