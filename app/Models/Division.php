<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    protected $table = 'divisions';
    protected $primaryKey = 'division_id';

    protected $fillable = [
        'division_name',
        'description',
    ];

    /**
     * Get users in this division
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'division_id', 'division_id');
    }

    /**
     * Get projects owned by this division
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'owner_division_id', 'division_id');
    }

    /**
     * Get checkpoints responsible by this division
     */
    public function checkpoints(): HasMany
    {
        return $this->hasMany(Checkpoint::class, 'responsible_division', 'division_id');
    }

    /**
     * Get request procurements from this division (PERBAIKAN #8)
     */
    public function requestProcurements(): HasMany
    {
        return $this->hasMany(RequestProcurement::class, 'department_id', 'division_id');
    }
}
