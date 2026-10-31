<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        Category::firstOrCreate(['slug' => 'mon-chinh'], ['name' => 'Món chính']);
        Category::firstOrCreate(['slug' => 'do-uong'], ['name' => 'Đồ uống']);
    }
}
