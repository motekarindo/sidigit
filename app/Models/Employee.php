<?php

namespace App\Models;

use App\Enums\EmployeeStatus;
use App\Traits\LogsAllActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use RichanFongdasen\EloquentBlameable\BlameableTrait;

class Employee extends Model
{
    use HasFactory, LogsAllActivity, BlameableTrait, SoftDeletes;

    protected $table = 'mst_employees';

    protected $fillable = [
        'name',
        'address',
        'phone_number',
        'email',
        'photo',
        'salary',
        'status',
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
}
