<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $fillable = [
        'shift_number',
        'cashier_id',
        'opened_at',
        'closed_at',
        'closed_by',
        'opening_cash',
        'cash_sales',
        'change_given',
        'cash_in_total',
        'cash_out_total',
        'expected_cash',
        'counted_cash',
        'variance',
        'notes',
        'status',
    ];

    protected $casts = [
        'opened_at' => 'timestamp',
        'closed_at' => 'timestamp',
    ];

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'shift_id');
    }

    public function movements()
    {
        return $this->hasMany(ShiftMovement::class, 'shift_id');
    }
}
