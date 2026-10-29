<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name'])]
class Ingredient extends Model
{
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_ingredient');
    }
}
