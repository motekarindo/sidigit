<?php

namespace App\Models;

use App\Traits\LogsAllActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use RichanFongdasen\EloquentBlameable\BlameableTrait;

class Branch extends Model
{
    use HasFactory, LogsAllActivity, BlameableTrait, SoftDeletes;

    protected $table = 'branches';

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'logo_path',
        'qris_path',
        'is_main',
    ];

    protected $casts = [
        'is_main' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'branch_user')->withTimestamps();
    }

    public function defaultUsers()
    {
        return $this->hasMany(User::class, 'branch_id');
    }
}
