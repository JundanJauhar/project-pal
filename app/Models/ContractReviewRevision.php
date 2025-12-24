<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractReviewRevision extends Model
{
    use HasFactory;

    protected $table = 'contract_review_revisions';
    protected $primaryKey = 'contract_review_revision_id';

    protected $fillable = [
        'contract_review_id',
        'revision_code',
        'vendor_link',
        'sc_link',
        'date_sent_to_vendor',
        'date_vendor_feedback',
        'date_result',
        'result',
        'created_by',
    ];

    protected $casts = [
        'date_sent_to_vendor' => 'date',
        'date_vendor_feedback' => 'date',
        'date_result' => 'date',
    ];

    /**
     * Relationship to ContractReview
     */
    public function contractReview()
    {
        return $this->belongsTo(ContractReview::class, 'contract_review_id', 'contract_review_id');
    }

    /**
     * Relationship to User (created by)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }
}
