<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Negotiation extends Model
{
    use HasFactory;

    protected $table = 'negotiations';
    protected $primaryKey = 'negotiation_id';
    public $timestamps = true;

    protected $fillable = [
        'procurement_id',
        'vendor_id',
        'hps',
        'currency_hps',
        'budget',
        'currency_budget',
        'harga_final',
        'currency_harga_final',
        'tanggal_kirim',
        'tanggal_terima',
        'notes',
    ];


    protected $casts = [
        'hps' => 'decimal:2',
        'budget' => 'decimal:2',
        'harga_final' => 'decimal:2',
        'tanggal_kirim' => 'date',
        'tanggal_terima' => 'date',
    ];

    // Relationships
    public function procurement()
    {
        return $this->belongsTo(Procurement::class, 'procurement_id', 'procurement_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id_vendor');
    }
}
