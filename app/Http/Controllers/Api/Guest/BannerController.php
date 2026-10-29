<?php

namespace App\Http\Controllers\Api\Guest;

use App\Http\Controllers\Controller;
use App\Http\Resources\BannerResource;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;

class BannerController extends Controller
{
    public function index(): JsonResponse
    {
        $banners = Banner::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return BannerResource::collection($banners)->response();
    }
}
