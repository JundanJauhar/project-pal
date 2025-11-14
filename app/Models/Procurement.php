<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Procurement extends Model
{
    protected $table = 'procurements';
    protected $primaryKey = 'procurement_id';

    protected $fillable = [
        'code_procurement',
        'name_procurement',
        'description',
        'department_procurement',
        'priority',
        'start_date',
        'end_date',
        'status_procurement',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function division()
    {
        return $this->belongsTo(Division::class, 'department_procurement', 'division_id');
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'procurement_id', 'procurement_id');
    }

    public function requestProcurements()
    {
        return $this->hasMany(RequestProcurement::class, 'procurement_id', 'procurement_id');
    }
}
