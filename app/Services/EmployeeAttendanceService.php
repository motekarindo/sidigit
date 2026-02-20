<?php

namespace App\Services;

use App\Models\EmployeeAttendance;
use App\Repositories\EmployeeAttendanceRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EmployeeAttendanceService
{
    public function __construct(
        protected EmployeeAttendanceRepository $repository
    ) {}

    public function query(): Builder
    {
        return $this->repository->query()->with('employee');
    }

    public function getPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return $this->query()->paginate($perPage);
    }

    public function store(array $data): EmployeeAttendance
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): EmployeeAttendance
    {
        $attendance = $this->repository->findOrFail($id);
        return $this->repository->update($attendance, $data);
    }

    public function destroy(int $id): void
    {
        $attendance = $this->repository->findOrFail($id);
        $this->repository->delete($attendance);
    }

    public function destroyMany(array $ids): void
    {
        $ids = array_values(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        $this->repository->query()->whereIn('id', $ids)->delete();
    }

    public function find(int $id): EmployeeAttendance
    {
        return $this->repository->query()->with('employee')->findOrFail($id);
    }
}
