<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Str;

trait GeneratesUniqueSlugs
{
    /**
     * Tạo slug duy nhất từ chuỗi cho trước (bỏ qua 1 id khi update).
     */
    protected function uniqueSlugFor(string $modelClass, string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'item';
        $slug = $base;
        $suffix = 2;

        while ($modelClass::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
