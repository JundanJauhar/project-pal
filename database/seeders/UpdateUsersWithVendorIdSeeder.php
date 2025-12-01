<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UpdateUsersWithVendorIdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil semua vendor dari database
        $vendors = Vendor::all();

        if ($vendors->isEmpty()) {
            $this->command->warn('Tidak ada vendor di database. Tambahkan vendor terlebih dahulu.');
            return;
        }

        $this->command->info('Membuat akun untuk ' . $vendors->count() . ' vendor...');

        foreach ($vendors as $vendor) {
            // Buat email dari nama vendor
            // Contoh: "PT Krakatau Steel" -> "krakatau@pal.com"
            $emailPrefix = $this->generateEmailPrefix($vendor->name_vendor);
            $email = $emailPrefix . '@pal.com';

            // Buat atau update user untuk vendor
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

            $this->command->info("✓ {$vendor->name_vendor} | Email: {$email} | Password: password");
        }

        $this->command->info("\nSemua akun vendor berhasil dibuat/diupdate!");
    }

    /**
     * Generate email prefix from vendor name
     */
    private function generateEmailPrefix($vendorName): string
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
}
