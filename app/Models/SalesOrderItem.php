<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrderItem extends Model
{
    protected $table = 'sales_order_items';
    protected $fillable = [
        'so_id',
        'product_id',
        'product_name',
        'unit',
        'quantity',
        'unit_price',
        'line_discount_pct',
        'subtotal',
    ];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'so_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
