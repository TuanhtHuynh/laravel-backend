<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Role::firstOrCreate(
            ['slug' => Role::MANAGER],
            ['name' => 'Quản lý'],
        );

        Role::firstOrCreate(
            ['slug' => Role::WAREHOUSE],
            ['name' => 'Quản kho'],
        );

        Role::firstOrCreate(
            ['slug' => Role::SALES],
            ['name' => 'Bán hàng'],
        );

        Role::firstOrCreate(
            ['slug' => Role::ADMIN],
            ['name' => 'Quản trị'],
        );
    }
}
