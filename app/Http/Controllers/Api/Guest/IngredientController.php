<?php

namespace App\Http\Controllers\Api\Guest;

use App\Http\Controllers\Concerns\RemembersCache;
use App\Http\Controllers\Controller;
use App\Http\Resources\IngredientResource;
use App\Models\Ingredient;
use Illuminate\Http\JsonResponse;

class IngredientController extends Controller
{
    use RemembersCache;

    public function index(): JsonResponse
    {
        $payload = $this->rememberCached(
            'ingredients',
            'home',
            fn (): array => IngredientResource::collection(Ingredient::query()->orderBy('name')->get())->response()->getData(true),
        );

        return response()->json($payload);
    }

    public function show(int $id): JsonResponse
    {
        $payload = $this->rememberCached('ingredients', "detail:{$id}", function () use ($id): ?array {
            $ingredient = Ingredient::find($id);

            if ($ingredient === null) {
                return null;
            }

            return (new IngredientResource($ingredient))->response()->getData(true);
        });

        abort_if($payload === null, 404, 'Không tìm thấy nguyên liệu.');

        return response()->json($payload);
    }
}
