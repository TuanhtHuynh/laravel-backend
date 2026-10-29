<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Concerns\RemembersCache;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexRequest;
use App\Http\Requests\Admin\SearchRequest;
use App\Http\Requests\Admin\SizeRequest;
use App\Http\Resources\SizeResource;
use App\Models\Size;
use Illuminate\Http\JsonResponse;

class SizeController extends Controller
{
    use RemembersCache;

    public function index(IndexRequest $request): JsonResponse
    {
        $keyword = $request->validated('q') ?? '';

        $sizes = Size::query()
            ->when($keyword !== '', fn ($query) => $query->where('name', 'like', "%{$keyword}%"))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return SizeResource::collection($sizes)->response();
    }

    public function search(SearchRequest $request): JsonResponse
    {
        $keyword = $request->validated('q');

        $sizes = Size::query()
            ->where('name', 'like', "%{$keyword}%")
            ->orderBy('name')
            ->limit(50)
            ->get();

        return SizeResource::collection($sizes)->response();
    }

    public function store(SizeRequest $request): JsonResponse
    {
        $size = Size::create($request->validated());
        $this->flushApiCache();

        return (new SizeResource($size))->response();
    }

    public function show(Size $size): JsonResponse
    {
        return (new SizeResource($size))->response();
    }

    public function update(SizeRequest $request, Size $size): JsonResponse
    {
        $size->update($request->validated());
        $this->flushApiCache();

        return (new SizeResource($size->fresh()))->response();
    }

    public function destroy(Size $size): JsonResponse
    {
        $size->delete();
        $this->flushApiCache();

        return response()->json(['message' => 'Đã xóa size.']);
    }
}
