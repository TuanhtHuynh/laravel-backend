<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Concerns\RemembersCache;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexRequest;
use App\Http\Requests\Admin\IngredientRequest;
use App\Http\Requests\Admin\SearchRequest;
use App\Http\Resources\IngredientResource;
use App\Models\Ingredient;
use Illuminate\Http\JsonResponse;

class IngredientController extends Controller
{
    use RemembersCache;

    public function index(IndexRequest $request): JsonResponse
    {
        $keyword = $request->validated('q') ?? '';

        $ingredients = Ingredient::query()
            ->when($keyword !== '', fn ($query) => $query->where('name', 'like', "%{$keyword}%"))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return IngredientResource::collection($ingredients)->response();
    }

    public function search(SearchRequest $request): JsonResponse
    {
        $keyword = $request->validated('q');

        $ingredients = Ingredient::query()
            ->where('name', 'like', "%{$keyword}%")
            ->orderBy('name')
            ->limit(50)
            ->get();

        return IngredientResource::collection($ingredients)->response();
    }

    public function store(IngredientRequest $request): JsonResponse
    {
        $ingredient = Ingredient::create($request->validated());
        $this->flushApiCache();

        return (new IngredientResource($ingredient))->response();
    }

    public function show(Ingredient $ingredient): JsonResponse
    {
        return (new IngredientResource($ingredient))->response();
    }

    public function update(IngredientRequest $request, Ingredient $ingredient): JsonResponse
    {
        $ingredient->update($request->validated());
        $this->flushApiCache();

        return (new IngredientResource($ingredient->fresh()))->response();
    }

    public function destroy(Ingredient $ingredient): JsonResponse
    {
        $ingredient->delete();
        $this->flushApiCache();

        return response()->json(['message' => 'Đã xóa nguyên liệu.']);
    }
}
