<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    protected $primaryKey = 'contract_id';

    protected $fillable = [
        'project_id',
        'vendor_id',
        'contract_number',
        'contract_value',
        'start_date',
        'end_date',
        'status',
        'created_by'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'contract_value' => 'integer'
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id_vendor');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }
}
