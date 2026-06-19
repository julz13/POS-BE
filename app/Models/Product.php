<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'sku',
        'barcode',
        'name',
        'description',
        'category_id',
        'brand',
        'unit',
        'cost_price',
        'selling_price',
        'tax_code',
        'stock',
        'reorder_level',
        'track_inventory',
        'allow_negative_stock',
        'status',
        'image_url',
        'created_by',
    ];

    protected $casts = [
        'track_inventory' => 'boolean',
        'allow_negative_stock' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function barcodes()
    {
        return $this->hasMany(ProductBarcode::class, 'product_id');
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'product_id');
    }

    public function goodsReceivedItems()
    {
        return $this->hasMany(GoodsReceivedItem::class, 'product_id');
    }

    public function inventoryLedger()
    {
        return $this->hasMany(InventoryLedger::class, 'product_id');
    }

    public function stockTakeItems()
    {
        return $this->hasMany(StockTakeItem::class, 'product_id');
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class, 'product_id');
    }

    public function quoteItems()
    {
        return $this->hasMany(QuoteItem::class, 'product_id');
    }

    public function salesOrderItems()
    {
        return $this->hasMany(SalesOrderItem::class, 'product_id');
    }

    public function returnItems()
    {
        return $this->hasMany(ReturnItem::class, 'product_id');
    }
}
