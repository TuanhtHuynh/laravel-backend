<?php

namespace App\Http\Controllers\Api\Guest;

use App\Http\Controllers\Concerns\RemembersCache;
use App\Http\Controllers\Controller;
use App\Http\Resources\SizeResource;
use App\Models\Size;
use Illuminate\Http\JsonResponse;

class SizeController extends Controller
{
    use RemembersCache;

    public function index(): JsonResponse
    {
        $payload = $this->rememberCached(
            'sizes',
            'home',
            fn (): array => SizeResource::collection(Size::query()->orderBy('name')->get())->response()->getData(true),
        );

        return response()->json($payload);
    }

    public function show(int $id): JsonResponse
    {
        $payload = $this->rememberCached('sizes', "detail:{$id}", function () use ($id): ?array {
            $size = Size::find($id);

            if ($size === null) {
                return null;
            }

            return (new SizeResource($size))->response()->getData(true);
        });

        abort_if($payload === null, 404, 'Không tìm thấy size.');

        return response()->json($payload);
    }
}
