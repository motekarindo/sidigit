<?php

namespace App\Models;

use App\Traits\LogsAllActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use RichanFongdasen\EloquentBlameable\BlameableTrait;

class Expense extends Model
{
    use HasFactory, LogsAllActivity, BlameableTrait, SoftDeletes;

    protected $table = 'expenses';

    protected $fillable = [
        'type',
        'material_id',
        'supplier_id',
        'unit_id',
        'qty',
        'unit_cost',
        'qty_base',
        'unit_cost_base',
        'amount',
        'payment_method',
        'expense_date',
        'notes',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'qty_base' => 'decimal:2',
        'unit_cost_base' => 'decimal:2',
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}
