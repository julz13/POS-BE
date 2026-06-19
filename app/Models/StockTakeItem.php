<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTakeItem extends Model
{
    protected $table = 'stock_take_items';
    protected $fillable = [
        'stock_take_id',
        'product_id',
        'system_qty',
        'counted_qty',
        'variance',
        'notes',
    ];

    public function stockTake()
    {
        return $this->belongsTo(StockTake::class, 'stock_take_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
