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
                'image' => 'https://i.ibb.co/HDpKjFq0/60d0afb1c3b3.jpg',
                'title' => 'Banner 1',
                'link_url' => NULL,
                'sort_order' => 10,
            ],
            [
                'image' => 'https://i.ibb.co/5b3Ck7x/9c950069b413.jpg',
                'title' => 'Banner 2',
                'link_url' => NULL,
                'sort_order' => 11,
            ],
            [
                'image' => 'https://i.ibb.co/VY5MbCgg/6766877d5a68.jpg',
                'title' => 'Banner 3',
                'link_url' => NULL,
                'sort_order' => 12,
            ],
            [
                'image' => 'https://i.ibb.co/dJzzKK6X/7cf7159b267f.jpg',
                'title' => 'Banner 4',
                'link_url' => NULL,
                'sort_order' => 13,
            ],
            [
                'image' => 'https://i.ibb.co/7dvFKQZv/18159fc320e5.jpg',
                'title' => 'Banner 5',
                'link_url' => NULL,
                'sort_order' => 14,
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
