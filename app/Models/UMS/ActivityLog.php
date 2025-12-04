<?php

namespace App\Models\UMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_logs';
    protected $primaryKey = 'id';

    protected $fillable = [
        'actor_user_id',
        'module',
        'action',
        'target_id',
        'details',
        'created_at',
    ];

    protected $casts = [
        'details' => 'array',
        'created_at' => 'datetime',
    ];

    public $timestamps = false;

    public function actor()
    {
        return $this->belongsTo(\App\Models\User::class, 'actor_user_id', 'user_id');
    }

    public function getIpAttribute()
    {
        return $this->details['ip'] ?? null;
    }

    public function getUserAgentAttribute()
    {
        return $this->details['ua'] ?? null;
    }
}
