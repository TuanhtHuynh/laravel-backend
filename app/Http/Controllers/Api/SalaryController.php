<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Staff;
use App\Models\StaffShift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalaryController extends Controller
{
    public function calculate(Request $request)
    {
        $current = Auth::user()->staff;
        if (! $current) {
            return response()->json(['message' => 'Chưa có staff'], 403);
        }

        $staff = $current;
        if ($request->filled('staff_id')) {
            if ($current->role?->slug !== Role::MANAGER && $current->role?->slug !== Role::ADMIN) {
                return response()->json(['message' => 'Chỉ manager/admin'], 403);
            }

            $target = Staff::withTrashed()->find($request->input('staff_id'));
            if (! $target || ! $this->canManageStaff($current, $target)) {
                return response()->json(['message' => 'Staff thuộc store khác'], 403);
            }
            $staff = $target;
        }

        $hours = StaffShift::where('staff_id', $staff->id)->sum('hours');

        return response()->json([
            'staff_id' => $staff->id,
            'store_id' => $staff->store_id,
            'total_hours' => $hours,
            'hourly_rate' => $staff->hourly_rate,
            'total_salary' => round($hours * (float) ($staff->hourly_rate ?? 0), 2),
            'note' => 'Lương theo giờ từ staff_shifts x hourly_rate của staff',
        ]);
    }

    public function updateRate(Request $request, Staff $staff)
    {
        $current = Auth::user()->staff;
        if (! $current) {
            return response()->json(['message' => 'Chưa có staff'], 403);
        }

        $target = Staff::withTrashed()->find($staff->id);
        if (! $target || ! $this->canManageStaff($current, $target)) {
            return response()->json(['message' => 'Staff thuộc store khác'], 403);
        }

        $data = $request->validate([
            'hourly_rate' => 'required|numeric|min:0',
        ]);

        $target->update(['hourly_rate' => $data['hourly_rate']]);

        return response()->json([
            'staff_id' => $target->id,
            'hourly_rate' => $target->hourly_rate,
        ]);
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
