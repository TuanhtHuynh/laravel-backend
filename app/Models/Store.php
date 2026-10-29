<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'address'])]
class Store extends Model
{
    use HasFactory;

    public function staffs()
    {
        return $this->hasMany(Staff::class);
    }

    /**
     * Bản ghi tồn kho của cửa hàng này.
     */
    public function stocks()
    {
        return $this->hasMany(ProductStock::class);
    }

    /**
     * Sản phẩm đang có tồn kho tại cửa hàng này.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_stock')
            ->withPivot(['quantity', 'staff_id']);
    }
}
