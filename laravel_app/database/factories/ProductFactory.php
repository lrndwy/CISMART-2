<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(fake()->numberBetween(2, 4), true);

        return [
            'name' => ucwords($name),
            'slug' => \Illuminate\Support\Str::slug($name).'-'.\Illuminate\Support\Str::random(5),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 10000, 1000000),
            'stock' => fake()->numberBetween(0, 100),
            'weight_gram' => fake()->numberBetween(50, 2000),
            'images' => [],
            'is_featured' => fake()->boolean(20),
            'status' => fake()->randomElement(['published', 'published', 'published', 'draft']),
        ];
    }
}
