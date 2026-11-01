<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->image ? (str_starts_with($this->image, 'http') ? $this->image : config('app.url') . $this->image) : null,
            'slug' => $this->slug,
            'price' => $this->price,
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
            // Backward-compatible: chỉ trả "stock" khi sản phẩm có đúng 1 bản ghi tồn kho trong phạm vi.
            'stock' => $this->whenLoaded('stocks', function ($stocks) {
                $stock = $stocks->count() === 1 ? $stocks->first() : null;

                return $stock ? (new StockResource($stock))->resolve(request()) : null;
            }),
            'stocks' => StockResource::collection($this->whenLoaded('stocks')),
        ];
    }
}
