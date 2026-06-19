<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherUsage extends Model
{
    protected $table = 'voucher_usages';
    protected $fillable = [
        'voucher_id',
        'transaction_id',
        'date',
        'amount_used',
        'balance_after',
        'redeemed_by',
    ];

    protected $casts = [
        'date' => 'timestamp',
    ];

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function redeemedBy()
    {
        return $this->belongsTo(User::class, 'redeemed_by');
    }
}
