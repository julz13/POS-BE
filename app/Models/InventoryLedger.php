<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryLedger extends Model
{
    protected $table = 'inventory_ledger';
    protected $fillable = [
        'product_id',
        'date',
        'type',
        'reference_type',
        'reference_id',
        'reference_number',
        'qty_in',
        'qty_out',
        'balance_after',
        'unit_cost',
        'user_id',
        'notes',
    ];

    protected $casts = [
        'date' => 'timestamp',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
