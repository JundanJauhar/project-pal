<?php

namespace Database\Seeders;

use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class VendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vendors = [
            // ===== MATERIAL LOKAL (AD) =====
            [
                'vendor_code' => 'AD',
                'name_vendor' => 'PT Krakatau Steel',
                'specialization' => 'material_lokal',
                'address' => 'Jl. Industri No. 1, Cilegon, Banten',
                'phone_number' => '+62-21-12345678',
                'email' => 'sales@krakatausteel.com',
                'is_importer' => false,
            ],
            [
                'vendor_code' => 'AD',
                'name_vendor' => 'PT Pindad',
                'specialization' => 'material_lokal',
                'address' => 'Jl. Gatot Subroto, Bandung',
                'phone_number' => '+62-22-87654321',
                'email' => 'procurement@pindad.com',
                'is_importer' => false,
            ],
            [
                'vendor_code' => 'AD',
                'name_vendor' => 'PT Barata Indonesia',
                'specialization' => 'material_lokal',
                'address' => 'Jl. Raya Bojonegoro-Surabaya, Jawa Timur',
                'phone_number' => '+62-31-75654321',
                'email' => 'sales@barata.co.id',
                'is_importer' => false,
            ],

            // ===== MATERIAL IMPOR (AL) =====
            [
                'vendor_code' => 'AL',
                'name_vendor' => 'PT Dirgantara Indonesia',
                'specialization' => 'material_impor',
                'address' => 'Jl. Pajajaran No. 154, Bandung',
                'phone_number' => '+62-22-98765432',
                'email' => 'sales@indonesian-aerospace.com',
                'is_importer' => true,
            ],
            [
                'vendor_code' => 'AL',
                'name_vendor' => 'PT Tripatra Engineering',
                'specialization' => 'material_impor',
                'address' => 'Jl. Sudirman No. 56, Jakarta Selatan',
                'phone_number' => '+62-21-54321098',
                'email' => 'procurement@tripatra.com',
                'is_importer' => true,
            ],
            [
                'vendor_code' => 'AL',
                'name_vendor' => 'PT Metalindo Pratama',
                'specialization' => 'material_impor',
                'address' => 'Jl. Gatot Subroto Km 3, Medan',
                'phone_number' => '+62-61-45678901',
                'email' => 'sales@metalindo.com',
                'is_importer' => true,
            ],

            // ===== JASA (AS) =====
            [
                'vendor_code' => 'AS',
                'name_vendor' => 'PT Konsultan Teknik Indonesia',
                'specialization' => 'jasa',
                'address' => 'Jl. M.H. Thamrin No. 12, Jakarta Pusat',
                'phone_number' => '+62-21-31234567',
                'email' => 'info@kti-consulting.com',
                'is_importer' => false,
            ],
            [
                'vendor_code' => 'AS',
                'name_vendor' => 'PT Solusi Teknologi Marine',
                'specialization' => 'jasa',
                'address' => 'Jl. Pelabuhan No. 99, Surabaya',
                'phone_number' => '+62-31-12398765',
                'email' => 'support@marinetech.co.id',
                'is_importer' => false,
            ],
            [
                'vendor_code' => 'AS',
                'name_vendor' => 'PT Engineering Solutions',
                'specialization' => 'jasa',
                'address' => 'Jl. Ahmad Yani No. 8, Yogyakarta',
                'phone_number' => '+62-274-56789012',
                'email' => 'contact@engsol.id',
                'is_importer' => false,
            ],
            [
                'vendor_code' => 'AS',
                'name_vendor' => 'PT Inspeksi dan Sertifikasi',
                'specialization' => 'jasa',
                'address' => 'Jl. Veteran No. 45, Semarang',
                'phone_number' => '+62-24-87654321',
                'email' => 'qa@inspeksi.co.id',
                'is_importer' => false,
            ],
        ];

        foreach ($vendors as $vendor) {
            // Generate user_vendor (email login) dari nama vendor
            $user_vendor = $this->generateUserVendor($vendor['name_vendor']);

            // Tambahkan user_vendor dan password
            $vendor['user_vendor'] = $user_vendor;
            $vendor['password'] = Hash::make('password'); // Default password: 'password'

            Vendor::create($vendor);
        }
    }

    /**
     * Generate user_vendor (email login) dari vendor name
     * Contoh: "PT Krakatau Steel" -> "krakatausteel@vendor.com"
     */
    private function generateUserVendor(string $vendorName): string
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
        while (Vendor::where('user_vendor', $email)->exists()) {
            $email = str_replace('@vendor.com', $counter . '@vendor.com', $baseEmail);
            $counter++;
        }

        return $email;
    }
}
