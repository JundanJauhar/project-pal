<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NcrReport extends Model
{
    protected $table = 'ncr_reports';
    protected $primaryKey = 'ncr_id';

    protected $fillable = [
        'ncr_number',
        'inspection_id',
        'project_id',
        'item_id',
        'ncr_date',
        'nonconformance_description',
        'severity',
        'root_cause',
        'corrective_action',
        'preventive_action',
        'assigned_to',
        'target_completion_date',
        'actual_completion_date',
        'status',
        'created_by',
        'verified_by',
        'verified_at',
        'attachment_path',
    ];

    protected $casts = [
        'ncr_date' => 'date',
        'target_completion_date' => 'date',
        'actual_completion_date' => 'date',
        'verified_at' => 'datetime',
    ];

    /**
     * Boot method to auto-generate NCR number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->ncr_number)) {
                $model->ncr_number = 'NCR-' . date('Ymd') . '-' . str_pad(static::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Get the inspection report
     */
    public function inspection(): BelongsTo
    {
        return $this->belongsTo(InspectionReport::class, 'inspection_id', 'inspection_id');
    }

    /**
     * Get the project
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    /**
     * Get the item
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id', 'item_id');
    }

    /**
     * Get the creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    /**
     * Get the verifier
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by', 'user_id');
    }

    /**
     * Get the assigned user
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to', 'user_id');
    }
}
