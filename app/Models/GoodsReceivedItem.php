<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsReceivedItem extends Model
{
    protected $table = 'goods_received_items';
    protected $fillable = [
        'grn_id',
        'product_id',
        'po_item_id',
        'received_qty',
        'unit_cost',
        'subtotal',
        'expiry_date',
        'batch_number',
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function goodsReceivedNote()
    {
        return $this->belongsTo(GoodsReceivedNote::class, 'grn_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'po_item_id');
    }
}
