<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JaminanPembayaran extends Model
{
    use HasFactory;

    protected $table = 'jaminan_pembayarans';
    protected $primaryKey = 'jaminan_pembayaran_id';
    public $timestamps = true;

    protected $fillable = [
        'vendor_id',
        'procurement_id',
        'advance_guarantee',   // Jaminan Uang Muka
        'performance_bond',    // Jaminan Pelaksanaan
        'warranty_bond',       // Jaminan Pemeliharaan
        'target_terbit',
        'realisasi_terbit',
        'expiry_date',
    ];

    protected $casts = [
        'advance_guarantee' => 'boolean',
        'performance_bond'  => 'boolean',
        'warranty_bond'     => 'boolean',
        'target_terbit'     => 'date',
        'realisasi_terbit'  => 'date',
        'expiry_date'       => 'date',
    ];

    // ================= RELATIONSHIPS =================

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id_vendor');
    }

    public function procurement()
    {
        return $this->belongsTo(Procurement::class, 'procurement_id', 'procurement_id');
    }
}
