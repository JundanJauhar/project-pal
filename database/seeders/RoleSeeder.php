<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Division;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [

            'User Division' => [
                ['role_code' => 'requester', 'role_name' => 'Requester', 'description' => 'Pengaju procurement'],
            ],

            'Supply Chain' => [
                ['role_code' => 'inquiry',     'role_name' => 'Inquiry & Quotation', 'description' => 'Inquiry & quotation vendor'],
                ['role_code' => 'evatek',     'role_name' => 'evatek', 'description' => 'evatek'],
                ['role_code' => 'negotiation', 'role_name' => 'Negotiation',          'description' => 'Negosiasi harga'],
                ['role_code' => 'pengadaan', 'role_name' => 'Pengadaan OC',          'description' => 'Pengadaan OC'],
                ['role_code' => 'contract',    'role_name' => 'Contract & PO',        'description' => 'Kontrak dan PO'],
                ['role_code' => 'pembayaran',    'role_name' => 'Pembayaran',        'description' => 'Pembayaran'],
                ['role_code' => 'delivery',    'role_name' => 'Pengiriman Material',  'description' => 'Logistik & pengiriman'],
            ],

            'Treasury' => [
                ['role_code' => 'treasury', 'role_name' => 'Treasury Officer', 'description' => 'Pembayaran vendor'],
            ],

            'Accounting' => [
                ['role_code' => 'accounting', 'role_name' => 'Accounting', 'description' => 'Verifikasi invoice'],
            ],

            'Quality Assurance' => [
                ['role_code' => 'qa_inspector', 'role_name' => 'Inspector', 'description' => 'Inspeksi material'],
                ['role_code' => 'qa_approver',  'role_name' => 'QA Approval', 'description' => 'Approval hasil inspeksi'],
            ],

            'Sekretaris Direksi' => [
                ['role_code' => 'sekdir', 'role_name' => 'Sekretaris Direksi', 'description' => 'Grafana'],
            ],

            'Desain' => [
                ['role_code' => 'designer', 'role_name' => 'Designer', 'description' => 'Evatek'],
            ],

            'Vendor' => [
                ['role_code' => 'vendor', 'role_name' => 'Vendor User', 'description' => 'Akses vendor'],
            ],

            'Sistem' => [
                ['role_code' => 'superadmin', 'role_name' => 'Super Admin', 'description' => 'Akses Super Admin'],
                ['role_code' => 'admin', 'role_name' => 'Admin', 'description' => 'Akses Admin Sistem'],
            ],
        ];

        foreach ($roles as $divisionName => $items) {
            $division = Division::where('division_name', $divisionName)->first();
            if (!$division) continue;

            foreach ($items as $role) {
                Role::firstOrCreate(
                    [
                        'division_id' => $division->division_id,
                        'role_code'   => $role['role_code'],
                    ],
                    [
                        'role_name'   => $role['role_name'],
                        'description' => $role['description'],
                    ]
                );
            }
        }
    }
}
