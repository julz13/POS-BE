<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'first_name',
        'last_name',
        'phone',
        'email',
        'address',
        'customer_type',
        'credit_limit',
        'current_balance',
        'store_credit_balance',
        'loyalty_points',
        'total_spent',
        'total_transactions',
        'status',
        'notes',
        'last_visit_at',
    ];

    protected $casts = [
        'last_visit_at' => 'date',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'customer_id');
    }

    public function returns()
    {
        return $this->hasMany(Return_::class, 'customer_id');
    }

    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class, 'customer_id');
    }

    public function quotes()
    {
        return $this->hasMany(Quote::class, 'customer_id');
    }

    public function vouchers()
    {
        return $this->hasMany(Voucher::class, 'issued_to_customer_id');
    }
}
