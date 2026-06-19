<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'audit_log';
    protected $fillable = [
        'timestamp',
        'user_id',
        'user_name',
        'user_role',
        'action',
        'module',
        'description',
        'reference_type',
        'reference_id',
        'reference_number',
        'severity',
        'details',
        'ip_address',
    ];

    protected $casts = [
        'timestamp' => 'timestamp',
        'details' => 'json',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
