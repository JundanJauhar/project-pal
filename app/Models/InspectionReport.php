<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InspectionReport extends Model
{
    protected $table = 'inspection_reports';
    protected $primaryKey = 'inspection_id';

    protected $fillable = [
        'project_id',
        'item_id',
        'inspection_date',
        'inspector_id',
        'result',
        'findings',
        'notes',
        'attachment_path',
        'ncr_required',
    ];

    protected $casts = [
        'inspection_date' => 'date',
        'ncr_required' => 'boolean',
    ];

    /**
     * Get the project for this inspection
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    /**
     * Get the item being inspected
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id', 'item_id');
    }

    /**
     * Get the inspector (QA user)
     */
    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id', 'user_id');
    }

    /**
     * Get NCR reports for this inspection
     */
    public function ncrReports(): HasMany
    {
        return $this->hasMany(NcrReport::class, 'inspection_id', 'inspection_id');
    }
}
