<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentSchedule extends Model
{
    protected $table = 'payment_schedules';
    protected $primaryKey = 'payment_schedule_id';

    protected $fillable = [
        'project_id',
        'contract_id',
        'payment_type',
        'amount',
        'percentage',
        'due_date',
        'payment_date',
        'status',
        'verified_by_accounting',
        'verified_by_treasury',
        'verified_at_accounting',
        'verified_at_treasury',
        'notes',
        'attachment_path',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'percentage' => 'decimal:2',
        'due_date' => 'date',
        'payment_date' => 'date',
        'verified_at_accounting' => 'datetime',
        'verified_at_treasury' => 'datetime',
    ];

    /**
     * Get the project for this payment
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    /**
     * Get the contract for this payment
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class, 'contract_id', 'contract_id');
    }

    /**
     * Get accounting user who verified
     */
    public function accountingVerifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_accounting');
    }

    /**
     * Get treasury user who verified
     */
    public function treasuryVerifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_treasury');
    }
}
