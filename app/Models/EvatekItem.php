<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvatekItem extends Model
{
    protected $table = 'evatek_items';
    protected $primaryKey = 'id_evatek_item';

    protected $fillable = [
        'item_id',
        'vendor_id',
        'status',
        'evaluation_note',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'item_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id_vendor');
    }
}
