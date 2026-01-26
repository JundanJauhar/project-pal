<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'user_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'name',
        'email',
        'password',
        'division_id',
        'vendor_id',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }

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
     * Many-to-many: User <-> Roles
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'role_user',   // pivot table
            'user_id',     // FK ke users
            'role_id'      // FK ke roles
        );
    }

    public function procurementProgress(): HasMany
    {
        return $this->hasMany(ProcurementProgress::class, 'user_id', 'user_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id_vendor');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id', 'user_id');
    }

    public function sentNotifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'sender_id', 'user_id');
    }

    /*
    |--------------------------------------------------------------------------
    | ROLE HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * Check single role by role_code
     */
    public function hasRole(string $roleCode): bool
    {
        return $this->roles->contains('role_code', $roleCode);
    }

    /**
     * Check multiple roles (OR logic)
     */
    public function hasAnyRole(array $roleCodes): bool
    {
        return $this->roles
            ->whereIn('role_code', $roleCodes)
            ->isNotEmpty();
    }

    /**
     * Check multiple roles (AND logic)
     */
    public function hasAllRoles(array $roleCodes): bool
    {
        return collect($roleCodes)->every(
            fn($role) => $this->hasRole($role)
        );
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('superadmin');
    }

    public function isVendor(): bool
    {
        return !is_null($this->vendor_id);
    }

    public function loadAuthContext(): self
    {
        return $this->loadMissing(['roles', 'division']);
    }
}
