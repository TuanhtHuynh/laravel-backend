<?php

namespace Tests\Feature;

use App\Models\ClockIn;
use App\Models\Role;
use App\Models\Staff;
use App\Models\StaffShift;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagerAttendanceTest extends TestCase
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

    private function roles(): array
    {
        return collect([
            'manager' => [Role::MANAGER, 'Quản lý'],
            'admin' => [Role::ADMIN, 'Quản trị'],
            'sales' => [Role::SALES, 'Bán hàng'],
        ])->map(fn ($r) => Role::query()->firstOrCreate(['slug' => $r[0]], ['name' => $r[1]])->refresh())->all();
    }

    public function test_manager_can_create_shift_for_staff_in_own_store(): void
    {
        $roles = $this->roles();
        $storeA = Store::factory()->create();
        $storeB = Store::factory()->create();
        $manager = $this->makeStaff($roles['manager'], $storeA);
        $staffA = $this->makeStaff($roles['sales'], $storeA);
        $staffB = $this->makeStaff($roles['sales'], $storeB);

        $this->actingAs($manager->user, 'sanctum')
            ->postJson('/api/attendance/shifts', [
                'staff_id' => $staffA->id,
                'shift_date' => '2026-10-25',
                'start_time' => '08:00',
                'end_time' => '12:00',
            ])
            ->assertCreated()
            ->assertJsonPath('hours', 4)
            ->assertJsonPath('staff_id', $staffA->id);

        // staff store khác -> 403
        $this->actingAs($manager->user, 'sanctum')
            ->postJson('/api/attendance/shifts', [
                'staff_id' => $staffB->id,
                'shift_date' => '2026-10-25',
                'start_time' => '08:00',
                'end_time' => '12:00',
            ])
            ->assertForbidden();
    }

    public function test_manager_can_update_and_delete_shift_of_own_store(): void
    {
        $roles = $this->roles();
        $storeA = Store::factory()->create();
        $manager = $this->makeStaff($roles['manager'], $storeA);
        $staffA = $this->makeStaff($roles['sales'], $storeA);

        $shift = StaffShift::create([
            'staff_id' => $staffA->id,
            'shift_date' => '2026-10-25',
            'start_time' => '08:00',
            'end_time' => '12:00',
            'hours' => 4,
        ]);

        $this->actingAs($manager->user, 'sanctum')
            ->putJson("/api/attendance/shifts/{$shift->id}", ['end_time' => '16:00'])
            ->assertOk()
            ->assertJsonPath('hours', 8);

        $this->actingAs($manager->user, 'sanctum')
            ->deleteJson("/api/attendance/shifts/{$shift->id}")
            ->assertOk();

        $this->assertDatabaseMissing('staff_shifts', ['id' => $shift->id]);
    }

    public function test_manager_can_manage_clock_in_records_of_own_store(): void
    {
        $roles = $this->roles();
        $storeA = Store::factory()->create();
        $storeB = Store::factory()->create();
        $manager = $this->makeStaff($roles['manager'], $storeA);
        $staffA = $this->makeStaff($roles['sales'], $storeA);
        $staffB = $this->makeStaff($roles['sales'], $storeB);

        // chấm công hộ staff cùng store
        $this->actingAs($manager->user, 'sanctum')
            ->postJson('/api/attendance/clock-in', ['staff_id' => $staffA->id])
            ->assertCreated()
            ->assertJsonPath('staff_id', $staffA->id);

        // chấm công hộ staff khác store -> 403
        $this->actingAs($manager->user, 'sanctum')
            ->postJson('/api/attendance/clock-in', ['staff_id' => $staffB->id])
            ->assertForbidden();

        $record = ClockIn::create([
            'staff_id' => $staffB->id,
            'clock_in' => '2026-10-25 09:00:00',
        ]);

        // manager sửa/xóa record store khác -> 403
        $this->actingAs($manager->user, 'sanctum')
            ->putJson("/api/attendance/clock-ins/{$record->id}", [
                'clock_out' => '2026-10-25 17:00:00',
            ])
            ->assertForbidden();

        $ownRecord = ClockIn::create([
            'staff_id' => $staffA->id,
            'clock_in' => '2026-10-25 09:00:00',
        ]);

        $this->actingAs($manager->user, 'sanctum')
            ->putJson("/api/attendance/clock-ins/{$ownRecord->id}", [
                'clock_in' => '2026-10-25 08:30:00',
                'clock_out' => '2026-10-25 17:00:00',
            ])
            ->assertOk();

        // clock_out <= clock_in -> 422
        $this->actingAs($manager->user, 'sanctum')
            ->putJson("/api/attendance/clock-ins/{$ownRecord->id}", [
                'clock_out' => '2026-10-25 07:00:00',
            ])
            ->assertStatus(422);
    }

    public function test_sales_cannot_manage_shifts(): void
    {
        $roles = $this->roles();
        $storeA = Store::factory()->create();
        $sales = $this->makeStaff($roles['sales'], $storeA);
        $staffA = $this->makeStaff($roles['sales'], $storeA);

        StaffShift::create([
            'staff_id' => $staffA->id,
            'shift_date' => '2026-10-25',
            'start_time' => '08:00',
            'end_time' => '12:00',
            'hours' => 4,
        ]);

        $this->actingAs($sales->user, 'sanctum')
            ->getJson('/api/attendance/shifts')
            ->assertForbidden();
    }

    public function test_manager_can_read_and_update_salary_of_own_store_staff(): void
    {
        $roles = $this->roles();
        $storeA = Store::factory()->create();
        $storeB = Store::factory()->create();
        $manager = $this->makeStaff($roles['manager'], $storeA);
        $staffA = $this->makeStaff($roles['sales'], $storeA);
        $staffB = $this->makeStaff($roles['sales'], $storeB);

        StaffShift::create([
            'staff_id' => $staffA->id,
            'shift_date' => '2026-10-25',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'hours' => 2,
        ]);

        $this->actingAs($manager->user, 'sanctum')
            ->putJson("/api/attendance/staff/{$staffA->id}/hourly-rate", ['hourly_rate' => 50000])
            ->assertOk()
            ->assertJsonPath('hourly_rate', 50000);

        $this->actingAs($manager->user, 'sanctum')
            ->getJson("/api/attendance/salary?staff_id={$staffA->id}")
            ->assertOk()
            ->assertJsonPath('total_hours', 2)
            ->assertJsonPath('total_salary', 100000);

        // staff khác store -> 403
        $this->actingAs($manager->user, 'sanctum')
            ->putJson("/api/attendance/staff/{$staffB->id}/hourly-rate", ['hourly_rate' => 50000])
            ->assertForbidden();
    }

    public function test_admin_can_manage_any_store(): void
    {
        $roles = $this->roles();
        $storeA = Store::factory()->create();
        $storeB = Store::factory()->create();
        $admin = $this->makeStaff($roles['admin'], $storeA);
        $staffB = $this->makeStaff($roles['sales'], $storeB);

        $this->actingAs($admin->user, 'sanctum')
            ->postJson('/api/attendance/shifts', [
                'staff_id' => $staffB->id,
                'shift_date' => '2026-10-25',
                'start_time' => '08:00',
                'end_time' => '12:00',
            ])
            ->assertCreated();

        $this->actingAs($admin->user, 'sanctum')
            ->putJson("/api/attendance/staff/{$staffB->id}/hourly-rate", ['hourly_rate' => 30000])
            ->assertOk();

        $this->actingAs($admin->user, 'sanctum')
            ->getJson("/api/attendance/salary?staff_id={$staffB->id}")
            ->assertOk()
            ->assertJsonPath('total_salary', 120000);
    }
}
