<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'departments';
    protected $primaryKey = 'department_id';

    protected $fillable = [
        'department_name',
        'description',
    ];

    public function checkpoints()
    {
        return $this->hasMany(Checkpoint::class, 'responsible_division', 'department_id');
    }

    public function requestProcurements()
    {
        return $this->hasMany(RequestProcurement::class, 'department_id', 'department_id');
    }
}
