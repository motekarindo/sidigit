<?php

namespace App\Models;

use App\Traits\LogsAllActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use RichanFongdasen\EloquentBlameable\BlameableTrait;

class OrderItemFinish extends Model
{
    use HasFactory, LogsAllActivity, BlameableTrait, SoftDeletes;

    protected $table = 'order_item_finishes';

    protected $fillable = [
        'order_item_id',
        'finish_id',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    public function finish()
    {
        return $this->belongsTo(Finish::class, 'finish_id');
    }
}
