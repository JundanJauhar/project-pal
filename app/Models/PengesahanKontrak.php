<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengesahanKontrak extends Model
{
    use HasFactory;

    protected $table = 'pengesahan_kontraks';
    protected $primaryKey = 'pengesahan_id';
    public $timestamps = true;

    protected $fillable = [
        'procurement_id',
        'vendor_id',
        'currency',
        'nilai',
        'tgl_kadep_to_kadiv',
        'tgl_kadiv_to_cto',
        'tgl_cto_to_ceo',
        'tgl_acc',
        'remarks',
    ];

    protected $casts = [
        'nilai' => 'decimal:2',
        'tgl_kadep_to_kadiv' => 'date',
        'tgl_kadiv_to_cto' => 'date',
        'tgl_cto_to_ceo' => 'date',
        'tgl_acc' => 'date',
    ];

    public function procurement()
    {
        return $this->belongsTo(Procurement::class, 'procurement_id', 'procurement_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id_vendor');
    }
}
