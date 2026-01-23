<?php
// app/Models/MaterialDelivery.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MaterialDelivery extends Model
{
    use HasFactory;

    protected $table = 'material_deliveries';
    protected $primaryKey = 'delivery_id';
    public $timestamps = true;

    protected $fillable = [
        'procurement_id',
        'incoterms',
        'imo_number',
        'container_number',
        'etd',
        'eta_sby_port',
        'eta_pal',
        'atd',
        'ata_sby_port',
        'remark',
    ];

    protected $casts = [
        'etd' => 'date',
        'eta_sby_port' => 'date',
        'eta_pal' => 'date',
        'atd' => 'date',
        'ata_sby_port' => 'date',
    ];

    // Relationships
    public function procurement()
    {
        return $this->belongsTo(Procurement::class, 'procurement_id', 'procurement_id');
    }

}