<?php

/**
 * User Model
 * 
 * Model untuk tabel users yang menyimpan data user/pegawai
 * Model ini extends Authenticatable untuk authentication Laravel
 * Mengelola relasi dengan Division, ProcurementProgress, Approvals, Evaluations, dan HPS
 * 
 * @package App\Models
 * @author PT PAL Indonesia
 */

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
     * Field yang dapat diisi secara mass assignment
     * 
     * Field-field ini dapat diisi langsung menggunakan create() atau update()
     * 
     * @var list<string>
     */
    protected $fillable = [
        'name', // Nama user
        'email', // Email user (digunakan untuk login)
        'password', // Password user (akan di-hash otomatis)
        'division_id', // ID divisi tempat user bekerja
        'roles', // Role user (user, supply_chain, treasury, accounting, qa, sekretaris_direksi, desain)
        'status', // Status user (active, inactive, dll)
    ];

    /**
     * Field yang disembunyikan saat serialization (JSON, array)
     * 
     * Field-field ini tidak akan muncul saat model di-convert ke JSON atau array
     * Keamanan: Mencegah password dan token ter-expose
     * 
     * @var list<string>
     */
    protected $hidden = [
        'password', // Password tidak boleh ter-expose
        'remember_token', // Remember token untuk keamanan
    ];

    /**
     * Casting tipe data untuk field tertentu
     * 
     * Laravel akan otomatis mengkonversi field ini ke tipe data yang sesuai
     * 
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime', // Cast ke Carbon datetime object
            'password' => 'hashed', // Password akan di-hash otomatis saat disimpan
        ];
    }

    /**
     * Relasi: User belongs to Division
     * 
     * Setiap user bekerja di satu divisi
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function division(): BelongsTo
    {
        // Relasi belongsTo: user bekerja di satu division
        // Parameter: Model Division, foreign key di tabel users, primary key di tabel divisions
        return $this->belongsTo(Division::class, 'division_id', 'divisi_id');
    }

    /**
     * Relasi: User has many ProcurementProgress
     * 
     * Satu user dapat membuat banyak progress pengadaan
     * Progress pengadaan mencatat tahapan yang sudah dilalui dalam proses pengadaan
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function procurementProgress(): HasMany
    {
        // Relasi hasMany: user membuat banyak procurement progress
        // Parameter: Model ProcurementProgress, foreign key di tabel procurement_progress
        return $this->hasMany(ProcurementProgress::class, 'user_id');
    }

    /**
     * Relasi: User has many Approvals
     * 
     * Satu user dapat memberikan banyak approval
     * Approval digunakan untuk persetujuan dalam proses pengadaan
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function approvals(): HasMany
    {
        // Relasi hasMany: user memberikan banyak approvals
        // Parameter: Model Approval, foreign key di tabel approvals
        return $this->hasMany(Approval::class, 'approver_id');
    }

    /**
     * Relasi: User has many Evaluations (Evatek)
     * 
     * Satu user dapat membuat banyak evaluasi
     * Evaluasi digunakan untuk menilai vendor atau proses pengadaan
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function evaluations(): HasMany
    {
        // Relasi hasMany: user membuat banyak evaluations
        // Parameter: Model Evatek, foreign key di tabel evatek
        return $this->hasMany(Evatek::class, 'evaluated_by');
    }

    /**
     * Relasi: User has many HPS (Harga Perkiraan Sendiri)
     * 
     * Satu user dapat membuat banyak HPS
     * HPS digunakan untuk estimasi harga sebelum pemilihan vendor
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hps(): HasMany
    {
        // Relasi hasMany: user membuat banyak HPS
        // Parameter: Model Hps, foreign key di tabel hps
        return $this->hasMany(Hps::class, 'created_by');
    }
}
