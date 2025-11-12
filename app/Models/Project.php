<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Project extends Model
{
    protected $table = 'projects';
    protected $primaryKey = 'project_id';

    protected $fillable = [
        'code_project',
        'name_project',
        'description',
        'owner_division_id',
        'priority',
        'start_date',
        'end_date',
        'status_project',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the division that owns the project
     */
    public function ownerDivision(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'owner_division_id', 'divisi_id');
    }

    /**
     * Get contracts for this project
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'project_id', 'project_id');
    }

    /**
     * Get the latest Evatek record for this project
     */
    public function evatek(): HasOne
    {
        return $this->hasOne(Evatek::class, 'project_id', 'project_id')
            ->latestOfMany('evatek_id');
    }

    /**
     * Get the primary vendor associated through contracts
     */
    public function vendor(): HasOneThrough
    {
        return $this->hasOneThrough(
            Vendor::class,
            Contract::class,
            'project_id',    // Foreign key on contracts table...
            'id_vendor',     // Foreign key on vendors table...
            'project_id',    // Local key on projects table...
            'vendor_id'      // Local key on contracts table...
        );
    }

    /**
     * Get HPS for this project
     */
    public function hps(): HasMany
    {
        return $this->hasMany(Hps::class, 'project_id', 'project_id');
    }

    /**
     * Get evaluations for this project
     */
    public function evaluations(): HasMany
    {
        return $this->hasMany(Evatek::class, 'project_id', 'project_id');
    }

    /**
     * Get request procurements for this project
     */
    public function requestProcurements(): HasMany
    {
        return $this->hasMany(RequestProcurement::class, 'project_id', 'project_id');
    }
}
