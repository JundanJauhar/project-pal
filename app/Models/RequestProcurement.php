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
        'procurement_id',
        'vendor_id',
        'request_name',
        'created_date',
        'deadline_date',
        'request_status',
        'department_id',
        'project_id', // pastikan kolom ini ada di migration
    ];

    protected $casts = [
        'created_date' => 'date',
        'deadline_date' => 'date',
    ];

    /**
     * Get the procurement for this request
     */
    public function procurement(): BelongsTo
    {
        return $this->belongsTo(Procurement::class, 'procurement_id', 'procurement_id');
    }

    /**
     * Get the vendor for this request
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id_vendor');
    }

    /**
     * Get the department for this request
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
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
}
