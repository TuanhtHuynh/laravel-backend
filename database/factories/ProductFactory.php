<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 99999),
            'price' => fake()->numberBetween(10000, 500000),
            'sale_price' => null,
            'sale_start_at' => null,
            'sale_end_at' => null,
            'category_id' => Category::factory(),
            'meta_title' => null,
            'meta_description' => null,
            'meta_keyword' => null,
        ];
    }
}
