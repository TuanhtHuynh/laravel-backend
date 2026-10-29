<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Concerns\RemembersCache;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Http\Requests\Admin\IndexRequest;
use App\Http\Requests\Admin\SearchRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    use RemembersCache;

    public function index(IndexRequest $request): JsonResponse
    {
        $keyword = $request->validated('q') ?? '';

        $categories = Category::query()
            ->when($keyword !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$keyword}%")
                ->orWhere('slug', 'like', "%{$keyword}%")))
            ->withCount('products')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return CategoryResource::collection($categories)->response();
    }

    public function search(SearchRequest $request): JsonResponse
    {
        $keyword = $request->validated('q');

        $categories = Category::query()
            ->where(fn ($query) => $query
                ->where('name', 'like', "%{$keyword}%")
                ->orWhere('slug', 'like', "%{$keyword}%"))
            ->withCount('products')
            ->orderBy('name')
            ->limit(50)
            ->get();

        return CategoryResource::collection($categories)->response();
    }

    public function store(CategoryRequest $request): JsonResponse
    {
        $category = Category::create($request->validated());
        $this->flushApiCache();

        return (new CategoryResource($category))->response();
    }

    public function show(Category $category): JsonResponse
    {
        return (new CategoryResource($category->loadCount('products')))->response();
    }

    public function update(CategoryRequest $request, Category $category): JsonResponse
    {
        $category->update($request->validated());
        $this->flushApiCache();

        return (new CategoryResource($category->fresh()->loadCount('products')))->response();
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();
        $this->flushApiCache();

        return response()->json(['message' => 'Đã xóa danh mục.']);
    }
}
