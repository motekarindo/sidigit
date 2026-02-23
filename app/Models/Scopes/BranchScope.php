<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class BranchScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        $user = auth()->user();
        if (!$user) {
            return;
        }

        $activeBranchId = session('active_branch_id');
        if (!empty($activeBranchId)) {
            $builder->where($model->getTable() . '.branch_id', $activeBranchId);
            return;
        }

        if (method_exists($user, 'isBranchSuperAdmin') && $user->isBranchSuperAdmin()) {
            return;
        }

        if (!empty($user->branch_id)) {
            $builder->where($model->getTable() . '.branch_id', $user->branch_id);
        }
    }
}
