<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestProcurement extends Model
{
    use SoftDeletes;

    protected $table = 'request_procurement';
    protected $primaryKey = 'request_id';

    protected $fillable = [
        'project_id',
        'item_id',
        'vendor_id',
        'request_name',
        'created_date',
        'deadline_date',
        'request_status',
        'applicant_department',
    ];

    protected $casts = [
        'created_date' => 'date',
        'deadline_date' => 'date',
    ];

    /**
     * Get the project for this request
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    /**
     * Get the vendor for this request
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id_vendor');
    }

    /**
     * Get the applicant division for this request
     */
    public function applicantDivision(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'applicant_department', 'divisi_id');
    }

    /**
     * Get items for this request
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'request_procurement_id', 'request_id');
    }

    /**
     * Get procurement progress for this request
     */
    public function procurementProgress(): HasMany
    {
        return $this->hasMany(ProcurementProgress::class, 'permintaan_pengadaan_id', 'request_id');
    }

    /**
     * Get negotiations for this request
     */
    public function negotiations(): HasMany
    {
        return $this->hasMany(Negotiation::class, 'request_id', 'request_id');
    }
}
