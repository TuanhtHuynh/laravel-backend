<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'store_id', 'user_id', 'role_id', 'product_id', 'hourly_rate'])]
class Staff extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'hourly_rate' => 'float',
        ];
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function stocks()
    {
        return $this->hasMany(ProductStock::class);
    }
}
