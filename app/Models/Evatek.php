<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evatek extends Model
{
    protected $table = 'evatek';
    protected $primaryKey = 'evatek_id';

    public $timestamps = false;

    protected $fillable = [
        'project_id',
        'evaluated_by',
        'status',
        'evaluation_result',
    ];

    /**
     * Get the project for this evaluation
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    /**
     * Get the evaluator (user) for this evaluation
     */
    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }
}
