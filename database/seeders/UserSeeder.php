<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'admin@food.sg'],
            [
                'name' => 'Admin',
                'password' => Hash::make('Admin@123'),
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Account: admin@food.sg | Pass: Admin@123 | User ID: ' . $user->id);
    }
}
