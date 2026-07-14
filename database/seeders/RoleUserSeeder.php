<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RoleUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define users
        $users = [
            [
                'name' => 'Admin Utama',
                'email' => 'admin@gmail.com',
                'role' => UserRole::SuperAdmin,
            ],
            [
                'name' => 'Kasir Cafe',
                'email' => 'kasir@gmail.com',
                'role' => UserRole::Cashier,
            ],
            [
                'name' => 'Koki Dapur',
                'email' => 'dapur@gmail.com',
                'role' => UserRole::Kitchen,
            ],
            [
                'name' => 'Pelayan Meja',
                'email' => 'pelayan@gmail.com',
                'role' => UserRole::Waiter,
            ],
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password'),
                    'role' => $userData['role'],
                    'email_verified_at' => now(),
                ]
            );
            
            // Simple role assignment via Enum is enough
        }
    }
}
