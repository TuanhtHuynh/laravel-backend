<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Staff;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    /**
     * Seed 2 cửa hàng + nhân viên kho của mỗi cửa hàng để kiểm thử phân quyền theo store.
     */
    public function run(): void
    {
        $warehouse = Role::where('slug', Role::WAREHOUSE)->first();
        $admin = Role::where('slug', Role::ADMIN)->first();

        $stores = [
            ['name' => 'Food-SG Cầu Giấy', 'address' => '123 Cầu Giấy, Hà Nội'],
            ['name' => 'Food-SG Nguyễn Huệ', 'address' => '45 Nguyễn Huệ, TP.HCM'],
        ];

        foreach ($stores as $index => $data) {
            $store = Store::firstOrCreate(['name' => $data['name']], ['address' => $data['address']]);

            $user = User::firstOrCreate(
                ['email' => 'warehouse'.($index + 1).'@food-sg.test'],
                ['name' => 'Kho '.($index + 1), 'password' => 'password'],
            );

            Staff::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => 'Kho '.($index + 1),
                    'store_id' => $store->id,
                    'role_id' => $warehouse?->id,
                ]
            );
        }

        $adminUser = User::firstOrCreate(
            ['email' => 'admin@food-sg.test'],
            ['name' => 'Quản trị', 'password' => 'password'],
        );

        Staff::firstOrCreate(
            ['user_id' => $adminUser->id],
            [
                'name' => 'Quản trị',
                'store_id' => Store::orderBy('id')->first()?->id,
                'role_id' => $admin?->id,
            ]
        );
    }
}
