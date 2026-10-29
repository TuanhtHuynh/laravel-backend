<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClockIn;
use App\Models\Role;
use App\Models\Staff;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClockInController extends Controller
{
    public function clockIn(Request $request)
    {
        $staff = $this->resolveStaff($request);
        if ($staff instanceof JsonResponse) {
            return $staff;
        }

        $record = ClockIn::create([
            'staff_id' => $staff->id,
            'clock_in' => now(),
        ]);

        return response()->json($record, 201);
    }

    public function clockOut(Request $request)
    {
        $staff = $this->resolveStaff($request);
        if ($staff instanceof JsonResponse) {
            return $staff;
        }

        $record = ClockIn::where('staff_id', $staff->id)
            ->whereNull('clock_out')
            ->latest()
            ->first();

        if (! $record) {
            return response()->json(['message' => 'Không có ca đang mở'], 404);
        }

        $record->update(['clock_out' => now()]);

        return response()->json($record);
    }

    // ===== Quản lý chấm công của staff khác (manager trong store, admin toàn hệ thống) =====

    public function index(Request $request)
    {
        $current = Auth::user()->staff;
        if (! $current) {
            return response()->json(['message' => 'Chưa có staff'], 403);
        }
        if (! $this->managerOrAdmin($current)) {
            return response()->json(['message' => 'Chỉ manager/admin'], 403);
        }

        $query = ClockIn::query()->latest('clock_in');

        if (($current->role?->slug) === Role::MANAGER) {
            $query->whereHas('staff', fn ($q) => $q->where('store_id', $current->store_id));
        }

        if ($request->filled('staff_id')) {
            $target = Staff::withTrashed()->find($request->input('staff_id'));
            if (! $this->canManageStaff($current, $target)) {
                return response()->json(['message' => 'Staff thuộc store khác'], 403);
            }
            $query->where('staff_id', $target->id);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $current = Auth::user()->staff;
        if (! $current) {
            return response()->json(['message' => 'Chưa có staff'], 403);
        }
        if (! $this->managerOrAdmin($current)) {
            return response()->json(['message' => 'Chỉ manager/admin'], 403);
        }

        $data = $request->validate([
            'staff_id' => 'required|integer',
            'clock_in' => 'required|date',
            'clock_out' => 'nullable|date|after:clock_in',
        ]);

        $target = Staff::withTrashed()->find($data['staff_id']);
        if (! $this->canManageStaff($current, $target)) {
            return response()->json(['message' => 'Staff thuộc store khác'], 403);
        }

        return response()->json(ClockIn::create($data), 201);
    }

    public function update(Request $request, ClockIn $clock_in)
    {
        $current = Auth::user()->staff;
        if (! $current) {
            return response()->json(['message' => 'Chưa có staff'], 403);
        }
        if (! $this->canManageStaff($current, $clock_in->staff)) {
            return response()->json(['message' => 'Staff thuộc store khác'], 403);
        }

        $data = $request->validate([
            'clock_in' => 'sometimes|date',
            'clock_out' => 'sometimes|nullable|date',
        ]);

        $clockInTime = $data['clock_in'] ?? $clock_in->clock_in;
        $clockOutTime = $data['clock_out'] ?? $clock_in->clock_out;
        if ($clockInTime && $clockOutTime && strtotime($clockOutTime) <= strtotime($clockInTime)) {
            return response()->json(['message' => 'clock_out phải sau clock_in'], 422);
        }

        $clock_in->update($data);

        return response()->json($clock_in);
    }

    public function destroy(Request $request, ClockIn $clock_in)
    {
        $current = Auth::user()->staff;
        if (! $current) {
            return response()->json(['message' => 'Chưa có staff'], 403);
        }
        if (! $this->canManageStaff($current, $clock_in->staff)) {
            return response()->json(['message' => 'Staff thuộc store khác'], 403);
        }

        $clock_in->delete();

        return response()->json(['message' => 'Đã xóa bản ghi chấm công']);
    }

    private function resolveStaff(Request $request): Staff|JsonResponse
    {
        $current = Auth::user()->staff;
        if (! $current) {
            return response()->json(['message' => 'Chưa có staff'], 403);
        }

        // self mặc định; manager/admin có thể chấm hộ staff khác cùng store (admin: mọi store)
        if ($request->filled('staff_id') && $this->managerOrAdmin($current)) {
            $target = Staff::withTrashed()->find($request->input('staff_id'));
            if (! $this->canManageStaff($current, $target)) {
                return response()->json(['message' => 'Staff thuộc store khác'], 403);
            }

            return $target;
        }

        return $current;
    }

    private function managerOrAdmin(Staff $staff): bool
    {
        $slug = $staff->role?->slug;

        return $slug === Role::MANAGER || $slug === Role::ADMIN;
    }

    private function canManageStaff(Staff $current, ?Staff $target): bool
    {
        if (! $target) {
            return false;
        }

        $slug = $current->role?->slug;

        if ($slug === Role::ADMIN) {
            return true;
        }

        return $slug === Role::MANAGER
            && $current->store_id !== null
            && $current->store_id === $target->store_id;
    }
}
