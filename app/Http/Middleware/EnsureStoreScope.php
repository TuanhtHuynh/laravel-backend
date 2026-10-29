<?php

namespace App\Http\Middleware;

use App\Models\ProductStock;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStoreScope
{
    /**
     * Giới hạn thao tác theo cửa hàng của nhân viên.
     *
     * Quy tắc nghiệp vụ:
     * - Nhân viên (kho, bán hàng) chỉ được thao tác dữ liệu thuộc cửa hàng của họ.
     * - Cửa hàng khác ⇒ 403, dù tài khoản hợp lệ.
     * - Quản trị (admin) là vai trò toàn cục nên được bỏ qua giới hạn này.
     *
     * Cách xác định cửa hàng đích (theo thứ tự ưu tiên):
     *  1. Route model `stock` (ProductStock) → stock.store_id
     *  2. Route model `store` (Store)       → store.id
     *  3. Input `store_id` (body hoặc query) → store.id
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        $staff = $user instanceof User ? $user->staff : null;
        $slug = $staff?->role?->slug;

        if ($staff === null || $slug === null) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }

        if ($roles !== [] && ! in_array($slug, $roles, true)) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }

        // Quản trị: phạm vi toàn hệ thống.
        if ($slug === Role::ADMIN) {
            $request->attributes->set('store_scope_id', $this->resolveStoreId($request));

            return $next($request);
        }

        $ownStoreId = $staff->store_id;
        $targetStoreId = $this->resolveStoreId($request);

        if ($targetStoreId !== null && $targetStoreId !== $ownStoreId) {
            abort(403, 'Bạn không có quyền thao tác dữ liệu của cửa hàng khác.');
        }

        // Nhân viên luôn bị ghim vào cửa hàng của mình.
        $request->attributes->set('store_scope_id', $ownStoreId);

        return $next($request);
    }

    private function resolveStoreId(Request $request): ?int
    {
        $stock = $request->route('stock');

        if ($stock instanceof ProductStock) {
            return $stock->store_id !== null ? (int) $stock->store_id : null;
        }

        if (is_numeric($stock)) {
            $row = ProductStock::query()->whereKey((int) $stock)->first();

            return $row?->store_id !== null ? (int) $row->store_id : null;
        }

        $store = $request->route('store');

        if ($store instanceof Store) {
            return (int) $store->id;
        }

        if (is_numeric($store)) {
            return (int) $store;
        }

        $input = $request->input('store_id') ?? $request->query('store_id');

        return is_numeric($input) ? (int) $input : null;
    }
}
