<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class UserRepository
{
    public function query(): Builder
    {
        return User::query();
    }

    public function queryTrashed(): Builder
    {
        return User::onlyTrashed();
    }

    public function findOrFail(int $id): User
    {
        return User::query()->findOrFail($id);
    }

    public function findTrashed(int $id): User
    {
        return User::withTrashed()->findOrFail($id);
    }

    public function create(array $data): User
    {
        return User::query()->create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user;
    }

    public function delete(User $user): void
    {
        $user->delete();
    }
}
