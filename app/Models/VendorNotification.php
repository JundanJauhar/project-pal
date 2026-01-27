<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorNotification extends Model
{
    use HasFactory;

    protected $table = 'vendor_notifications';

    protected $fillable = [
        'vendor_id',
        'type',
        'title',
        'message',
        'link',
        'is_read',
        'is_starred',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_starred' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id_vendor');
    }
}
