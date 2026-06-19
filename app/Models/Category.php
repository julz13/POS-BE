<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'store_id',
        'name',
        'description',
        'status',
        'sort_order',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}
