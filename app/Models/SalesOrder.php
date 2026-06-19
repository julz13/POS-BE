<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    protected $table = 'sales_orders';
    protected $fillable = [
        'so_number',
        'customer_name',
        'customer_id',
        'phone',
        'order_date',
        'expected_pickup',
        'deposit_amount',
        'subtotal',
        'total',
        'balance_due',
        'status',
        'notes',
        'converted_transaction_id',
        'created_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_pickup' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class, 'so_id');
    }
}
