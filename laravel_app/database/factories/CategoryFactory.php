<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->randomElement([
            'Makanan Tradisional',
            'Kerajinan Tangan',
            'Souvenir',
            'Fashion',
            'Oleh-oleh',
            'Aksesoris',
            'Tas & Dompet',
            'Minuman',
            'Kue & Roti',
            'Elektronik',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->sentence(),
            'icon' => null,
        ];
    }
}
