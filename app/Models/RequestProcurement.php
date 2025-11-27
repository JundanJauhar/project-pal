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
        'project_id',
    ];

    protected $casts = [
        'created_date' => 'date',
        'deadline_date' => 'date',
    ];

    /**
     * Get the procurement that owns this request
     */
    public function procurement(): BelongsTo
    {
        return $this->belongsTo(Procurement::class, 'procurement_id', 'procurement_id');
    }

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
        // Foreign key: vendor_id di request_procurement
        // Owner key: id_vendor di vendors (primary key)
        return $this->belongsTo(
            Vendor::class, 
            'vendor_id',    // foreign key di request_procurement
            'id_vendor'     // owner key di vendors (primary key)
        );
    }

    /**
     * Get the department for this request
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
    }

    /**
     * Get all items for this request
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'request_procurement_id', 'request_id');
    }

    /**
     * Calculate total amount from items
     */
    public function calculateTotalAmount()
    {
        return $this->items()->sum('total_price');
    }

    /**
     * Update total amount
     */
    public function updateTotalAmount()
    {
        $this->update([
            'total_amount' => $this->calculateTotalAmount()
        ]);
    }
}   