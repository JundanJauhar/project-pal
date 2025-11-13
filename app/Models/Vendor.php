<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    protected $table = 'vendors';
    protected $primaryKey = 'id_vendor';

    protected $fillable = [
        'name_vendor',
        'address',
        'phone_number',
        'email',
        'legal_status',
        'is_importer',
    ];

    protected $casts = [
        'is_importer' => 'boolean',
    ];

    /**
     * Get contracts for this vendor
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'vendor_id', 'id_vendor');
    }

    /**
     * Get request procurements for this vendor
     */
    public function requestProcurements(): HasMany
    {
        return $this->hasMany(RequestProcurement::class, 'vendor_id', 'id_vendor');
    }
}
