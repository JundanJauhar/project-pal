<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    protected $table = 'divisions';
    protected $primaryKey = 'division_id';

    protected $fillable = [
        'division_name',
        'description',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'division_id', 'division_id');
    }

    public function procurements()
    {
        return $this->hasMany(Procurement::class, 'department_procurement', 'division_id');
    }
}
