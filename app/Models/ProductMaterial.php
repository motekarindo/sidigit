<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductMaterial extends Model
{
    use HasFactory;

    protected $table = 'mst_product_materials';

    protected $fillable = [
        'product_id',
        'material_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }
}
