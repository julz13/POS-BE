<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteItem extends Model
{
    protected $table = 'quote_items';
    protected $fillable = [
        'quote_id',
        'product_id',
        'product_name',
        'unit',
        'quantity',
        'unit_price',
        'line_discount_pct',
        'line_discount_amount',
        'subtotal',
    ];

    public function quote()
    {
        return $this->belongsTo(Quote::class, 'quote_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
