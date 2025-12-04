<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InquiryQuotation extends Model
{
    use HasFactory;

    protected $table = 'inquiry_quotations';
    protected $primaryKey = 'inquiry_quotation_id';
    public $timestamps = true;

    protected $fillable = [
        'vendor_id',
        'procurement_id',
        'tanggal_inquiry',
        'tanggal_quotation',
        'target_quotation',
        'lead_time',
        'nilai_harga',
        'currency',
        'notes',
    ];

    protected $casts = [
        'tanggal_inquiry' => 'date',
        'tanggal_quotation' => 'date',
        'target_quotation' => 'date',
        'nilai_harga' => 'decimal:2',
    ];

    // Relationships
    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id_vendor');
    }

    public function procurement()
    {
        return $this->belongsTo(Procurement::class, 'procurement_id', 'procurement_id');
    }
}
?>