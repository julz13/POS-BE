<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    protected $fillable = [
        'quote_number',
        'customer_name',
        'customer_id',
        'phone',
        'date',
        'valid_until',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total',
        'status',
        'notes',
        'converted_transaction_id',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'valid_until' => 'date',
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
        return $this->hasMany(QuoteItem::class, 'quote_id');
    }
}
