<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'code',
        'original_amount',
        'remaining_balance',
        'status',
        'issued_at',
        'expires_at',
        'issued_by',
        'issued_to_name',
        'issued_to_customer_id',
        'notes',
    ];

    protected $casts = [
        'issued_at' => 'timestamp',
        'expires_at' => 'date',
    ];

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function issuedToCustomer()
    {
        return $this->belongsTo(Customer::class, 'issued_to_customer_id');
    }

    public function usages()
    {
        return $this->hasMany(VoucherUsage::class, 'voucher_id');
    }
}
