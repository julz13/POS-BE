<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $table = 'purchase_order_items';
    protected $fillable = [
        'po_id',
        'product_id',
        'quantity',
        'unit_cost',
        'subtotal',
        'received_qty',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function goodsReceivedItems()
    {
        return $this->hasMany(GoodsReceivedItem::class, 'po_item_id');
    }
}
