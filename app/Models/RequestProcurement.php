<?php

/**
 * RequestProcurement Model
 * 
 * Model untuk tabel request_procurement yang menyimpan data permintaan pengadaan
 * Model ini menggunakan SoftDeletes untuk soft delete (data tidak benar-benar dihapus)
 * Mengelola relasi dengan Project, Vendor, Division, Item, ProcurementProgress, dan Negotiation
 * 
 * @package App\Models
 * @author PT PAL Indonesia
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestProcurement extends Model
{
    /**
     * Menggunakan SoftDeletes trait
     * 
     * Dengan SoftDeletes, data tidak benar-benar dihapus dari database
     * Data hanya ditandai sebagai deleted_at, sehingga bisa di-restore jika diperlukan
     */
    use SoftDeletes;

    /**
     * Nama tabel di database
     * 
     * @var string
     */
    protected $table = 'request_procurement';
    
    /**
     * Primary key tabel
     * 
     * @var string
     */
    protected $primaryKey = 'request_id';

    /**
     * Field yang dapat diisi secara mass assignment
     * 
     * Field-field ini dapat diisi langsung menggunakan create() atau update()
     * 
     * @var array<string>
     */
    protected $fillable = [
        'project_id', // ID proyek yang terkait dengan request ini
        'item_id', // ID item (jika ada)
        'vendor_id', // ID vendor yang dipilih (jika sudah ada)
        'request_name', // Nama request procurement
        'created_date', // Tanggal request dibuat
        'deadline_date', // Tanggal deadline request
        'request_status', // Status request (submitted, approved, rejected, dll)
        'applicant_department', // ID divisi yang mengajukan request
    ];

    /**
     * Casting tipe data untuk field tertentu
     * 
     * Laravel akan otomatis mengkonversi field ini ke tipe data yang sesuai
     * 
     * @var array<string, string>
     */
    protected $casts = [
        'created_date' => 'date', // Cast ke Carbon date object
        'deadline_date' => 'date', // Cast ke Carbon date object
    ];

    /**
     * Relasi: RequestProcurement belongs to Project
     * 
     * Setiap request procurement terkait dengan satu proyek
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project(): BelongsTo
    {
        // Relasi belongsTo: request procurement terkait dengan satu project
        // Parameter: Model Project, foreign key di tabel request_procurement, primary key di tabel projects
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    /**
     * Relasi: RequestProcurement belongs to Vendor
     * 
     * Setiap request procurement dapat memiliki satu vendor yang dipilih
     * Vendor dipilih setelah proses tender/negosiasi
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function vendor(): BelongsTo
    {
        // Relasi belongsTo: request procurement dapat memiliki satu vendor
        // Parameter: Model Vendor, foreign key di tabel request_procurement, primary key di tabel vendors
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id_vendor');
    }

    /**
     * Relasi: RequestProcurement belongs to Division (applicant)
     * 
     * Setiap request procurement diajukan oleh satu divisi
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function applicantDivision(): BelongsTo
    {
        // Relasi belongsTo: request procurement diajukan oleh satu division
        // Parameter: Model Division, foreign key di tabel request_procurement, primary key di tabel divisions
        return $this->belongsTo(Division::class, 'applicant_department', 'divisi_id');
    }

    /**
     * Relasi: RequestProcurement has many Items
     * 
     * Satu request procurement dapat memiliki banyak items (barang/jasa)
     * Items adalah detail barang/jasa yang diminta dalam request procurement
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items(): HasMany
    {
        // Relasi hasMany: request procurement memiliki banyak items
        // Parameter: Model Item, foreign key di tabel items, local key di tabel request_procurement
        return $this->hasMany(Item::class, 'request_procurement_id', 'request_id');
    }

    /**
     * Relasi: RequestProcurement has many ProcurementProgress
     * 
     * Satu request procurement dapat memiliki banyak progress
     * Progress mencatat tahapan yang sudah dilalui dalam proses pengadaan
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function procurementProgress(): HasMany
    {
        // Relasi hasMany: request procurement memiliki banyak procurement progress
        // Parameter: Model ProcurementProgress, foreign key di tabel procurement_progress, local key di tabel request_procurement
        return $this->hasMany(ProcurementProgress::class, 'permintaan_pengadaan_id', 'request_id');
    }

    /**
     * Relasi: RequestProcurement has many Negotiations
     * 
     * Satu request procurement dapat memiliki banyak negosiasi
     * Negosiasi digunakan untuk berdiskusi dengan vendor tentang harga, spesifikasi, dll
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function negotiations(): HasMany
    {
        // Relasi hasMany: request procurement memiliki banyak negotiations
        // Parameter: Model Negotiation, foreign key di tabel negotiations, local key di tabel request_procurement
        return $this->hasMany(Negotiation::class, 'request_id', 'request_id');
    }
}
