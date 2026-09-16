<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed hanya akun admin (tanpa data demo lain).
     * Peran tetap dibuat karena dipakai aplikasi: pendaftaran pembeli/penjual
     * memanggil assignRole('buyer'/'seller') dan panel admin butuh peran 'admin'.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
}
