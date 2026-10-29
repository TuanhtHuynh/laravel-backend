<?php

namespace App\Http\Controllers\Api\Stock;

use App\Http\Controllers\Concerns\RemembersCache;
use App\Http\Controllers\Controller;
use App\Http\Requests\Stock\StockIndexRequest;
use App\Http\Requests\Stock\StockRequest;
use App\Http\Resources\StockResource;
use App\Models\ProductStock;
use App\Models\Role;
use App\Models\Staff;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class StockController extends Controller
{
    use RemembersCache;

    /**
     * Danh sách tồn kho.
     *
     * - Nhân viên (kho/bán hàng): chỉ thấy tồn kho của cửa hàng mình.
     * - Quản trị: thấy toàn bộ, có thể lọc theo store_id.
     */
    public function index(StockIndexRequest $request): JsonResponse
    {
        $staff = $request->user()->staff;
        $isAdmin = $staff->role->slug === Role::ADMIN;
        $storeId = $isAdmin ? $request->validated('store_id') : $staff->store_id;

        $keyword = $request->validated('q') ?? '';

        $stocks = ProductStock::query()
            ->with(['product', 'store', 'staff'])
            ->when($storeId !== null, fn ($query) => $query->forStore($storeId))
            ->when($request->validated('product_id') !== null, fn ($query) => $query->where(
                'product_id',
                $request->validated('product_id')
            ))
            ->when($keyword !== '', fn ($query) => $query->whereHas('product', fn ($query) => $query
                ->where('name', 'like', "%{$keyword}%")
                ->orWhere('slug', 'like', "%{$keyword}%")))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return StockResource::collection($stocks)->response();
    }

    public function store(StockRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $staff = $request->user()->staff;
        $isAdmin = $request->isAdmin();

        $storeId = $isAdmin
            ? ($validated['store_id'] ?? null)
            : $staff->store_id;

        if ($storeId === null) {
            throw ValidationException::withMessages([
                'store_id' => ['Vui lòng chọn cửa hàng cần quản lý tồn kho.'],
            ]);
        }

        $staffId = $isAdmin
            ? ($validated['staff_id'] ?? null)
            : $staff->id;

        $this->guardStaffBelongsToStore($staffId, $storeId);

        $attributes = [
            'product_id' => $validated['product_id'],
            'store_id' => $storeId,
        ];

        $exists = ProductStock::query()->where($attributes)->exists();

        $stock = ProductStock::updateOrCreate(
            $attributes,
            [
                'quantity' => $validated['quantity'],
                'staff_id' => $staffId,
            ]
        );

        $this->flushApiCache();

        $response = (new StockResource($stock->load(['product', 'store', 'staff'])))->response();

        return $response->setStatusCode($exists ? 200 : 201);
    }

    public function show(ProductStock $stock): JsonResponse
    {
        return (new StockResource($stock->load(['product', 'store', 'staff'])))->response();
    }

    public function update(StockRequest $request, ProductStock $stock): JsonResponse
    {
        $validated = $request->validated();
        $staff = $request->user()->staff;
        $isAdmin = $request->isAdmin();

        $staffId = $isAdmin
            ? ($validated['staff_id'] ?? $stock->staff_id)
            : $staff->id;

        $this->guardStaffBelongsToStore($staffId, $stock->store_id);

        $stock->update([
            'quantity' => $validated['quantity'],
            'staff_id' => $staffId,
        ]);

        $this->flushApiCache();

        return (new StockResource($stock->fresh()->load(['product', 'store', 'staff'])))->response();
    }

    public function destroy(ProductStock $stock): JsonResponse
    {
        $stock->delete();

        $this->flushApiCache();

        return response()->json(['message' => 'Đã xóa bản ghi tồn kho.']);
    }

    /**
     * Nhân viên được gán phụ trách tồn kho phải thuộc đúng cửa hàng của bản ghi kho.
     */
    private function guardStaffBelongsToStore(?int $staffId, ?int $storeId): void
    {
        if ($staffId === null || $storeId === null) {
            return;
        }

        $staff = Staff::find($staffId);

        if ($staff === null || $staff->store_id !== $storeId) {
            throw ValidationException::withMessages([
                'staff_id' => ['Nhân viên không thuộc cửa hàng đang chọn.'],
            ]);
        }
    }
}
