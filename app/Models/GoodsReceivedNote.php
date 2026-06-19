<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsReceivedNote extends Model
{
    protected $table = 'goods_received_notes';
    protected $fillable = [
        'grn_number',
        'po_id',
        'supplier_id',
        'status',
        'received_date',
        'invoice_number',
        'total_cost',
        'notes',
        'received_by',
        'posted_at',
    ];

    protected $casts = [
        'received_date' => 'date',
        'posted_at' => 'timestamp',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items()
    {
        return $this->hasMany(GoodsReceivedItem::class, 'grn_id');
    }
}
