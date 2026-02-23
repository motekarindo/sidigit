<?php

namespace App\Models;

use App\Traits\LogsAllActivity;
use App\Traits\BranchScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use RichanFongdasen\EloquentBlameable\BlameableTrait;

class Material extends Model
{
    use HasFactory, LogsAllActivity, BranchScoped, BlameableTrait, SoftDeletes;

    protected $table = 'mst_materials';

    protected $fillable = [
        'name',
        'category_id',
        'unit_id',
        'purchase_unit_id',
        'description',
        'cost_price',
        'conversion_qty',
        'roll_width_cm',
        'roll_waste_percent',
        'reorder_level',
        'branch_id',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'conversion_qty' => 'decimal:2',
        'roll_width_cm' => 'decimal:2',
        'roll_waste_percent' => 'decimal:2',
        'reorder_level' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function purchaseUnit()
    {
        return $this->belongsTo(Unit::class, 'purchase_unit_id');
    }
}
