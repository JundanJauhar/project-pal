<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Checkpoint extends Model
{
    protected $table = 'checkpoints';
    protected $primaryKey = 'point_id';

    protected $fillable = [
        'point_name',
        'point_sequence',
        'responsible_division',
        'is_final',
    ];

    protected $casts = [
        'is_final' => 'boolean',
    ];

    /**
     * Get the division responsible for this checkpoint
     */
    public function responsibleDivision(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'responsible_division', 'division_id');
    }

    /**
     * Get procurement progress for this checkpoint
     */
    public function procurementProgress(): HasMany
    {
        return $this->hasMany(ProcurementProgress::class, 'checkpoint_id', 'point_id');
    }
}
