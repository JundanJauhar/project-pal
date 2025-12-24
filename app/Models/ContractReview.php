<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractReview extends Model
{
    use HasFactory;

    protected $table = 'contract_reviews';
    protected $primaryKey = 'contract_review_id';

    protected $fillable = [
        'procurement_id',
        'vendor_id',
        'project_id',
        'start_date',
        'current_revision',
        'date_sent_to_vendor',
        'date_vendor_feedback',
        'remarks',
        'log',
        'result',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'date_sent_to_vendor' => 'date',
        'date_vendor_feedback' => 'date',
    ];

    /**
     * Relationship to Procurement
     */
    public function procurement()
    {
        return $this->belongsTo(Procurement::class, 'procurement_id', 'procurement_id');
    }

    /**
     * Relationship to Vendor
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id_vendor');
    }

    /**
     * Relationship to Project
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    /**
     * Get revision revisions history
     */
    public function revisions()
    {
        return $this->hasMany(ContractReviewRevision::class, 'contract_review_id', 'contract_review_id')
            ->orderBy('revision_code', 'desc');
    }

    /**
     * Get formatted revision number
     */
    public function getFormattedRevisionAttribute()
    {
        return $this->current_revision;
    }

    /**
     * Check if review is waiting for vendor feedback
     */
    public function isWaitingFeedback()
    {
        return $this->status === 'waiting_feedback';
    }

    /**
     * Check if review is completed
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Calculate review duration
     */
    public function getDurationInDays()
    {
        if (!$this->date_vendor_feedback || !$this->date_sent_to_vendor) {
            return null;
        }

        return $this->date_sent_to_vendor->diffInDays($this->date_vendor_feedback);
    }
}
