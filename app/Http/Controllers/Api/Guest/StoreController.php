<?php

namespace App\Http\Controllers\Api\Guest;

use App\Http\Controllers\Concerns\RemembersCache;
use App\Http\Controllers\Controller;
use App\Http\Resources\StockResource;
use App\Http\Resources\StoreResource;
use App\Models\Store;
use Illuminate\Http\JsonResponse;

class StoreController extends Controller
{
    use RemembersCache;

    public function index(): JsonResponse
    {
        $payload = $this->rememberCached(
            'stores',
            'home',
            fn (): array => StoreResource::collection(Store::query()->orderBy('name')->get())->response()->getData(true),
        );

        return response()->json($payload);
    }

    public function show(int $id): JsonResponse
    {
        $payload = $this->rememberCached('stores', "detail:{$id}", function () use ($id): ?array {
            $store = Store::find($id);

            if ($store === null) {
                return null;
            }

            return (new StoreResource($store))->response()->getData(true);
        });

        abort_if($payload === null, 404, 'Không tìm thấy cửa hàng.');

        return response()->json($payload);
    }

    /**
     * Danh sách sản phẩm đang có tồn kho tại cửa hàng (kèm số lượng tồn).
     */
    public function products(int $id): JsonResponse
    {
        $payload = $this->rememberCached('stores', "products:{$id}", function () use ($id): ?array {
            $store = Store::find($id);

            if ($store === null) {
                return null;
            }

            $stocks = $store->stocks()
                ->with('product.category')
                ->where('quantity', '>', 0)
                ->latest('id')
                ->get();

            return [
                'store' => (new StoreResource($store))->resolve(request()),
                'products' => StockResource::collection($stocks)->resolve(request()),
            ];
        });

        abort_if($payload === null, 404, 'Không tìm thấy cửa hàng.');

        return response()->json($payload);
    }
}
