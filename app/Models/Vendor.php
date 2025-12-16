<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

class Vendor extends Authenticatable
{
    protected $table = 'vendors';
    protected $primaryKey = 'id_vendor';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id_vendor',
        'name_vendor',
        'address',
        'phone_number',
        'email',
        'user_vendor',
        'password',
        'is_importer',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_importer' => 'boolean',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate user_vendor (email login) dan password saat vendor dibuat
        static::creating(function ($vendor) {
            if (empty($vendor->user_vendor)) {
                $vendor->user_vendor = self::generateEmailVendor($vendor->name_vendor);
            }
            
            if (empty($vendor->password)) {
                $vendor->password = Hash::make('password'); // default password
            }
        });
    }

    /**
     * Generate user_vendor (email login) from vendor name
     * Contoh: "PT Mega Persada" -> "megapersada@vendor.com"
     */
    private static function generateEmailVendor($vendorName): string
    {
        // Hapus "PT", "CV", "UD", "Tbk" dll dari nama
        $name = preg_replace('/^(PT|CV|UD|Tbk)\.?\s*/i', '', $vendorName);
        
        // Hapus semua spasi dan karakter special, lowercase
        $cleanName = strtolower(preg_replace('/[^A-Za-z0-9]/', '', $name));
        
        // Format email login dengan @vendor.com
        $baseEmail = $cleanName . '@vendor.com';
        
        // Pastikan unique dengan menambah angka jika sudah ada
        $email = $baseEmail;
        $counter = 1;
        while (self::where('user_vendor', $email)->exists()) {
            $email = str_replace('@vendor.com', $counter . '@vendor.com', $baseEmail);
            $counter++;
        }
        
        return $email;
    }

    /**
     * Override getAuthPassword untuk autentikasi
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Get the name of the unique identifier for the user.
     */
    public function getAuthIdentifierName()
    {
        return 'user_vendor';
    }

    /**
     * Get request procurements for this vendor
     */
    public function requestProcurements(): HasMany
    {
        return $this->hasMany(RequestProcurement::class, 'vendor_id', 'id_vendor');
    }

    public function evatekItems()
    {
        return $this->hasMany(EvatekItem::class, 'vendor_id', 'id_vendor');
    }
}
