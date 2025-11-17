<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Procurement extends Model
{
    protected $table = 'procurement';
    protected $primaryKey = 'procurement_id';

    protected $fillable = [
        'project_id',
        'code_procurement',
        'name_procurement',
        'description',
        'department_procurement',
        'priority',
        'start_date',
        'end_date',
        'status_procurement',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the project for this procurement
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    /**
     * Get the department for this procurement
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_procurement', 'department_id');
    }

    /**
     * Get request procurements for this procurement
     */
    public function requestProcurements(): HasMany
    {
        return $this->hasMany(RequestProcurement::class, 'procurement_id', 'procurement_id');
    }

    /**
     * Get procurement progress for this procurement
     */
    public function procurementProgress(): HasMany
    {
        return $this->hasMany(ProcurementProgress::class, 'procurement_id', 'procurement_id');
    }

    /**
     * Get all vendors through request procurements
     */
    public function vendors()
    {
        return $this->hasManyThrough(
            Vendor::class,
            RequestProcurement::class,
            'procurement_id',
            'id_vendor',
            'procurement_id',
            'vendor_id'
        );
    }

    /**
     * Get all items through request procurements
     */
    public function items()
    {
        return $this->hasManyThrough(
            Item::class,
            RequestProcurement::class,
            'procurement_id',
            'request_procurement_id',
            'procurement_id',
            'request_id'
        );
    }
}
