<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnItem extends Model
{
    protected $table = 'return_items';
    protected $fillable = [
        'return_id',
        'product_id',
        'product_name',
        'sku',
        'unit',
        'original_qty',
        'return_qty',
        'unit_price',
        'line_discount_pct',
        'refund_amount',
    ];

    public function return()
    {
        return $this->belongsTo(Return_::class, 'return_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
