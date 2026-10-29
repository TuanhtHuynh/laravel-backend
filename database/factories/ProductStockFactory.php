<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductStock>
 */
class ProductStockFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'store_id' => Store::factory(),
            'staff_id' => null,
            'quantity' => fake()->numberBetween(0, 100),
        ];
    }
}
