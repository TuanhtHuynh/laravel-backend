<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Concerns\RemembersCache;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductIndexRequest;
use App\Http\Requests\Admin\ProductRequest;
use App\Http\Requests\Admin\SearchRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    use RemembersCache;

    public function index(ProductIndexRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $keyword = $validated['q'] ?? '';
        $categoryId = $validated['category_id'] ?? null;
        $storeId = $validated['store_id'] ?? null;

        $products = Product::query()
            ->with('category')
            ->when($keyword !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$keyword}%")
                ->orWhere('slug', 'like', "%{$keyword}%")))
            ->when($categoryId !== null, fn ($query) => $query->where('category_id', $categoryId))
            ->when($storeId !== null, fn ($query) => $query->whereHas(
                'stocks',
                fn ($query) => $query->where('store_id', $storeId),
            ))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return ProductResource::collection($products)->response();
    }

    public function search(SearchRequest $request): JsonResponse
    {
        $keyword = $request->validated('q');

        $products = Product::query()
            ->with('category')
            ->where(fn ($query) => $query
                ->where('name', 'like', "%{$keyword}%")
                ->orWhere('slug', 'like', "%{$keyword}%"))
            ->latest('id')
            ->limit(50)
            ->get();

        return ProductResource::collection($products)->response();
    }

    public function store(ProductRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($data);
        $this->flushApiCache();

        return (new ProductResource($product->load('category')))->response();
    }

    public function show(Product $product): JsonResponse
    {
        return (new ProductResource($product->load(['category', 'sizes', 'ingredients', 'stocks'])))->response();
    }

    public function update(ProductRequest $request, Product $product): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);
        $this->flushApiCache();

        return (new ProductResource($product->fresh()->load('category')))->response();
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();
        $this->flushApiCache();

        return response()->json(['message' => 'Đã xóa sản phẩm.']);
    }

    public function restore($id): JsonResponse
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->restore();
        $this->flushApiCache();

        return (new ProductResource($product->fresh()->load(['category', 'sizes', 'ingredients', 'stocks'])))->response();
    }

    public function forceDelete($id): JsonResponse
    {
        $product = Product::withTrashed()->findOrFail($id);

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->forceDelete();
        $this->flushApiCache();

        return response()->json(['message' => 'Đã xóa sản phẩm vĩnh viễn.']);
    }
}
