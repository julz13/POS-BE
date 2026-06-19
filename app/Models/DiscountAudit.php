<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountAudit extends Model
{
    protected $table = 'discount_audit';
    protected $fillable = [
        'transaction_id',
        'discount_type_id',
        'type_name',
        'scope',
        'transaction_item_id',
        'product_name',
        'applied_by',
        'applied_at',
        'discount_pct',
        'original_amount',
        'discount_amount',
        'final_amount',
        'reason',
        'id_number',
        'overridden_by',
    ];

    protected $casts = [
        'applied_at' => 'timestamp',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function discountType()
    {
        return $this->belongsTo(DiscountType::class, 'discount_type_id');
    }

    public function transactionItem()
    {
        return $this->belongsTo(TransactionItem::class, 'transaction_item_id');
    }

    public function appliedBy()
    {
        return $this->belongsTo(User::class, 'applied_by');
    }

    public function overriddenBy()
    {
        return $this->belongsTo(User::class, 'overridden_by');
    }
}
