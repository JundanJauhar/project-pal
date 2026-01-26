<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | SUPER ADMIN (PEGANG SEMUA ROLE)
        |--------------------------------------------------------------------------
        */
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@pal.com'],
            [
                'name'        => 'Super Admin',
                'password'    => Hash::make('SuperAdmin123!'),
                'division_id' => null,
                'status'      => 'active',
            ]
        );

        // attach semua role ke super admin
        $superAdmin->roles()->sync(
            Role::pluck('role_id')->toArray()
        );

        /*
        |--------------------------------------------------------------------------
        | USER LAIN (MULTI ROLE SESUAI DIVISI)
        |--------------------------------------------------------------------------
        */
        $users = [
            [
                'user' => [
                    'name'        => 'User Division',
                    'email'       => 'user@pal.com',
                    'division_id' => 1,
                ],
                'roles' => ['requester'],
            ],
            [
                'user' => [
                    'name'        => 'Supply Chain',
                    'email'       => 'supplychain@pal.com',
                    'division_id' => 2,
                ],
                'roles' => [
                    'inquiry',
                    'evatek',
                    'negotiation',
                    'pengadaan',
                    'contract',
                    'delivery',
                ],
            ],
            [
                'user' => [
                    'name'        => 'Treasury',
                    'email'       => 'treasury@pal.com',
                    'division_id' => 3,
                ],
                'roles' => ['treasury', 'pembayaran'],
            ],
            [
                'user' => [
                    'name'        => 'Accounting',
                    'email'       => 'accounting@pal.com',
                    'division_id' => 4,
                ],
                'roles' => ['accounting'],
            ],
            [
                'user' => [
                    'name'        => 'Quality Assurance',
                    'email'       => 'qa@pal.com',
                    'division_id' => 5,
                ],
                'roles' => ['qa_inspector', 'qa_approver'],
            ],
            [
                'user' => [
                    'name'        => 'Sekretaris Direksi',
                    'email'       => 'sekretaris@pal.com',
                    'division_id' => 6,
                ],
                'roles' => ['sekdir'],
            ],
            [
                'user' => [
                    'name'        => 'Desain',
                    'email'       => 'desain@pal.com',
                    'division_id' => 7,
                ],
                'roles' => ['designer', 'evatek'],
            ],
        ];

        foreach ($users as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['user']['email']],
                [
                    'name'        => $data['user']['name'],
                    'division_id' => $data['user']['division_id'],
                    'password'    => Hash::make('password'),
                    'status'      => 'active',
                ]
            );

            $roleIds = Role::whereIn('role_code', $data['roles'])
                ->pluck('role_id')
                ->toArray();

            $user->roles()->sync($roleIds);
        }
    }
}
