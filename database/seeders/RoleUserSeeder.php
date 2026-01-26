<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class RoleUserSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | SUPER ADMIN → ALL ROLES
        |--------------------------------------------------------------------------
        */
        $superAdmin = User::where('email', 'superadmin@pal.com')->first();

        if ($superAdmin) {
            $superAdmin->roles()->sync(
                Role::pluck('role_id')->toArray()
            );
        }

        /*
        |--------------------------------------------------------------------------
        | USER ROLE MAPPING (EMAIL → ROLE_CODE[])
        |--------------------------------------------------------------------------
        */
        $roleMapping = [
            'user@pal.com' => [
                'requester',
            ],

            'supplychain@pal.com' => [
                'inquiry',
                'evatek',
                'negotiation',
                'pengadaan',
                'contract',
                'pembayaran',
                'delivery',
            ],

            'treasury@pal.com' => [
                'treasury',
            ],

            'accounting@pal.com' => [
                'accounting',
            ],

            'qa@pal.com' => [
                'qa_inspector',
                'qa_approver',
            ],

            'sekretaris@pal.com' => [
                'sekdir',
            ],

            'desain@pal.com' => [
                'designer',
                'evatek',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | ATTACH ROLES
        |--------------------------------------------------------------------------
        */
        foreach ($roleMapping as $email => $roleCodes) {
            $user = User::where('email', $email)->first();
            if (!$user) continue;

            $roleIds = Role::whereIn('role_code', $roleCodes)
                ->pluck('role_id')
                ->toArray();

            $user->roles()->sync($roleIds);
        }
    }
}
