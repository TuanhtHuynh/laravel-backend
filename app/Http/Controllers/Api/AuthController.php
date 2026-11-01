<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->validated())) {
            return response()->json([
                'success' => false,
                'message' => 'Thông tin đăng nhập không đúng.',
                'errors' => ['email' => ['Thông tin đăng nhập không đúng.']],
            ], 401);
        }

        $user = $this->user($request);
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => (new UserResource($user->load(['staff.role', 'staff.store'])))->resolve(),
            'role' => $user->staff->role->slug ?? null,
            'permissions' => $this->permissions($user),
        ])->withCookie('jwt_token', $token, 60 * 24, '/', null, true, true, 'None');
    }

    public function me(Request $request): JsonResponse
    {
        $user = $this->user($request);

        return response()->json([
            'user' => $this->userResource($request),
            'role' => $user->staff->role->slug ?? null,
            'permissions' => $this->permissions($user),
        ]);
    }

    public function profile(Request $request): JsonResponse
    {
        $user = $this->user($request);

        return response()->json([
            'user' => $this->userResource($request),
            'role' => $user->staff->role->slug ?? null,
            'permissions' => $this->permissions($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->bearerToken() ?: $request->cookie('jwt_token');

        if ($token) {
            JWTAuth::setToken($token)->invalidate();
        }

        return response()->json([
            'message' => 'Đăng xuất thành công.',
        ])->withCookie('jwt_token', null, -1, '/', null, true, true, 'None');
    }

    private function userResource(Request $request): array
    {
        return (new UserResource($this->user($request)->load(['staff.role', 'staff.store'])))->resolve();
    }

    private function permissions(User $user): array
    {
        $slug = $user->staff->role->slug ?? 'guest';

        return match ($slug) {
            'admin' => ['admin', 'stock.read', 'stock.create', 'stock.update', 'stock.delete', 'attendance.read', 'attendance.create', 'attendance.update', 'attendance.delete', 'product.admin', 'category.admin', 'store.admin', 'size.admin', 'ingredient.admin', 'banner.admin', 'staff.manage', 'user.profile', 'clock-in', 'clock-out', 'public.read'],
            'manager' => ['manager', 'store.read', 'stock.read', 'attendance.read', 'attendance.create', 'attendance.update', 'attendance.delete', 'staff.manage', 'user.profile', 'clock-in', 'clock-out', 'public.read'],
            'warehouse' => ['warehouse', 'store.read', 'stock.read', 'stock.create', 'stock.update', 'stock.delete', 'attendance.read', 'user.profile', 'clock-in', 'clock-out', 'public.read'],
            'sales' => ['sales', 'store.read', 'stock.read', 'attendance.read', 'user.profile', 'clock-in', 'clock-out', 'public.read'],
            default => ['guest', 'public.read'],
        };
    }

    private function user(Request $request): User
    {
        $user = $request->user();

        if (! $user instanceof User) {
            throw new AuthenticationException;
        }

        return $user;
    }
}
