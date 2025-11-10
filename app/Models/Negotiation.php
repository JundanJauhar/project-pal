<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Negotiation extends Model
{
    protected $table = 'negotiations';
    protected $primaryKey = 'negotiation_id';

    public $timestamps = false;

    protected $fillable = [
        'request_id',
        'status',
        'notes',
    ];

    /**
     * Get the request procurement for this negotiation
     */
    public function requestProcurement(): BelongsTo
    {
        return $this->belongsTo(RequestProcurement::class, 'request_id', 'request_id');
    }
}
