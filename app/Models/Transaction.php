<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'receipt_number',
        'date',
        'cashier_id',
        'customer_id',
        'customer_name',
        'shift_id',
        'subtotal',
        'line_discounts_total',
        'transaction_discount_pct',
        'transaction_discount_amount',
        'transaction_discount_type',
        'tax_amount',
        'tax_rate',
        'tax_type',
        'total',
        'amount_paid',
        'change_given',
        'status',
        'source',
        'source_id',
        'voided_at',
        'voided_by',
        'void_reason',
    ];

    protected $casts = [
        'date' => 'timestamp',
        'voided_at' => 'timestamp',
    ];

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    public function voidedBy()
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class, 'transaction_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'transaction_id');
    }

    public function returns()
    {
        return $this->hasMany(Return_::class, 'transaction_id');
    }

    public function discountAudits()
    {
        return $this->hasMany(DiscountAudit::class, 'transaction_id');
    }
}
