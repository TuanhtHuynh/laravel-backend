<?php

namespace App\Http\Controllers\Api\Guest;

use App\Http\Controllers\Concerns\RemembersCache;
use App\Http\Controllers\Controller;
use App\Http\Requests\Guest\ProductIndexRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use RemembersCache;

    public function index(ProductIndexRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $page = max(1, (int) ($validated['page'] ?? 1));
        $perPage = (int) ($validated['per_page'] ?? 12);
        $categorySlug = $validated['category'] ?? null;
        $storeId = $validated['store_id'] ?? null;

        $payload = $this->rememberCached(
            'products',
            "home:{$categorySlug}:{$storeId}:{$page}:{$perPage}",
            fn (): array => ProductResource::collection(
                Product::query()
                    ->with('category')
                    ->with(['stocks' => fn ($query) => $query->when(
                        $storeId !== null,
                        fn ($query) => $query->where('store_id', $storeId),
                    )])
                    ->when($categorySlug !== null && $categorySlug !== '', fn ($query) => $query->whereHas(
                        'category',
                        fn ($query) => $query->where('slug', $categorySlug),
                    ))
                    ->when($storeId !== null, fn ($query) => $query->whereHas(
                        'stocks',
                        fn ($query) => $query->where('store_id', $storeId),
                    ))
                    ->latest('id')
                    ->paginate($perPage)
                    ->withQueryString()
            )->response()->getData(true),
        );

        return response()->json($payload);
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $storeId = $request->query('store_id');
        $storeId = is_numeric($storeId) ? (int) $storeId : null;

        $payload = $this->rememberCached('products', "detail:{$slug}:{$storeId}", function () use ($slug, $storeId): ?array {
            $product = Product::query()
                ->with(['category', 'sizes', 'ingredients'])
                ->with(['stocks' => fn ($query) => $query->when(
                    $storeId !== null,
                    fn ($query) => $query->where('store_id', $storeId),
                )])
                ->where('slug', $slug)
                ->first();

            if ($product === null) {
                return null;
            }

            return (new ProductResource($product))->response()->getData(true);
        });

        abort_if($payload === null, 404, 'Không tìm thấy sản phẩm.');

        return response()->json($payload);
    }
}
