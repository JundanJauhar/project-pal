<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestProcurement extends Model
{
    use SoftDeletes;

    protected $table = 'request_procurements';
    protected $primaryKey = 'request_id';

    protected $fillable = [
        'procurement_id',
        'item_id',
        'vendor_id',
        'request_name',
        'created_date',
        'deadline_date',
        'department_id',
        'request_status',
    ];

    protected $casts = [
        'created_date' => 'date',
        'deadline_date' => 'date',
    ];

    public function procurement()
    {
        return $this->belongsTo(Procurement::class, 'procurement_id', 'procurement_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id_vendor');
    }

    public function items()
    {
        return $this->hasMany(Item::class, 'request_procurement_id', 'request_id');
    }

    public function procurementProgress()
    {
        return $this->hasMany(ProcurementProgress::class, 'request_id', 'request_id');
    }
}
