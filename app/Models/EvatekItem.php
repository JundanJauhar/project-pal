<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvatekItem extends Model
{
    protected $table = 'evatek_items';
    protected $primaryKey = 'evatek_id';

    protected $fillable = [
        'item_id',
        'procurement_id',
        'vendor_id',
        'start_date',
        'target_date',
        'current_revision',
        'status',
        'current_date',
        'approved_at',
        'not_approved_at',
        'log',
    ];

    protected $casts = [
        'current_date' => 'date',
        'start_date' => 'date',
        'target_date' => 'date',
        'approved_at' => 'datetime',
        'not_approved_at' => 'datetime',
    ];

    /** Item relationship */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id', 'item_id');
    }

    /** Project relationship */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    /** Vendor relationship */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id_vendor');
    }

    /** Procurement relationship */
    public function procurement(): BelongsTo
    {
        return $this->belongsTo(Procurement::class, 'procurement_id', 'procurement_id');
    }

    /** All revisions for this Evatek */
    public function revisions(): HasMany
    {
        return $this->hasMany(EvatekRevision::class, 'evatek_id', 'evatek_id')
            ->orderBy('revision_id', 'ASC');
    }

    /** Get latest revision */
    public function latestRevision()
    {
        return $this->hasOne(EvatekRevision::class, 'evatek_id', 'evatek_id')
            ->latest('revision_id');
    }
}
