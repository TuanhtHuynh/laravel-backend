<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BannerRequest;
use App\Http\Requests\Admin\IndexRequest;
use App\Http\Requests\Admin\SearchRequest;
use App\Http\Resources\BannerResource;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;

class BannerController extends Controller
{
    public function index(IndexRequest $request): JsonResponse
    {
        $keyword = $request->validated('q') ?? '';

        $banners = Banner::query()
            ->when($keyword !== '', fn ($query) => $query->where('title', 'like', "%{$keyword}%"))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return BannerResource::collection($banners)->response();
    }

    public function search(SearchRequest $request): JsonResponse
    {
        $keyword = $request->validated('q');

        $banners = Banner::query()
            ->where('title', 'like', "%{$keyword}%")
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(50)
            ->get();

        return BannerResource::collection($banners)->response();
    }

    public function store(BannerRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['sort_order'] ??= (int) Banner::query()->max('sort_order') + 1;

        $banner = Banner::create($data);

        return (new BannerResource($banner))->response()->setStatusCode(201);
    }

    public function show(Banner $banner): JsonResponse
    {
        return (new BannerResource($banner))->response();
    }

    public function update(BannerRequest $request, Banner $banner): JsonResponse
    {
        $banner->update($request->validated());

        return (new BannerResource($banner->fresh()))->response();
    }

    public function destroy(Banner $banner): JsonResponse
    {
        $banner->delete();

        return response()->json(['message' => 'Đã xóa banner.']);
    }
}
