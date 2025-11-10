<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contract extends Model
{
    protected $table = 'contracts';
    protected $primaryKey = 'contract_id';

    protected $fillable = [
        'project_id',
        'vendor_id',
        'contract_number',
        'status',
    ];

    /**
     * Get the project for this contract
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    /**
     * Get the vendor for this contract
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id_vendor');
    }
}
