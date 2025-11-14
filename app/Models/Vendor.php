<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $table = 'vendors';
    protected $primaryKey = 'id_vendor';

    protected $fillable = [
        'name_vendor',
        'is_importer',
    ];

    protected $casts = [
        'is_importer' => 'boolean',
    ];

    public function requestProcurements()
    {
        return $this->hasMany(RequestProcurement::class, 'vendor_id', 'id_vendor');
    }
}
