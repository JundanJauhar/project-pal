<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcurementProgress extends Model
{
    protected $table = 'procurement_progress';
    protected $primaryKey = 'progress_id';

    protected $fillable = [
        'procurement_id',
        'checkpoint_id',
        'user_id',
        'status',
        'start_date',
        'end_date',
        'note',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    /**
     * Get the procurement for this progress
     */
    public function procurement(): BelongsTo
    {
        return $this->belongsTo(Procurement::class, 'procurement_id', 'procurement_id');
    }

    /**
     * Get the checkpoint for this progress
     */
    public function checkpoint(): BelongsTo
    {
        return $this->belongsTo(Checkpoint::class, 'checkpoint_id', 'point_id');
    }

    /**
     * Get the user who made this progress
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
