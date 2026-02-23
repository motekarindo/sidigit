<?php

namespace App\Services;

use App\Models\User;
use App\Models\Employee;
use App\Repositories\UserRepository;
use Illuminate\Database\Eloquent\Builder;

class UserService
{
    protected UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function query(): Builder
    {
        return $this->repository->query()->with('roles');
    }

    public function queryTrashed(): Builder
    {
        return $this->repository->queryTrashed()->with('roles');
    }

    public function find(int $id): User
    {
        return $this->repository->findOrFail($id);
    }

    public function findWithRoles(int $id): User
    {
        return $this->repository->query()->with(['roles', 'branches'])->findOrFail($id);
    }

    public function store(array $data): User
    {
        $user = $this->repository->create($data);
        $this->syncEmployeeEmail($data['employee_id'] ?? null, $data['email'] ?? null);
        return $user;
    }

    public function update(int $id, array $data): User
    {
        $user = $this->repository->findOrFail($id);
        $user = $this->repository->update($user, $data);
        $this->syncEmployeeEmail($data['employee_id'] ?? null, $data['email'] ?? null);
        return $user;
    }

    public function syncRoles(int $id, array $roles): void
    {
        $user = $this->repository->findOrFail($id);
        $user->roles()->sync($roles);
    }

    public function syncBranches(int $id, array $branches): void
    {
        $user = $this->repository->findOrFail($id);
        $branches = array_values(array_filter($branches));
        $user->branches()->sync($branches);
    }

    protected function syncEmployeeEmail(?int $employeeId, ?string $email): void
    {
        if (empty($employeeId) || empty($email)) {
            return;
        }

        Employee::query()
            ->whereKey($employeeId)
            ->whereNull('email')
            ->update(['email' => $email]);
    }

    public function destroy(int $id): void
    {
        $user = $this->repository->findOrFail($id);
        $this->repository->delete($user);
    }

    public function destroyMany(array $ids): void
    {
        $ids = array_values(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        $this->repository->query()->whereIn('id', $ids)->delete();
    }

    public function restore(int $id): void
    {
        $user = $this->repository->findTrashed($id);
        $user->restore();
    }

    public function restoreMany(array $ids): void
    {
        $ids = array_values(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        $this->repository->queryTrashed()->whereIn('id', $ids)->restore();
    }
}
