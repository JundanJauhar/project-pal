<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Hps extends Model
{
    protected $table = 'hps';
    protected $primaryKey = 'hps_id';

    public $timestamps = false;

    protected $fillable = [
        'project_id',
        'hps_date',
        'total_amount',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'hps_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Get the project for this HPS
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    /**
     * Get the creator (user) of this HPS
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
