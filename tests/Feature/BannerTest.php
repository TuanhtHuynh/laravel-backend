<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Role;
use App\Models\Staff;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BannerTest extends TestCase
{
    use RefreshDatabase;

    private function adminStaff(): Staff
    {
        $role = Role::query()->firstOrCreate(['slug' => Role::ADMIN], ['name' => 'Quản trị']);
        $store = Store::factory()->create();
        $user = User::factory()->create();

        return Staff::factory()->create([
            'user_id' => $user->id,
            'role_id' => $role->id,
            'store_id' => $store->id,
        ]);
    }

    public function test_guest_sees_banners_sorted_by_sort_order(): void
    {
        Banner::create(['image' => '/storage/banners/b.jpg', 'title' => 'B', 'sort_order' => 2]);
        Banner::create(['image' => '/storage/banners/a.jpg', 'title' => 'A', 'sort_order' => 1]);

        $response = $this->getJson('/api/banners');

        $response->assertOk();
        $response->assertJsonPath('data.0.image', '/storage/banners/a.jpg');
        $response->assertJsonPath('data.1.image', '/storage/banners/b.jpg');
        $response->assertJsonCount(2, 'data');
    }

    public function test_guest_cannot_see_soft_deleted_banner(): void
    {
        $banner = Banner::create(['image' => '/storage/banners/c.jpg', 'title' => 'C', 'sort_order' => 1]);
        $banner->delete();

        $this->getJson('/api/banners')->assertJsonCount(0, 'data');
    }

    public function test_admin_can_create_update_delete_banner(): void
    {
        $admin = $this->adminStaff();

        $created = $this->actingAs($admin->user, 'sanctum')->postJson('/api/admin/banners', [
            'image' => '/storage/banners/new.jpg',
            'title' => 'Khuyến mãi',
            'link_url' => 'https://example.com',
        ]);

        $created->assertCreated();
        $created->assertJsonPath('data.title', 'Khuyến mãi');
        $created->assertJsonPath('data.sort_order', 1);

        $bannerId = $created->json('data.id');

        $this->actingAs($admin->user, 'sanctum')
            ->putJson("/api/admin/banners/{$bannerId}", ['title' => 'Mới', 'sort_order' => 5])
            ->assertOk()
            ->assertJsonPath('data.title', 'Mới')
            ->assertJsonPath('data.sort_order', 5);

        // q = "Mới" (url-encode; tìm theo title đã update)
        $this->actingAs($admin->user, 'sanctum')
            ->getJson('/api/admin/banners/search?q=M%E1%BB%9Bi')
            ->assertOk()
            ->assertJsonPath('data.0.id', $bannerId);

        $this->actingAs($admin->user, 'sanctum')
            ->deleteJson("/api/admin/banners/{$bannerId}")
            ->assertOk();

        $this->assertSoftDeleted('banners', ['id' => $bannerId]);
    }

    public function test_staff_non_admin_cannot_manage_banners(): void
    {
        $salesRole = Role::query()->firstOrCreate(['slug' => Role::SALES], ['name' => 'Bán hàng']);
        $store = Store::factory()->create();
        $user = User::factory()->create();
        $staff = Staff::factory()->create([
            'user_id' => $user->id,
            'role_id' => $salesRole->id,
            'store_id' => $store->id,
        ]);

        $this->actingAs($staff->user, 'sanctum')
            ->postJson('/api/admin/banners', ['image' => '/storage/banners/x.jpg'])
            ->assertForbidden();
    }

    public function test_banner_seeder_copies_images_and_is_idempotent(): void
    {
        Storage::fake('public');

        $this->artisan('db:seed', ['--class' => 'BannerImageSeeder']);
        $this->artisan('db:seed', ['--class' => 'BannerImageSeeder']);

        foreach (['top-1.jpg', 'top-2.jpg', 'top-3.jpg', 'top-4.jpg'] as $file) {
            Storage::disk('public')->assertExists('banners/'.$file);
        }

        $this->assertSame(4, Banner::count());

        $orders = Banner::query()->orderBy('sort_order')->pluck('sort_order')->values();
        $this->assertSame([1, 2, 3, 4], $orders->all());
    }
}
