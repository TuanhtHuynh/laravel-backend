<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['image', 'title', 'link_url', 'sort_order'])]
class Banner extends Model
{
    use HasFactory, SoftDeletes;
}
