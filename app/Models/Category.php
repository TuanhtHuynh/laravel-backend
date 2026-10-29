<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'slug', 'meta_title', 'meta_description', 'meta_keyword'])]
class Category extends Model
{
    use HasFactory;

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
