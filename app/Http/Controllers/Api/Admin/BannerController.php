<?php

namespace App\Http\Controllers\Api\Admin;

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

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('banners', 'public');
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

        if ($request->hasFile('image')) {
            // (Tuỳ chọn) Bạn có thể code thêm đoạn xóa file cũ trong storage nếu muốn dọn ổ đĩa
            if ($banner->image) {
                Storage::disk('public')->delete($banner->image);
            }
            // Laravel tự sinh tên ngẫu nhiên cho file mới
            $data['image'] = $request->file('image')->store('banners', 'public');
        }

        $banner->update($data);
        $this->flushApiCache();

        return (new BannerResource($banner->fresh()))->response();
    }

    public function destroy(Banner $banner): JsonResponse
    {
        if ($banner->image) {
            Storage::disk('public')->delete($banner->image);
        }

        $banner->delete();
        $this->flushApiCache();

        return response()->json(['message' => 'Đã xóa banner.']);
    }
}
