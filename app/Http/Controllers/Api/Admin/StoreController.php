<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Concerns\RemembersCache;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexRequest;
use App\Http\Requests\Admin\SearchRequest;
use App\Http\Requests\Admin\StoreRequest;
use App\Http\Resources\StockResource;
use App\Http\Resources\StoreResource;
use App\Models\Store;
use Illuminate\Http\JsonResponse;

class StoreController extends Controller
{
    use RemembersCache;

    public function index(IndexRequest $request): JsonResponse
    {
        $keyword = $request->validated('q') ?? '';

        $stores = Store::query()
            ->when($keyword !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$keyword}%")
                ->orWhere('address', 'like', "%{$keyword}%")))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return StoreResource::collection($stores)->response();
    }

    public function search(SearchRequest $request): JsonResponse
    {
        $keyword = $request->validated('q');

        $stores = Store::query()
            ->where(fn ($query) => $query
                ->where('name', 'like', "%{$keyword}%")
                ->orWhere('address', 'like', "%{$keyword}%"))
            ->orderBy('name')
            ->limit(50)
            ->get();

        return StoreResource::collection($stores)->response();
    }

    public function store(StoreRequest $request): JsonResponse
    {
        $store = Store::create($request->validated());
        $this->flushApiCache();

        return (new StoreResource($store))->response();
    }

    public function show(Store $store): JsonResponse
    {
        return (new StoreResource($store->loadCount('staffs')))->response();
    }

    /**
     * Danh sách sản phẩm (kèm tồn kho) đang phục vụ tại cửa hàng này.
     */
    public function products(Store $store): JsonResponse
    {
        $stocks = $store->stocks()
            ->with(['product.category', 'staff'])
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return StockResource::collection($stocks)->response();
    }

    public function update(StoreRequest $request, Store $store): JsonResponse
    {
        $store->update($request->validated());
        $this->flushApiCache();

        return (new StoreResource($store->fresh()))->response();
    }

    public function destroy(Store $store): JsonResponse
    {
        $store->delete();
        $this->flushApiCache();

        return response()->json(['message' => 'Đã xóa cửa hàng.']);
    }
}
