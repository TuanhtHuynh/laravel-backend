<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name'])]
class Size extends Model
{
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_size');
    }
}
