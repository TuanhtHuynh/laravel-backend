<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_copies_images_into_storage_and_fills_products_image(): void
    {
        Storage::fake('public');

        $this->artisan('db:seed', ['--class' => 'ProductImageSeeder'])->assertExitCode(0);

        $disk = Storage::disk('public');

        // 4 ảnh từ Resources_A phải vào storage/app/public/products
        foreach (['grillded_beef.jpg', 'mi_trung_001.jpg', 'rice_ball.jpg', 'spicy.-noodle.jpg'] as $file) {
            $disk->assertExists('products/'.$file);
        }

        $products = Product::query()->whereNotNull('image')->get();
        $this->assertCount(4, $products);
        $products->each(fn (Product $p) => $this->assertStringStartsWith('/storage/products/', $p->image));
    }

    public function test_seeder_is_idempotent(): void
    {
        Storage::fake('public');

        $this->artisan('db:seed', ['--class' => 'ProductImageSeeder']);
        $this->artisan('db:seed', ['--class' => 'ProductImageSeeder']);

        $this->assertSame(4, Product::whereNotNull('image')->count());
    }
}
