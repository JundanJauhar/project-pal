<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvatekRevision extends Model
{
    protected $table = 'evatek_revisions';
    protected $primaryKey = 'revision_id';

    protected $fillable = [
        'evatek_id',
        'revision_code',
        'vendor_link',
        'design_link',
        'status',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /** Relation to EvatekItem */
    public function evatek(): BelongsTo
    {
        return $this->belongsTo(EvatekItem::class, 'evatek_id', 'evatek_id');
    }
}
