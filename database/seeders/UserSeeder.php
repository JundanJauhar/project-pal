<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'User Division',
                'email' => 'user@pal.com',
                'password' => Hash::make('password'),
                'division_id' => 1,
                'roles' => 'user',
                'status' => 'active',
            ],
            [
                'name' => 'Supply Chain Manager',
                'email' => 'supplychain@pal.com',
                'password' => Hash::make('password'),
                'division_id' => 2,
                'roles' => 'supply_chain',
                'status' => 'active',
            ],
            [
                'name' => 'Treasury Staff',
                'email' => 'treasury@pal.com',
                'password' => Hash::make('password'),
                'division_id' => 3,
                'roles' => 'treasury',
                'status' => 'active',
            ],
            [
                'name' => 'Accounting Staff',
                'email' => 'accounting@pal.com',
                'password' => Hash::make('password'),
                'division_id' => 4,
                'roles' => 'accounting',
                'status' => 'active',
            ],
            [
                'name' => 'Quality Assurance',
                'email' => 'qa@pal.com',
                'password' => Hash::make('password'),
                'division_id' => 5,
                'roles' => 'qa',
                'status' => 'active',
            ],
            [
                'name' => 'Sekretaris Direksi',
                'email' => 'sekretaris@pal.com',
                'password' => Hash::make('password'),
                'division_id' => 6,
                'roles' => 'sekretaris',
                'status' => 'active',
            ],
            [
                'name' => 'Desain Staff',
                'email' => 'desain@pal.com',
                'password' => Hash::make('password'),
                'division_id' => 7,
                'roles' => 'desain',
                'status' => 'active',
            ],
            [
                'name' => 'PT Pindad',
                'email' => 'vendor@pal.com',
                'password' => Hash::make('password'),
                'division_id' => null,
                'roles' => 'vendor',
                'status' => 'active',
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
