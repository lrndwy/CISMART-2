<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@cismart.test'],
            [
                'name' => 'Admin CISMART',
                'password' => bcrypt('password'),
                'phone' => '+6281234567890',
                'address' => 'Jl. Admin No. 1, Cilacap',
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('admin');
    }
}
