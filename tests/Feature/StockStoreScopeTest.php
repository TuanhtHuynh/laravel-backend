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

class StockStoreScopeTest extends TestCase
{
    use RefreshDatabase;

    private function makeStaff(Role $role, Store $store): Staff
    {
        $user = User::factory()->create();

        return Staff::factory()->create([
            'user_id' => $user->id,
            'role_id' => $role->id,
            'store_id' => $store->id,
        ]);
    }

    public function test_warehouse_only_sees_stock_of_own_store(): void
    {
        $warehouseRole = Role::factory()->create(['slug' => Role::WAREHOUSE]);
        $storeA = Store::factory()->create();
        $storeB = Store::factory()->create();
        $staffA = $this->makeStaff($warehouseRole, $storeA);

        $product = Product::factory()->create(['category_id' => Category::factory()->create()->id]);

        ProductStock::factory()->create([
            'product_id' => $product->id,
            'store_id' => $storeA->id,
            'staff_id' => $staffA->id,
            'quantity' => 10,
        ]);

        ProductStock::factory()->create([
            'product_id' => $product->id,
            'store_id' => $storeB->id,
            'quantity' => 99,
        ]);

        $response = $this->actingAs($staffA->user, 'sanctum')->getJson('/api/stock');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.store_id', $storeA->id);
    }

    public function test_warehouse_cannot_read_stock_of_other_store(): void
    {
        $warehouseRole = Role::factory()->create(['slug' => Role::WAREHOUSE]);
        $storeA = Store::factory()->create();
        $storeB = Store::factory()->create();
        $staffA = $this->makeStaff($warehouseRole, $storeA);

        $product = Product::factory()->create(['category_id' => Category::factory()->create()->id]);

        $stockB = ProductStock::factory()->create([
            'product_id' => $product->id,
            'store_id' => $storeB->id,
            'quantity' => 50,
        ]);

        $this->actingAs($staffA->user, 'sanctum')
            ->getJson("/api/stock/{$stockB->id}")
            ->assertForbidden();
    }

    public function test_warehouse_cannot_write_stock_for_other_store(): void
    {
        $warehouseRole = Role::factory()->create(['slug' => Role::WAREHOUSE]);
        $storeA = Store::factory()->create();
        $storeB = Store::factory()->create();
        $staffA = $this->makeStaff($warehouseRole, $storeA);

        $product = Product::factory()->create(['category_id' => Category::factory()->create()->id]);

        $this->actingAs($staffA->user, 'sanctum')
            ->postJson('/api/stock', [
                'product_id' => $product->id,
                'store_id' => $storeB->id,
                'quantity' => 5,
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('product_stock', [
            'product_id' => $product->id,
            'store_id' => $storeB->id,
        ]);
    }

    public function test_warehouse_writes_stock_into_own_store(): void
    {
        $warehouseRole = Role::factory()->create(['slug' => Role::WAREHOUSE]);
        $storeA = Store::factory()->create();
        $staffA = $this->makeStaff($warehouseRole, $storeA);

        $product = Product::factory()->create(['category_id' => Category::factory()->create()->id]);

        $response = $this->actingAs($staffA->user, 'sanctum')
            ->postJson('/api/stock', [
                'product_id' => $product->id,
                'quantity' => 7,
            ]);

        $response->assertCreated();
        $response->assertJsonPath('data.store_id', $storeA->id);
        $response->assertJsonPath('data.staff_id', $staffA->id);
        $response->assertJsonPath('data.quantity', 7);
    }

    public function test_admin_sees_all_stores_and_can_filter(): void
    {
        $adminRole = Role::factory()->create(['slug' => Role::ADMIN]);
        $storeA = Store::factory()->create();
        $storeB = Store::factory()->create();
        $admin = $this->makeStaff($adminRole, $storeA);

        $product = Product::factory()->create(['category_id' => Category::factory()->create()->id]);

        ProductStock::factory()->create([
            'product_id' => $product->id,
            'store_id' => $storeA->id,
            'quantity' => 1,
        ]);
        ProductStock::factory()->create([
            'product_id' => $product->id,
            'store_id' => $storeB->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($admin->user, 'sanctum')->getJson('/api/stock');
        $response->assertOk();
        $response->assertJsonCount(2, 'data');

        $filtered = $this->actingAs($admin->user, 'sanctum')->getJson("/api/stock?store_id={$storeB->id}");
        $filtered->assertOk();
        $filtered->assertJsonCount(1, 'data');
        $filtered->assertJsonPath('data.0.store_id', $storeB->id);
    }

    public function test_sales_role_cannot_write_stock(): void
    {
        $salesRole = Role::factory()->create(['slug' => Role::SALES]);
        $storeA = Store::factory()->create();
        $staffA = $this->makeStaff($salesRole, $storeA);

        $product = Product::factory()->create(['category_id' => Category::factory()->create()->id]);

        $this->actingAs($staffA->user, 'sanctum')
            ->postJson('/api/stock', [
                'product_id' => $product->id,
                'quantity' => 3,
            ])
            ->assertForbidden();
    }

    public function test_guest_cannot_access_stock(): void
    {
        $this->getJson('/api/stock')->assertUnauthorized();
    }
}
