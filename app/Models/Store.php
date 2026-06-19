<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'owner_id',
        'name',
        'code',
        'address',
        'phone',
        'email',
        'city',
        'province',
        'postal_code',
        'latitude',
        'longitude',
        'status',
        'opened_date',
        'closed_date',
        'notes',
    ];

    protected $casts = [
        'opened_date' => 'datetime',
        'closed_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    // Relationships
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function categories()
    {
        return $this->hasMany(Category::class, 'store_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'store_id');
    }

    public function suppliers()
    {
        return $this->hasMany(Supplier::class, 'store_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'store_id');
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class, 'store_id');
    }

    public function purchases()
    {
        return $this->hasMany(PurchaseOrder::class, 'store_id');
    }

    public function grns()
    {
        return $this->hasMany(GoodsReceivedNote::class, 'store_id');
    }

    public function inventory()
    {
        return $this->hasMany(InventoryLedger::class, 'store_id');
    }

    public function stockTakes()
    {
        return $this->hasMany(StockTake::class, 'store_id');
    }

    public function vouchers()
    {
        return $this->hasMany(Voucher::class, 'store_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'store_id');
    }
}
