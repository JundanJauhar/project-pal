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
        'project_code',
        'project_name',
        'description',
        'owner_division_id',
        'priority',
        'start_date',
        'end_date',
        'status_project',
        'review_notes',
        'review_documents',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'review_documents' => 'array',
    ];

    /**
     * Get the division that owns the project
     */
    public function ownerDivision(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'owner_division_id', 'division_id');
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
     * Get procurements for this project
     */
    public function procurements(): HasMany
    {
        return $this->hasMany(Procurement::class, 'project_id', 'project_id');
    }

    /**
     * Get request procurements for this project (through procurements)
     */
    public function requests(): HasMany
    {
        return $this->hasMany(RequestProcurement::class, 'project_id', 'project_id');
    }
}
