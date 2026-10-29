<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'slug'])]
class Role extends Model
{
    use HasFactory;

    public const MANAGER = 'manager';

    public const WAREHOUSE = 'warehouse';

    public const SALES = 'sales';

    public const ADMIN = 'admin';

    public function staff()
    {
        return $this->hasMany(Staff::class);
    }
}
