<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $table = 'items';
    protected $primaryKey = 'item_id';

    protected $fillable = [
        'request_procurement_id',
        'item_name',
        'item_description',
        'amount',
        'unit',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'amount' => 'integer',
        'unit_price' => 'integer',
        'total_price' => 'integer',
    ];

    public function requestProcurement()
    {
        return $this->belongsTo(RequestProcurement::class, 'request_procurement_id', 'request_id');
    }
}
