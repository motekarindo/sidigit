<?php

namespace App\Traits;

use App\Models\Scopes\BranchScope;

trait BranchScoped
{
    public static function bootBranchScoped(): void
    {
        static::addGlobalScope(new BranchScope());

        static::creating(function ($model) {
            if (!empty($model->branch_id)) {
                return;
            }

            $activeBranchId = session('active_branch_id');
            if (!empty($activeBranchId)) {
                $model->branch_id = $activeBranchId;
                return;
            }

            $user = auth()->user();
            if ($user && !empty($user->branch_id)) {
                $model->branch_id = $user->branch_id;
            }
        });
    }
}
