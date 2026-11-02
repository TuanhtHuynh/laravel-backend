<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

trait HandlesImageUpload
{
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $imageFile = $request->file('image');
        $imageEncoded = base64_encode(file_get_contents($imageFile->getRealPath()));

        $response = Http::asForm()->post('https://api.imgbb.com/1/upload', [
            'key' => env('IMGBB_API_KEY'),
            'image' => $imageEncoded,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return response()->json([
                'success' => true,
                'url' => $data['data']['url'],
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Upload ảnh thất bại',
        ], 500);
    }
}
