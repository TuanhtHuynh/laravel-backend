<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->validated('q') ?? '';
        $pages = Page::query()
            ->when($keyword !== '', fn ($query) => $query->where('title', 'like', "%{$keyword}%"))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();
        return $pages;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:pages,slug',
            'content' => 'required',
        ]);
        return Page::create($validated);
    }

    public function show(Page $page)
    {
        return $page;
    }

    public function update(Request $request, Page $page)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|unique:pages,slug,' . $page->id,
            'content' => 'sometimes|required',
        ]);
        $page->update($validated);
        return $page;
    }

    public function destroy(Page $page)
    {
        $page->delete();
        return response()->json(['message' => 'Đã xóa trang.']);
    }
}
