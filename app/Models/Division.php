<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    protected $table = 'divisions';
    protected $primaryKey = 'divisi_id';

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get users in this division
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'division_id', 'divisi_id');
    }

    /**
     * Get projects owned by this division
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'owner_division_id', 'divisi_id');
    }

    /**
     * Get checkpoints responsible by this division
     */
    public function checkpoints(): HasMany
    {
        return $this->hasMany(Checkpoint::class, 'responsible_division', 'divisi_id');
    }

    /**
     * Get request procurements from this division
     */
    public function requestProcurements(): HasMany
    {
        return $this->hasMany(RequestProcurement::class, 'applicant_department', 'divisi_id');
    }
}
