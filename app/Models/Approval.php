<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Approval extends Model
{
    protected $table = 'approvals';
    protected $primaryKey = 'approval_id';

    protected $fillable = [
        'module',
        'module_id',
        'approver_id',
        'status',
        'approval_document_link',
        'approval_notes',
        'approved_at',
        'approved_by',
    ];

    /**
     * User yang jadi approver (sekdir)
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id', 'user_id');
    }

    /**
     * User yang menekan tombol approve (sekdir)
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by', 'user_id');
    }

    /**
     * Relasi dinamis ke procurement/project
     * module: 'procurement', 'project'
     */
    public function moduleItem()
    {
        return $this->morphTo(null, 'module', 'module_id');
    }
}
