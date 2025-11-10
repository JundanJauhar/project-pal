<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcurementProgress extends Model
{
    protected $table = 'procurement_progress';
    protected $primaryKey = 'progress_id';

    protected $fillable = [
        'permintaan_pengadaan_id',
        'titik_id',
        'status_progress',
        'tanggal_mulai',
        'tanggal_selesai',
        'user_id',
        'catatan',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    /**
     * Get the request procurement for this progress
     */
    public function requestProcurement(): BelongsTo
    {
        return $this->belongsTo(RequestProcurement::class, 'permintaan_pengadaan_id', 'request_id');
    }

    /**
     * Get the checkpoint for this progress
     */
    public function checkpoint(): BelongsTo
    {
        return $this->belongsTo(Checkpoint::class, 'titik_id', 'point_id');
    }

    /**
     * Get the user who made this progress
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
