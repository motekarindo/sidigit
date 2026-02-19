<?php

namespace App\Models;

use App\Traits\LogsAllActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use RichanFongdasen\EloquentBlameable\BlameableTrait;

class Order extends Model
{
    use HasFactory, LogsAllActivity, BlameableTrait, SoftDeletes;

    protected $table = 'orders';

    protected $fillable = [
        'order_no',
        'customer_id',
        'status',
        'order_date',
        'deadline',
        'total_hpp',
        'total_price',
        'total_discount',
        'grand_total',
        'paid_amount',
        'payment_status',
        'notes',
    ];

    protected $casts = [
        'order_date' => 'date',
        'deadline' => 'date',
        'total_hpp' => 'decimal:2',
        'total_price' => 'decimal:2',
        'total_discount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'order_id');
    }

    public function statusLogs()
    {
        return $this->hasMany(OrderStatusLog::class, 'order_id');
    }
}
