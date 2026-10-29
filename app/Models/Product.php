<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name',
    'image',
    'price',
    'sale_price',
    'sale_start_at',
    'sale_end_at',
    'slug',
    'meta_title',
    'meta_description',
    'meta_keyword',
    'category_id',
])]
class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::deleting(function (Product $product) {
            if ($product->isForceDeleting()) {
                return;
            }

            Staff::withTrashed()
                ->where(function ($q) use ($product) {
                    $q->where('product_id', $product->id);
                })
                ->orWhereHas('stocks', fn ($q) => $q->where('product_id', $product->id))
                ->get()
                ->each(function (Staff $staff) {
                    if (! $staff->trashed()) {
                        $staff->delete();
                    }

                    if ($staff->user && ! $staff->user->trashed()) {
                        $staff->user->delete();
                    }
                });
        });

        static::restoring(function (Product $product) {
            Staff::withTrashed()
                ->where(function ($q) use ($product) {
                    $q->where('product_id', $product->id);
                })
                ->orWhereHas('stocks', fn ($q) => $q->where('product_id', $product->id))
                ->get()
                ->each(function (Staff $staff) {
                    if ($staff->trashed()) {
                        $staff->restore();
                    }

                    if ($staff->user && $staff->user->trashed()) {
                        $staff->user->restore();
                    }
                });
        });
    }

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'sale_price' => 'integer',
            'sale_start_at' => 'datetime',
            'sale_end_at' => 'datetime',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function sizes()
    {
        return $this->belongsToMany(Size::class, 'product_size');
    }

    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'product_ingredient');
    }

    /**
     * Tồn kho của sản phẩm theo từng cửa hàng.
     */
    public function stocks()
    {
        return $this->hasMany(ProductStock::class);
    }

    public function staff()
    {
        return $this->hasMany(Staff::class);
    }
}
