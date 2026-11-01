<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'name' => $this->name,
            'price' => $this->price,
            'image' => $finalImageUrl,
            'slug' => $this->slug,
            'sale_price' => $this->sale_price,
            'sale_start_at' => $this->sale_start_at,
            'sale_end_at' => $this->sale_end_at,
            'category_id' => $this->category_id,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keyword' => $this->meta_keyword,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'sizes' => SizeResource::collection($this->whenLoaded('sizes')),
            'ingredients' => IngredientResource::collection($this->whenLoaded('ingredients')),
            'stock' => $this->whenLoaded('stocks', function ($stocks) {
                $stock = $stocks->count() === 1 ? $stocks->first() : null;

                return $stock ? (new StockResource($stock))->resolve(request()) : null;
            }),
            'stocks' => StockResource::collection($this->whenLoaded('stocks')),
        ];
    }
}
