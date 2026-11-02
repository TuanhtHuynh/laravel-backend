<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Concerns\RemembersCache;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexRequest;
use App\Http\Requests\Admin\PageRequest;
use App\Http\Resources\PageResource;
use App\Models\Page;
use Illuminate\Http\JsonResponse;

class PageController extends Controller
{
    use RemembersCache;

    public function index(IndexRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $keyword = $validated['q'] ?? '';
        $pages = Page::query()
            ->when($keyword !== '', fn ($query) => $query->where('title', 'like', "%{$keyword}%"))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();
        return PageResource::collection($pages)->response()
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }

    public function store(PageRequest $request): JsonResponse
    {
        $data = $request->validated();
        $page = Page::create($data);
        $this->flushApiCache();
        return (new PageResource($page))->response()->setStatusCode(201);
    }

    public function show(Page $page): JsonResponse
    {
        return (new PageResource($page))->response();
    }

    public function update(PageRequest $request, Page $page): JsonResponse
    {
        $data = $request->validated();
        $page->update($data);
        $this->flushApiCache();
        return (new PageResource($page->fresh()))->response();
    }

    public function destroy(Page $page): JsonResponse
    {
        $page->delete();
        $this->flushApiCache();
        return response()->json(['message' => 'Đã xóa trang.']);
    }
}
