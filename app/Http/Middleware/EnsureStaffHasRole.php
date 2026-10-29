<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaffHasRole
{
    /**
     * Yêu cầu staff đang đăng nhập có một trong các role chỉ định.
     *
     * Ví dụ: Route::middleware('role:warehouse,sales')
     * Không truyền role -> chỉ yêu cầu staff có role.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        $slug = $user instanceof User ? $user->staff?->role?->slug : null;

        if ($slug === null || ($roles !== [] && ! in_array($slug, $roles, true))) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }

        return $next($request);
    }
}
