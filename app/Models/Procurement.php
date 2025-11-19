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

    protected $appends = ['auto_status', 'current_checkpoint'];

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

    public function getAutoStatusAttribute()
    {
        // ✅ Gunakan relasi yang sudah di-load, bukan query baru
        $progressCollection = $this->relationLoaded('procurementProgress') 
            ? $this->procurementProgress 
            : $this->procurementProgress()->get();

        // Cek apakah ada yang ditolak
        $rejected = $progressCollection->where('status', 'rejected')->isNotEmpty();
        
        if ($rejected) {
            return 'rejected';
        }

        $totalCheckpoint = \App\Models\Checkpoint::count();
        $completed = $progressCollection->where('status', 'completed')->count();

        if ($completed === 0) {
            return 'not_started';
        }

        if ($completed >= $totalCheckpoint) {
            return 'completed';
        }

        return 'in_progress';
    }

    public function getCurrentCheckpointAttribute()
    {
        // ✅ Gunakan relasi yang sudah di-load
        $progressCollection = $this->relationLoaded('procurementProgress') 
            ? $this->procurementProgress 
            : $this->procurementProgress()->with('checkpoint')->get();

        // Cari progress yang sedang in_progress
        $latest = $progressCollection
            ->where('status', 'in_progress')
            ->sortByDesc('checkpoint_id')
            ->first();

        // Jika tidak ada yang in_progress, ambil yang terakhir completed
        if (!$latest) {
            $latest = $progressCollection
                ->where('status', 'completed')
                ->sortByDesc('checkpoint_id')
                ->first();
        }

        return $latest?->checkpoint?->point_name ?? null;
    }

    /**
     * Get all vendors through request procurements (PERBAIKAN #14)
     */
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