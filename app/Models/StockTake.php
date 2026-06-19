<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTake extends Model
{
    protected $table = 'stock_takes';
    protected $fillable = [
        'st_number',
        'date',
        'status',
        'notes',
        'created_by',
        'completed_at',
        'posted_at',
        'posted_by',
    ];

    protected $casts = [
        'date' => 'date',
        'completed_at' => 'timestamp',
        'posted_at' => 'timestamp',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function postedBy()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function items()
    {
        return $this->hasMany(StockTakeItem::class, 'stock_take_id');
    }
}
