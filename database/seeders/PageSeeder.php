<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        Page::firstOrCreate(
            ['slug' => 'gioi-thieu'],
            [
                'title' => 'Giới thiệu',
                'content' => '<p>Food SG — Developed a food management system for managing food, staff, stores, and products.</p>',
            ]
        );
    }
}
