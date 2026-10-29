<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\Staff;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Staff>
 */
class StaffFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->name(),
            'user_id' => User::factory(),
            'store_id' => Store::factory(),
            'role_id' => Role::factory(),
            'product_id' => null,
        ];
    }
}
