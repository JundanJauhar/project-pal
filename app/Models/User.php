<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'division_id',
        'roles',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the division that the user belongs to
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'division_id', 'divisi_id');
    }

    /**
     * Get procurement progress created by this user
     */
    public function procurementProgress(): HasMany
    {
        return $this->hasMany(ProcurementProgress::class, 'user_id');
    }

    /**
     * Get approvals made by this user
     */
    public function approvals(): HasMany
    {
        return $this->hasMany(Approval::class, 'approver_id');
    }

    /**
     * Get evaluations made by this user
     */
    public function evaluations(): HasMany
    {
        return $this->hasMany(Evatek::class, 'evaluated_by');
    }

    /**
     * Get HPS created by this user
     */
    public function hps(): HasMany
    {
        return $this->hasMany(Hps::class, 'created_by');
    }
}
