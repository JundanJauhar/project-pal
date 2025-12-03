<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;

class Vendor extends Model
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

        // Otomatis buat akun user ketika vendor baru dibuat
        static::created(function ($vendor) {
            $emailPrefix = self::generateEmailPrefix($vendor->name_vendor);
            $email = $emailPrefix . '@pal.com';

            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $vendor->name_vendor,
                    'password' => Hash::make('password'),
                    'vendor_id' => $vendor->id_vendor,
                    'roles' => 'vendor',
                    'status' => 'active',
                ]
            );
        });

        // Update user ketika vendor diupdate
        static::updated(function ($vendor) {
            $user = User::where('vendor_id', $vendor->id_vendor)->first();
            
            if ($user) {
                $user->update([
                    'name' => $vendor->name_vendor,
                ]);
            }
        });
    }

    /**
     * Generate email prefix from vendor name
     */
    private static function generateEmailPrefix($vendorName): string
    {
        // Hapus "PT", "CV", "UD" dll dari nama
        $name = preg_replace('/^(PT|CV|UD|Tbk)\s*/i', '', $vendorName);
        
        // Ambil kata pertama atau jika ada brand terkenal ambil itu
        $words = explode(' ', trim($name));
        $prefix = strtolower($words[0]);
        
        // Hapus karakter special dan spasi
        $prefix = preg_replace('/[^a-z0-9]/', '', $prefix);
        
        return $prefix;
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

    /**
     * Get user account for this vendor
     */
    public function user()
    {
        return $this->hasOne(User::class, 'vendor_id', 'id_vendor');
    }

}
