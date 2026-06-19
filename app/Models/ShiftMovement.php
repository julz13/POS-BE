<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftMovement extends Model
{
    protected $table = 'shift_movements';
    protected $fillable = [
        'shift_id',
        'type',
        'amount',
        'reason',
        'recorded_by',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'timestamp',
    ];

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
