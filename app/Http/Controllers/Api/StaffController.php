<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $staff = $user->staff;

        if (!$staff) {
            return response()->json(['message' => 'Không phải staff'], 403);
        }

        $query = Staff::with('user', 'store');
        if ($staff->role && $staff->role->name !== 'admin') {
            $query->where('store_id', $staff->store_id);
        }

        return response()->json($query->get());
    }

    public function show(Request $request, Staff $staff)
    {
        $current = Auth::user()->staff;
        if ($current && $current->role && $current->role->name !== 'admin') {
            if ($current->store_id != $staff->store_id) {
                return response()->json(['message' => 'Khác store'], 403);
            }
        }
        return response()->json($staff->load('user', 'store', 'role'));
    }
}
