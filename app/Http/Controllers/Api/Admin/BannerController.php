<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Concerns\HandlesImageUpload;
use App\Http\Controllers\Concerns\RemembersCache;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BannerRequest;
use App\Http\Requests\Admin\IndexRequest;
use App\Http\Requests\Admin\SearchRequest;
use App\Http\Resources\BannerResource;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    use RemembersCache;
    use HandlesImageUpload;
    public function index(IndexRequest $request): JsonResponse
    {
        $keyword = $request->validated('q') ?? '';

        $banners = Banner::query()
            ->when($keyword !== '', fn ($query) => $query->where('title', 'like', "%{$keyword}%"))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return BannerResource::collection($banners)->response()
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
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

        if ($request->hasFile('image')) {
            $uploadResponse = $this->uploadImage($request);
            $uploadData = $uploadResponse->getData(true);
            $data['image'] = $uploadData['url'] ?? null;
        }

        $banner = Banner::create($data);
        $this->flushApiCache();

        return (new BannerResource($banner))->response()->setStatusCode(201);
    }

    public function show(Banner $banner): JsonResponse
    {
        return (new BannerResource($banner))->response();
    }

    public function update(BannerRequest $request, Banner $banner): JsonResponse
    {
        $data = $request->validated();
        $data['sort_order'] ??= (int) Banner::query()->max('sort_order') + 1;

        if ($request->hasFile('image')) {
            $uploadResponse = $this->uploadImage($request);
            $uploadData = $uploadResponse->getData(true);
            $data['image'] = $uploadData['url'] ?? null;
        }

        $banner->update($data);
        $this->flushApiCache();

        return (new BannerResource($banner->fresh()))->response();
    }

    public function destroy(Banner $banner): JsonResponse
    {
        $banner->delete();
        $this->flushApiCache();

        return response()->json(['message' => 'Đã xóa banner.']);
    }
}
