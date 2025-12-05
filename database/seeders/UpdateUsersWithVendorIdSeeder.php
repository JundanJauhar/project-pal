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

            // Ambil nama vendor, ubah jadi lowercase, hapus semua spasi & simbol
            $emailPrefix = $this->generateEmailPrefix($vendor->name_vendor);
            $email = $emailPrefix . '@pal.com';



            // Buat atau update user untuk vendor
            User::updateOrCreate(
                ['vendor_id' => $vendor->id_vendor], // cari berdasarkan vendor, bukan email
                [
                    'email' => $email,
                    'name' => $vendor->name_vendor,
                    'password' => Hash::make('password'),
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
        // Ubah nama ke lowercase
        $name = strtolower($vendorName  );

        // Hapus prefix PT, CV, UD, dll (dengan atau tanpa titik)
        $name = preg_replace('/^(pt\.?|cv\.?|ud\.?|pd\.?|fa\.?|toko\.?)\s*/i', '', $name);

        // Menghapus semua spasi
        $name = str_replace(' ', '', $name);

        // Hapus semua karakter selain huruf dan angka
        $name = preg_replace('/[^a-z0-9]/', '', $name);

        return $name;
    }
}
