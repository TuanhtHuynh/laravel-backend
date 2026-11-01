<?php
namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'image' => 'banners/pho-bg.jpg',
                'title' => 'Phở Bò Banner',
                'link_url' => 'https://example.com',
                'sort_order' => 0,
            ],
            [
                'image' => 'banners/top-1.jpg',
                'title' => 'Top 1',
                'link_url' => NULL,
                'sort_order' => 1,
            ],
            [
                'image' => 'banners/top-2.jpg',
                'title' => 'Top 2',
                'link_url' => NULL,
                'sort_order' => 2,
            ],
            [
                'image' => 'banners/top-3.jpg',
                'title' => 'Top 3',
                'link_url' => NULL,
                'sort_order' => 3,
            ],
            [
                'image' => 'banners/top-4.jpg',
                'title' => 'Top 4',
                'link_url' => NULL,
                'sort_order' => 4,
            ],
            [
                'image' => 'banners/place.jpg',
                'title' => 'place',
                'link_url' => NULL,
                'sort_order' => 0,
            ],
            [
                'image' => 'banners/cinamon-sugar-raisin-bg.jpg',
                'title' => 'Dessert',
                'link_url' => NULL,
                'sort_order' => 0,
            ],
        ];

        foreach ($data as $item) {
            Banner::updateOrCreate(
                ['image' => $item['image']],
                $item
            );
        }
    }
}
