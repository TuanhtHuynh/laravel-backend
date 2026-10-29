<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Staff;
use App\Models\StaffShift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffShiftController extends Controller
{
    public function index(Request $request)
    {
        $current = Auth::user()->staff;
        if (! $current) {
            return response()->json(['message' => 'Chưa có staff'], 403);
        }

        $allowed = $this->managerOrAdmin($current);
        if (! $allowed) {
            return response()->json(['message' => 'Chỉ manager/admin'], 403);
        }

        $query = StaffShift::query()->orderBy('shift_date', 'desc');

        if ($current->role && $current->role->slug === Role::MANAGER) {
            $query->whereHas('staff', fn ($q) => $q->where('store_id', $current->store_id));
        }

        if ($request->filled('staff_id')) {
            $target = Staff::withTrashed()->find($request->input('staff_id'));
            if ($target && ! $this->canManageStaff($current, $target)) {
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
            'shift_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'hours' => 'nullable|numeric|min:0',
        ]);

        $target = Staff::withTrashed()->find($data['staff_id']);
        if (! $target || ! $this->canManageStaff($current, $target)) {
            return response()->json(['message' => 'Staff khác store'], 403);
        }

        $data['hours'] ??= $this->calcHours($data['start_time'], $data['end_time']);
        $shift = StaffShift::create($data);

        return response()->json($shift, 201);
    }

    public function update(Request $request, StaffShift $shift)
    {
        $current = Auth::user()->staff;
        if (! $current) {
            return response()->json(['message' => 'Chưa có staff'], 403);
        }
        if (! $this->canManageStaff($current, $shift->staff)) {
            return response()->json(['message' => 'Staff thuộc store khác'], 403);
        }

        $data = $request->validate([
            'staff_id' => 'sometimes|integer',
            'shift_date' => 'sometimes|date',
            'start_time' => 'sometimes|date_format:H:i',
            'end_time' => 'sometimes|date_format:H:i',
            'hours' => 'sometimes|nullable|numeric|min:0',
        ]);

        if (isset($data['staff_id'])) {
            $target = Staff::withTrashed()->find($data['staff_id']);
            if (! $target || ! $this->canManageStaff($current, $target)) {
                return response()->json(['message' => 'Staff khác store'], 403);
            }
        }

        $startTime = $data['start_time'] ?? $shift->start_time;
        $endTime = $data['end_time'] ?? $shift->end_time;
        if (isset($data['start_time']) || isset($data['end_time'])) {
            $data['hours'] = $data['hours'] ?? $this->calcHours($startTime, $endTime);
        }

        $shift->update($data);

        return response()->json($shift);
    }

    public function destroy(Request $request, StaffShift $shift)
    {
        $current = Auth::user()->staff;
        if (! $current) {
            return response()->json(['message' => 'Chưa có staff'], 403);
        }
        if (! $this->canManageStaff($current, $shift->staff)) {
            return response()->json(['message' => 'Staff thuộc store khác'], 403);
        }

        $shift->delete();

        return response()->json(['message' => 'Đã xóa ca']);
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

    private function calcHours(string $startTime, string $endTime): float
    {
        $base = '1970-01-01';
        $seconds = strtotime("$base $endTime") - strtotime("$base $startTime");
        if ($seconds < 0) {
            $seconds += 24 * 3600; // ca qua đêm
        }

        return round($seconds / 3600, 2);
    }
}
