<?php

/**
 * Project Model
 * 
 * Model untuk tabel projects yang menyimpan data proyek pengadaan
 * Model ini mengelola relasi dengan Division, Contract, HPS, Evaluations, dan RequestProcurement
 * 
 * @package App\Models
 * @author PT PAL Indonesia
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    /**
     * Nama tabel di database
     * 
     * @var string
     */
    protected $table = 'projects';
    
    /**
     * Primary key tabel
     * 
     * @var string
     */
    protected $primaryKey = 'project_id';

    /**
     * Field yang dapat diisi secara mass assignment
     * 
     * Field-field ini dapat diisi langsung menggunakan create() atau update()
     * Keamanan: Field yang tidak ada di sini tidak dapat diisi secara mass assignment
     * 
     * @var array<string>
     */
    protected $fillable = [
        'code_project', // Kode unik proyek
        'name_project', // Nama proyek
        'description', // Deskripsi proyek
        'owner_division_id', // ID divisi pemilik proyek
        'priority', // Prioritas proyek (rendah, sedang, tinggi)
        'start_date', // Tanggal mulai proyek
        'end_date', // Tanggal selesai proyek
        'status_project', // Status proyek (draft, review_sc, dll)
    ];

    /**
     * Casting tipe data untuk field tertentu
     * 
     * Laravel akan otomatis mengkonversi field ini ke tipe data yang sesuai
     * 
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date', // Cast ke Carbon date object
        'end_date' => 'date', // Cast ke Carbon date object
    ];

    /**
     * Relasi: Project belongs to Division (owner)
     * 
     * Setiap proyek dimiliki oleh satu divisi
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ownerDivision(): BelongsTo
    {
        // Relasi belongsTo: project memiliki satu owner division
        // Parameter: Model Division, foreign key di tabel projects, primary key di tabel divisions
        return $this->belongsTo(Division::class, 'owner_division_id', 'divisi_id');
    }

    /**
     * Relasi: Project has many Contracts
     * 
     * Satu proyek dapat memiliki banyak kontrak dengan vendor
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contracts(): HasMany
    {
        // Relasi hasMany: project memiliki banyak contracts
        // Parameter: Model Contract, foreign key di tabel contracts, local key di tabel projects
        return $this->hasMany(Contract::class, 'project_id', 'project_id');
    }

    /**
     * Relasi: Project has many HPS (Harga Perkiraan Sendiri)
     * 
     * Satu proyek dapat memiliki banyak HPS
     * HPS digunakan untuk estimasi harga sebelum pemilihan vendor
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hps(): HasMany
    {
        // Relasi hasMany: project memiliki banyak HPS
        return $this->hasMany(Hps::class, 'project_id', 'project_id');
    }

    /**
     * Relasi: Project has many Evaluations (Evatek)
     * 
     * Satu proyek dapat memiliki banyak evaluasi
     * Evaluasi digunakan untuk menilai vendor atau proses pengadaan
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function evaluations(): HasMany
    {
        // Relasi hasMany: project memiliki banyak evaluations
        return $this->hasMany(Evatek::class, 'project_id', 'project_id');
    }

    /**
     * Relasi: Project has many RequestProcurements
     * 
     * Satu proyek dapat memiliki banyak request procurement
     * Request procurement adalah permintaan pengadaan barang/jasa untuk proyek
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function requestProcurements(): HasMany
    {
        // Relasi hasMany: project memiliki banyak request procurements
        return $this->hasMany(RequestProcurement::class, 'project_id', 'project_id');
    }
}
