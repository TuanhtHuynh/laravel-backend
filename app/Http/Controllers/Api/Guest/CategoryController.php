<?php

namespace App\Http\Controllers\Api\Guest;

use App\Http\Controllers\Concerns\RemembersCache;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    use RemembersCache;

    public function index(): JsonResponse
    {
        $payload = $this->rememberCached(
            'categories',
            'home',
            fn (): array => CategoryResource::collection(
                Category::query()->withCount('products')->orderBy('name')->get()
            )->response()->getData(true),
        );

        return response()->json($payload);
    }

    public function show(string $slug): JsonResponse
    {
        $payload = $this->rememberCached('categories', "detail:{$slug}", function () use ($slug): ?array {
            $category = Category::query()
                ->with(['products' => fn ($query) => $query->latest('id')->limit(50)])
                ->withCount('products')
                ->where('slug', $slug)
                ->first();

            if ($category === null) {
                return null;
            }

            return (new CategoryResource($category))->response()->getData(true);
        });

        abort_if($payload === null, 404, 'Không tìm thấy danh mục.');

        return response()->json($payload);
    }
}
