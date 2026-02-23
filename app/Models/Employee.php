<?php

namespace App\Models;

use App\Enums\EmployeeStatus;
use App\Traits\LogsAllActivity;
use App\Traits\BranchScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use RichanFongdasen\EloquentBlameable\BlameableTrait;
use App\Models\User;

class Employee extends Model
{
    use HasFactory, LogsAllActivity, BranchScoped, BlameableTrait, SoftDeletes;

    protected $table = 'mst_employees';

    protected $fillable = [
        'name',
        'address',
        'phone_number',
        'email',
        'photo',
        'salary',
        'status',
        'branch_id',
    ];

    protected $casts = [
        'status' => EmployeeStatus::class,
        'salary' => 'integer',
    ];

    public function attendances()
    {
        return $this->hasMany(EmployeeAttendance::class, 'employee_id');
    }

    public function loans()
    {
        return $this->hasMany(EmployeeLoan::class, 'employee_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'employee_id');
    }
}
