<?php

namespace App\Models;

use App\Traits\LogsAllActivity;
use App\Traits\BranchScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use RichanFongdasen\EloquentBlameable\BlameableTrait;

class Category extends Model
{
    use HasFactory, LogsAllActivity, BranchScoped, BlameableTrait, SoftDeletes;

    protected $table = 'mst_categories';

    protected $fillable = [
        'name',
        'branch_id',
    ];

    public function materials()
    {
        return $this->hasMany(Material::class, 'category_id');
    }
}
