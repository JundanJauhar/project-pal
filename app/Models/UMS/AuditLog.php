<?php

namespace App\Models\UMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_logs';

    // Pastikan field yang benar ada di migration DB Anda:
    protected $fillable = [
        'actor_user_id',
        'action',
        'target_table',
        'target_id',
        'details',
        'created_at',
    ];

    // Cast agar created_at menjadi Carbon, details menjadi array
    protected $casts = [
        'details'    => 'array',
        'created_at' => 'datetime',
        'target_id'  => 'integer',
    ];

    // Jika table hanya punya created_at (useCurrent) dan tidak punya updated_at
    public $timestamps = false;

    /**
     * Relasi ke user (actor)
     */
    public function actor()
    {
        return $this->belongsTo(\App\Models\User::class, 'actor_user_id', 'user_id');
    }

    /**
     * Helper accessor: safe get IP dari details
     */
    public function getIpAttribute()
    {
        if (is_array($this->details)) {
            return $this->details['ip'] ?? $this->details['ip_address'] ?? null;
        }
        return null;
    }

    /**
     * Helper accessor: safe get user agent / device dari details
     */
    public function getUserAgentAttribute()
    {
        if (is_array($this->details)) {
            return $this->details['ua'] ?? $this->details['user_agent'] ?? null;
        }
        return null;
    }
}
