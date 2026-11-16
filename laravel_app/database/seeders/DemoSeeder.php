<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Data demo untuk lokal/staging.
 *
 * TIDAK dipanggil DatabaseSeeder (produksi cukup roles + admin).
 * Gunakan: php artisan db:seed --class=DemoSeeder
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            SellerApplicationsSeeder::class,
            ProductSeeder::class,
            OrderSeeder::class,
        ]);
    }
}