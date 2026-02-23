<?php

namespace App\Models;

use App\Traits\LogsAllActivity;
use App\Traits\BranchScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use RichanFongdasen\EloquentBlameable\BlameableTrait;

class StockMovement extends Model
{
    use HasFactory, LogsAllActivity, BranchScoped, BlameableTrait, SoftDeletes;

    protected $table = 'stock_movements';

    protected $fillable = [
        'material_id',
        'type',
        'qty',
        'ref_type',
        'ref_id',
        'notes',
        'branch_id',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }
}
