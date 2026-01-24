<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvatekItem extends Model
{
    protected $table = 'evatek_items';
    protected $primaryKey = 'evatek_id';

    protected $fillable = [
        'item_id',
        'procurement_id',
        'vendor_id',
        'pic_evatek',
        'evatek_status',
        'start_date',
        'target_date',
        'current_revision',
        'status',
        'current_date',
        'sc_design_link',
        'approved_at',
        'not_approved_at',
        'log',
    ];

    protected $casts = [
        'current_date' => 'date',
        'start_date' => 'date',
        'target_date' => 'date',
        'approved_at' => 'datetime',
        'not_approved_at' => 'datetime',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id', 'item_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id_vendor');
    }

    public function procurement(): BelongsTo
    {
        return $this->belongsTo(Procurement::class, 'procurement_id', 'procurement_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(EvatekRevision::class, 'evatek_id', 'evatek_id')
            ->orderBy('revision_id', 'ASC');
    }

    public function latestRevision()
    {
        return $this->hasOne(EvatekRevision::class, 'evatek_id', 'evatek_id')
            ->latest('revision_id');
    }

    public function getPicLabelAttribute()
    {
        $labels = [
            'EO' => 'Engineering Officer',
            'HC' => 'Head of Construction',
            'MO' => 'Material Officer',
            'HO' => 'Head of Operations',
            'SEWACO' => 'SEWACO',
        ];
        return $labels[$this->pic_evatek] ?? '-';
    }

    public function getEvatekStatusLabelAttribute()
    {
        $labels = [
            'evatek-vendor' => 'Waiting for Vendor',
            'evatek-desain' => 'Waiting for Design',
            'evatek-complete' => 'Complete',
        ];
        return $labels[$this->evatek_status] ?? 'Unknown';
    }

    public function getEvatekStatusColorAttribute()
    {
        $colors = [
            'evatek-vendor' => '#FF9800',      // Orange - Vendor pending
            'evatek-desain' => '#2196F3',      // Blue - Design pending
            'evatek-complete' => '#4CAF50',    // Green - Complete
        ];
        return $colors[$this->evatek_status] ?? '#999';
    }

    public function hasVendorLink()
    {
        $latestRevision = $this->latestRevision()->first();
        return $latestRevision && !empty($latestRevision->vendor_link);
    }

    public function hasDesignLink()
    {
        $latestRevision = $this->latestRevision()->first();
        return $latestRevision && !empty($latestRevision->design_link);
    }

    public function updateEvatekStatus()
    {
        $hasVendor = $this->hasVendorLink();
        $hasDesign = $this->hasDesignLink();

        if (!$hasVendor) {
            $this->evatek_status = 'evatek-vendor';
        } elseif (!$hasDesign) {
            $this->evatek_status = 'evatek-desain';
        } else {
            $this->evatek_status = 'evatek-complete';
        }

        $this->save();
    }
}