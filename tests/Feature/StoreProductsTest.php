<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Role;
use App\Models\Staff;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreProductsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_see_products_available_in_a_store(): void
    {
        $store = Store::factory()->create();
        $otherStore = Store::factory()->create();
        $product = Product::factory()->create(['category_id' => Category::factory()->create()->id]);

        ProductStock::factory()->create([
            'product_id' => $product->id,
            'store_id' => $store->id,
            'quantity' => 5,
        ]);

        ProductStock::factory()->create([
            'product_id' => $product->id,
            'store_id' => $otherStore->id,
            'quantity' => 0,
        ]);

        $response = $this->getJson("/api/stores/{$store->id}/products");

        $response->assertOk();
        $response->assertJsonPath('store.id', $store->id);
        $response->assertJsonCount(1, 'products');
        $response->assertJsonPath('products.0.quantity', 5);
    }

    public function test_guest_product_list_can_be_filtered_by_store(): void
    {
        $store = Store::factory()->create();
        $otherStore = Store::factory()->create();
        $product = Product::factory()->create(['category_id' => Category::factory()->create()->id]);

        ProductStock::factory()->create([
            'product_id' => $product->id,
            'store_id' => $store->id,
            'quantity' => 3,
        ]);

        $response = $this->getJson("/api/products?store_id={$store->id}");

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $product->id);
        $response->assertJsonCount(1, 'data.0.stocks');
    }

    public function test_admin_can_list_products_of_a_store(): void
    {
        $adminRole = Role::factory()->create(['slug' => Role::ADMIN]);
        $store = Store::factory()->create();
        $user = User::factory()->create();
        $admin = Staff::factory()->create([
            'user_id' => $user->id,
            'role_id' => $adminRole->id,
            'store_id' => $store->id,
        ]);

        $product = Product::factory()->create(['category_id' => Category::factory()->create()->id]);

        ProductStock::factory()->create([
            'product_id' => $product->id,
            'store_id' => $store->id,
            'quantity' => 12,
        ]);

        $response = $this->actingAs($admin->user, 'sanctum')
            ->getJson("/api/admin/stores/{$store->id}/products");

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.quantity', 12);
        $response->assertJsonPath('data.0.store_id', $store->id);
    }
}
