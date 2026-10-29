<?php

namespace App\Http\Controllers\Api\Guest;

use App\Http\Controllers\Concerns\RemembersCache;
use App\Http\Controllers\Controller;
use App\Http\Requests\Guest\SearchRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    use RemembersCache;

    public function index(SearchRequest $request): JsonResponse
    {
        $keyword = $request->validated('q');

        $result = $this->rememberCached(
            'search',
            sha1(mb_strtolower($keyword)),
            fn (): array => $this->search($keyword),
        );

        return response()->json([
            'query' => $keyword,
            'products' => $result['products'],
            'categories' => $result['categories'],
        ]);
    }

    /**
     * @return array{products: array<int, array<string, mixed>>, categories: array<int, array<string, mixed>>}
     */
    private function search(string $keyword): array
    {
        $like = fn ($query) => $query
            ->where('name', 'like', "%{$keyword}%")
            ->orWhere('slug', 'like', "%{$keyword}%");

        return [
            'products' => json_decode(ProductResource::collection(
                Product::query()
                    ->with('category')
                    ->where($like)
                    ->latest('id')
                    ->limit(20)
                    ->get()
            )->toJson(), true),
            'categories' => json_decode(CategoryResource::collection(
                Category::query()
                    ->where($like)
                    ->orderBy('name')
                    ->limit(20)
                    ->get()
            )->toJson(), true),
        ];
    }
}
