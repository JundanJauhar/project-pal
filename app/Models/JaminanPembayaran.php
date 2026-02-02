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
        'jenis_jaminan',
        'target_terbit',
        'realisasi_terbit',
        'expiry_date',
        'link',
    ];

    protected $casts = [
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
