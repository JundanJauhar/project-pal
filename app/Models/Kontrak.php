<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kontrak extends Model
{
    use HasFactory;

    protected $table = 'kontraks';
    protected $primaryKey = 'kontrak_id';
    public $timestamps = true;

    protected $fillable = [
        'procurement_id',
        'item_id',
        'vendor_id',
        'tgl_kontrak',
        'maker',
        'currency',
        'nilai',
        'payment_term',
        'incoterms',
        'coo',
        'warranty',
        'remarks',
    ];

    protected $casts = [
        'nilai' => 'decimal:2',
        'tgl_kontrak' => 'date',
    ];

    public function procurement()
    {
        return $this->belongsTo(Procurement::class, 'procurement_id', 'procurement_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'item_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id_vendor');
    }
}
