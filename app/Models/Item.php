<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    protected $table = 'items';
    protected $primaryKey = 'item_id';

    public $timestamps = false;

    protected $fillable = [
        'request_procurement_id',
        'item_name',
        'item_description',
        'specification',
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

    /**
     * Get the request procurement for this item
     */
    public function requestProcurement(): BelongsTo
    {
        return $this->belongsTo(RequestProcurement::class, 'request_procurement_id', 'request_id');
    }
}
