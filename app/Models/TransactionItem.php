<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    protected $table = 'transaction_items';
    protected $fillable = [
        'transaction_id',
        'product_id',
        'product_name',
        'sku',
        'category',
        'unit',
        'quantity',
        'unit_price',
        'original_unit_price',
        'line_discount_pct',
        'line_discount_type',
        'line_discount_amount',
        'line_total',
        'tax_code',
        'price_overridden',
        'price_override_approved_by',
    ];

    protected $casts = [
        'price_overridden' => 'boolean',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function priceOverrideApprovedBy()
    {
        return $this->belongsTo(User::class, 'price_override_approved_by');
    }

    public function discountAudits()
    {
        return $this->hasMany(DiscountAudit::class, 'transaction_item_id');
    }
}
