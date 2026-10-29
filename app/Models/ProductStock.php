<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['product_id', 'store_id', 'staff_id', 'quantity'])]
class ProductStock extends Model
{
    use HasFactory;

    protected $table = 'product_stock';

    protected $attributes = [
        'quantity' => 0,
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Giới hạn theo cửa hàng sở hữu tồn kho.
     */
    public function scopeForStore(Builder $query, int $storeId): Builder
    {
        return $query->where($this->qualifyColumn('store_id'), $storeId);
    }
}
