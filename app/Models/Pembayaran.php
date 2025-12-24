<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayarans';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'vendor_id',
        'procurement_id',
        'payment_type',
        'percentage',
        'payment_value',
        'currency',
        'no_memo',
        'link',
        'target_date',
        'realization_date',
    ];

    protected $casts = [
        'percentage'       => 'decimal:2',
        'payment_value'    => 'decimal:2',
        'target_date'      => 'date',
        'realization_date' => 'date',
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
