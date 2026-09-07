<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Makanan Tradisional',
                'description' => 'Makanan khas dan tradisional Cilacap',
            ],
            [
                'name' => 'Kerajinan Tangan',
                'description' => 'Hasil kerajinan tangan lokal',
            ],
            [
                'name' => 'Souvenir',
                'description' => 'Oleh-oleh dan cinderamata khas Cilacap',
            ],
            [
                'name' => 'Fashion',
                'description' => 'Pakaian dan aksesoris lokal',
            ],
            [
                'name' => 'Minuman',
                'description' => 'Minuman tradisional dan modern',
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => Str::slug($category['name'])],
                ['name' => $category['name'], 'description' => $category['description']],
            );
        }
    }
}
