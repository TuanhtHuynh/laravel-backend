<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Staff;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        $store = Store::orderBy('id')->first();
        $sales = Role::where('slug', Role::SALES)->first();

        $user = User::firstOrCreate(
            ['email' => 'sales1@food-sg.test'],
            [
                'name' => 'Bán hàng 1',
                'password' => Hash::make('Sales@123'),
            ]
        );

        Staff::firstOrCreate(
            ['user_id' => $user->id],
            [
                'name' => 'Bán hàng 1',
                'store_id' => $store?->id,
                'role_id' => $sales?->id,
                'hourly_rate' => 25000,
            ]
        );

        $this->command->info('Staff accounts (password = role prefix + @123):');
        $this->command->info('  admin@food-sg.test / Admin@123 (admin)');
        $this->command->info('  warehouse1@food-sg.test / Warehouse@123 (warehouse)');
        $this->command->info('  sales1@food-sg.test / Sales@123 (sales)');
    }
}
