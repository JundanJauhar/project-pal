<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    protected $table = 'items';
    protected $primaryKey = 'item_id';

    // PERBAIKAN #13 - enable timestamps
    // public $timestamps = false;

    protected $fillable = [
        'request_procurement_id',
        'item_name',
        'item_description',
        'specification',
        'amount',
        'unit',
        'unit_price',
        'total_price',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'unit_price' => 'integer',
        'total_price' => 'integer',
        'approved_at' => 'datetime',
    ];

    protected $appends = ['quantity', 'estimated_price'];

    /**
     * Get the request procurement for this item
     */
    public function requestProcurement(): BelongsTo
    {
        return $this->belongsTo(RequestProcurement::class, 'request_procurement_id', 'request_id');
    }

    /**
     * Get the user who approved this item
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by', 'user_id');
    }

    /**
     * Accessor for quantity (alias for amount)
     */
    public function getQuantityAttribute()
    {
        return $this->amount;
    }

    /**
     * Accessor for estimated_price (alias for unit_price)
     */
    public function getEstimatedPriceAttribute()
    {
        return $this->unit_price;
    }

    /**
     * Check if item is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Scope untuk filter approved items
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope untuk filter not approved items
     */
    public function scopeNotApproved($query)
    {
        return $query->where('status', 'not_approved');
    }

    public function inspectionReports()
{
    return $this->hasMany(\App\Models\InspectionReport::class, 'item_id', 'item_id');
}
}
