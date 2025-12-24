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

    protected $with = ['procurementProgress.checkpoint', 'requestProcurements.vendor'];


    // Appends untuk akses mudah di blade
    protected $appends = ['current_checkpoint'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_procurement', 'department_id');
    }

    public function requestProcurements(): HasMany
    {
        return $this->hasMany(RequestProcurement::class, 'procurement_id', 'procurement_id');
    }

    public function procurementProgress(): HasMany
    {
        return $this->hasMany(ProcurementProgress::class, 'procurement_id', 'procurement_id');
    }

    public function negotiations()
    {
        return $this->hasMany(Negotiation::class, 'procurement_id', 'procurement_id');
    }

    public function contractReviews()
    {
        return $this->hasMany(ContractReview::class, 'procurement_id', 'procurement_id');
    }

    public function inquiryQuotations()
    {
        return $this->hasMany(InquiryQuotation::class, 'procurement_id', 'procurement_id');
    }

    public function evatekItems()
    {
        return $this->hasMany(EvatekItem::class, 'procurement_id', 'procurement_id');
    }

    public function pengadaanOcs()
    {
        return $this->hasMany(PengadaanOc::class, 'procurement_id', 'procurement_id');
    }

    public function pengesahanKontraks()
    {
        return $this->hasMany(PengesahanKontrak::class, 'procurement_id', 'procurement_id');
    }

    public function kontraks()
    {
        return $this->hasMany(Kontrak::class, 'procurement_id', 'procurement_id');
    }
    
    public function pembayarans()
    {
        return $this->hasMany(Pembayaran::class, 'procurement_id', 'procurement_id');
    }

    public function jaminans()
    {
        return $this->hasMany(JaminanPembayaran::class, 'procurement_id', 'procurement_id');
    }

    public function materialDeliveries()
    {
        return $this->hasMany(MaterialDelivery::class, 'procurement_id', 'procurement_id');
    }

    public function getCurrentCheckpointAttribute()
    {
        // Only applicable for in_progress status
        if ($this->status_procurement !== 'in_progress') {
            return null;
        }

        // Use already loaded relation to avoid N+1
        $progressCollection = $this->relationLoaded('procurementProgress')
            ? $this->procurementProgress
            : $this->procurementProgress()->with('checkpoint')->get();

        // Find the checkpoint that is currently in_progress
        $currentProgress = $progressCollection
            ->filter(function ($progress) {
                return $progress->status === 'in_progress' && $progress->checkpoint;
            })
            ->sortBy(function ($progress) {
                return $progress->checkpoint->point_sequence ?? 999;
            })
            ->first();

        // If found in_progress checkpoint, return its name
        if ($currentProgress && $currentProgress->checkpoint) {
            return $currentProgress->checkpoint->point_name;
        }

        // Fallback: Find the LAST completed checkpoint
        // This means the next checkpoint should be "in_progress" but hasn't been created yet
        $lastCompleted = $progressCollection
            ->filter(function ($progress) {
                return $progress->status === 'completed' && $progress->checkpoint;
            })
            ->sortByDesc(function ($progress) {
                return $progress->checkpoint->point_sequence ?? 0;
            })
            ->first();

        if ($lastCompleted && $lastCompleted->checkpoint) {
            // Get the next checkpoint after the last completed one
            $nextCheckpointSequence = $lastCompleted->checkpoint->point_sequence + 1;
            $nextCheckpoint = \App\Models\Checkpoint::where('point_sequence', $nextCheckpointSequence)->first();

            if ($nextCheckpoint) {
                return $nextCheckpoint->point_name;
            }

            // If no next checkpoint, return the last completed checkpoint name
            return $lastCompleted->checkpoint->point_name;
        }

        // If no progress at all, return the first checkpoint
        $firstCheckpoint = \App\Models\Checkpoint::orderBy('point_sequence')->first();
        return $firstCheckpoint ? $firstCheckpoint->point_name : null;
    }

    /**
     * Get progress percentage based on completed checkpoints
     */
    public function getProgressPercentageAttribute()
    {
        $totalCheckpoints = \App\Models\Checkpoint::count();
        if ($totalCheckpoints === 0) {
            return 0;
        }

        $completedCheckpoints = $this->procurementProgress()
            ->where('status', 'completed')
            ->count();

        return round(($completedCheckpoints / $totalCheckpoints) * 100, 2);
    }

    /**
     * DEPRECATED: This accessor is not aligned with actual status_procurement enum
     * Use status_procurement directly instead
     * 
     * Actual enum values: 'in_progress', 'completed', 'cancelled'
     * This accessor returns: 'not_started', 'in_progress', 'completed', 'rejected'
     */
    public function getAutoStatusAttribute()
    {
        // ⚠️ WARNING: This method returns values that don't match the database enum
        // It's kept for backward compatibility but should not be used

        $progressCollection = $this->relationLoaded('procurementProgress')
            ? $this->procurementProgress
            : $this->procurementProgress()->get();

        // Check for blocked/rejected status
        $blocked = $progressCollection->where('status', 'blocked')->isNotEmpty();

        if ($blocked) {
            return 'rejected'; // Maps to 'cancelled' in real status
        }

        $totalCheckpoint = \App\Models\Checkpoint::count();
        $completed = $progressCollection->where('status', 'completed')->count();

        if ($completed === 0) {
            return 'not_started'; // This status doesn't exist in enum!
        }

        if ($completed >= $totalCheckpoint) {
            return 'completed';
        }

        return 'in_progress';
    }

    public function vendors()
    {
        return $this->hasManyThrough(
            Vendor::class,
            RequestProcurement::class,
            'procurement_id',    // FK di request_procurement
            'id_vendor',         // FK di vendors
            'procurement_id',    // Local key di procurement
            'vendor_id'          // Local key di request_procurement
        );
    }

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
