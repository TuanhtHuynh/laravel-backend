<?php

use App\Models\Size;
use Illuminate\Database\Seeder;

class SizeSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'name' => 'Vừa',
            ],
            [
                'name' => 'Lớn',
            ],
        ];

        foreach ($data as $item) {
            Size::updateOrCreate(
                ['name' => $item['name']],
                $item
            );
        }
    }
}
