<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Checkpoint extends Model
{
    protected $table = 'checkpoints';
    protected $primaryKey = 'point_id';

    protected $fillable = [
        'point_name',
        'point_sequence',
        'responsible_division',
        'is_true',
    ];

    protected $casts = [
        'is_true' => 'boolean',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'responsible_division', 'department_id');
    }

    public function procurementProgress()
    {
        return $this->hasMany(ProcurementProgress::class, 'checkpoint_id', 'point_id');
    }
}
