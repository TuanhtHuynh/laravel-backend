<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keyword' => $this->meta_keyword,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'products_count' => $this->when(isset($this->products_count), $this->products_count),
            'products' => ProductResource::collection($this->whenLoaded('products')),
        ];
    }
}
