<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Approval extends Model
{
    protected $table = 'approvals';
    protected $primaryKey = 'approval_id';

    public $timestamps = false;

    protected $fillable = [
        'module',
        'module_id',
        'approver_id',
        'status',
    ];

    /**
     * Get the approver (user) for this approval
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
