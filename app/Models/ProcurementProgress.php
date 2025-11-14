<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcurementProgress extends Model
{
    protected $table = 'procurement_progress';
    protected $primaryKey = 'progress_id';

    protected $fillable = [
        'request_id',
        'checkpoint_id',
        'status',
        'start_date',
        'end_date',
        'note',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function requestProcurement()
    {
        return $this->belongsTo(RequestProcurement::class, 'request_id', 'request_id');
    }

    public function checkpoint()
    {
        return $this->belongsTo(Checkpoint::class, 'checkpoint_id', 'point_id');
    }
}
