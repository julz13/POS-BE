<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';
    protected $fillable = [
        'store_name',
        'store_address',
        'store_phone',
        'store_email',
        'store_tin',
        'currency',
        'timezone',
        'receipt_header',
        'receipt_footer',
        'tax_enabled',
        'tax_name',
        'tax_rate',
        'tax_type',
        'max_cashier_discount_pct',
        'max_manager_discount_pct',
        'require_manager_approval_above_pct',
        'round_to_two_decimals',
        'allow_negative_stock',
        'require_shift_for_sales',
        'show_cashier_on_receipt',
        'show_customer_on_receipt',
        'show_tax_breakdown_on_receipt',
        'show_discount_breakdown_on_receipt',
        'loyalty_points_per_peso',
        'loyalty_redeem_rate',
    ];

    protected $casts = [
        'tax_enabled' => 'boolean',
        'round_to_two_decimals' => 'boolean',
        'allow_negative_stock' => 'boolean',
        'require_shift_for_sales' => 'boolean',
        'show_cashier_on_receipt' => 'boolean',
        'show_customer_on_receipt' => 'boolean',
        'show_tax_breakdown_on_receipt' => 'boolean',
        'show_discount_breakdown_on_receipt' => 'boolean',
    ];
}
