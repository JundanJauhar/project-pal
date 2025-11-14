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
                'name' => 'Admin System',
                'email' => 'admin@ptpal.co.id',
                'password' => Hash::make('password123'),
                'division_id' => 1,
                'role' => 'admin',
                'status' => 'active',
            ],
            [
                'name' => 'Supply Chain Manager',
                'email' => 'supply@ptpal.co.id',
                'password' => Hash::make('password123'),
                'division_id' => 1,
                'role' => 'supply_chain',
                'status' => 'active',
            ],
            [
                'name' => 'Finance Manager',
                'email' => 'finance@ptpal.co.id',
                'password' => Hash::make('password123'),
                'division_id' => 3,
                'role' => 'finance',
                'status' => 'active',
            ],
            [
                'name' => 'Engineering Staff',
                'email' => 'engineer@ptpal.co.id',
                'password' => Hash::make('password123'),
                'division_id' => 2,
                'role' => 'user',
                'status' => 'active',
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }
    }
}
