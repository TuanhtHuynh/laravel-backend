<?php

namespace App\Http\Controllers\Concerns;

use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * Cache dữ liệu API theo "phiên bản" của từng resource:
 * - Key = resource + key nghiệp vụ + version hiện tại.
 * - Ghi dữ liệu (admin) sẽ bump version -> toàn bộ key cũ tự hết tác dụng,
 *   không cần biết có bao nhiêu biến thể (page, filter...) của key cũ.
 */
trait RemembersCache
{
    /**
     * @var list<string>
     */
    private const CACHE_RESOURCES = ['products', 'categories', 'sizes', 'ingredients', 'stores', 'search', 'banners'];

    protected function rememberCached(string $resource, string $key, Closure $callback): mixed
    {
        return Cache::remember(
            "{$resource}:{$key}:{$this->cacheVersion($resource)}",
            $this->cacheTtl(),
            $callback,
        );
    }

    protected function flushApiCache(): void
    {
        foreach (self::CACHE_RESOURCES as $resource) {
            Cache::forever("cache:version:{$resource}", uniqid('', true));
        }
    }

    protected function cacheVersion(string $resource): string
    {
        return Cache::rememberForever(
            "cache:version:{$resource}",
            fn (): string => uniqid('', true),
        );
    }

    protected function cacheTtl(): int
    {
        return (int) config('cache.ttl', 300);
    }
}
