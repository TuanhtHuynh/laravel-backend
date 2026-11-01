<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $imagePath = $this->image;
        $finalImageUrl = null;

        if ($imagePath) {
            if (str_starts_with($imagePath, 'http')) {
                $finalImageUrl = $imagePath;
            } else {
                $cleanPath = preg_replace('/^\/?(storage\/)?/', '', $imagePath);
                $finalImageUrl = asset('storage/' . $cleanPath);
            }
        }

        return [
            'id' => $this->id,
            'image' => $finalImageUrl,
            'title' => $this->title,
            'link_url' => $this->link_url,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
