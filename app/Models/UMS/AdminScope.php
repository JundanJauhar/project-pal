<?php

namespace App\Models\UMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminScope extends Model
{
    use HasFactory;

    protected $table = 'admin_scopes';

    protected $fillable = [
        'scope_key',
        'scope_value',
        'description',
    ];
}
