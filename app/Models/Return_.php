<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Return_ extends Model
{
    protected $table = 'returns';
    protected $fillable = [
        'return_number',
        'transaction_id',
        'date',
        'customer_id',
        'customer_name',
        'return_type',
        'reason',
        'refund_amount',
        'refund_method',
        'status',
        'stock_restored',
        'created_by',
        'approved_by',
        'approved_at',
        'completed_by',
        'completed_at',
        'rejected_by',
        'rejected_at',
        'reject_reason',
        'notes',
    ];

    protected $casts = [
        'date' => 'timestamp',
        'approved_at' => 'timestamp',
        'completed_at' => 'timestamp',
        'rejected_at' => 'timestamp',
        'stock_restored' => 'boolean',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function items()
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
    }
}
