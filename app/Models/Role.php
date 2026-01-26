<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $primaryKey = 'role_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'division_id',
        'role_code',
        'role_name',
        'description',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'division_id', 'division_id');
    }

    /**
     * Many-to-many: Role <-> Users
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'role_user',   // pivot table
            'role_id',     // FK ke roles
            'user_id'      // FK ke users
        );
    }
}
