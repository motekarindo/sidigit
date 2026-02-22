<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusLog extends Model
{
    use HasFactory;

    protected $table = 'order_status_logs';

    protected $fillable = [
        'order_id',
        'status',
        'changed_by',
        'note',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
