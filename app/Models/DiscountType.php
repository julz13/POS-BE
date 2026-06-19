<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountType extends Model
{
    protected $table = 'discount_types';
    protected $fillable = [
        'store_id',
        'code',
        'name',
        'description',
        'default_pct',
        'max_pct',
        'is_custom_pct',
        'requires_id',
        'requires_reason',
        'requires_override',
        'is_active',
        'sort_order',
        'color',
    ];

    protected $casts = [
        'is_custom_pct' => 'boolean',
        'requires_id' => 'boolean',
        'requires_reason' => 'boolean',
        'requires_override' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function discountAudits()
    {
        return $this->hasMany(DiscountAudit::class, 'discount_type_id');
    }
}
