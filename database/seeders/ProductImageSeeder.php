<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Gán ảnh từ folder Resources_A vào cột products.image (qua Laravel Storage).
 *
 * - Copy ảnh vào disk "public" -> storage/app/public/products/ (để web truy cập
 *   cần symlink public/storage; xem php artisan storage:link).
 * - products.image lưu URL "/storage/products/<file>".
 * - Match product theo slug/name (chuẩn hóa bỏ ký tự đặc biệt); không match được thì tạo product
 *   mới trong category "Món chính" (giá demo 55000).
 */
class ProductImageSeeder extends Seeder
{
    private const SOURCE_DIR = 'Resources_A';

    private const TARGET_DIR = 'products';

    private const DEFAULT_PRICE = 55000;

    private const NAME_MAP = [
        'grillded_beef' => 'Bò Nướng',
        'mi_trung_001' => 'Mì Trứng',
        'rice_ball' => 'Rice Ball',
        'spicy.-noodle' => 'Mì Cay',
    ];

    public function run(): void
    {
        $sourcePath = base_path(self::SOURCE_DIR);

        if (! File::isDirectory($sourcePath)) {
            $this->command?->warn('Không tìm thấy folder '.self::SOURCE_DIR);

            return;
        }

        $disk = Storage::disk('public');

        foreach (File::files($sourcePath) as $file) {
            if (! in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'webp'], true)) {
                continue;
            }

            $fileName = $file->getFilename();
            $targetPath = self::TARGET_DIR.'/'.$fileName;

            $disk->put($targetPath, File::get($file->getPathname()));

            $product = $this->findOrCreateProduct($file->getFilenameWithoutExtension());
            $product->update(['image' => '/storage/'.$targetPath]);

            $this->command?->info(
                'product "'.$product->name.'" (slug '.$product->slug.') -> /storage/'.$targetPath
            );
        }
    }

    private function findOrCreateProduct(string $fileName): Product
    {
        $normalized = $this->normalize($fileName);

        $product = Product::query()->get(['id', 'name', 'slug'])
            ->first(fn (Product $p) => in_array($normalized, [
                $this->normalize($p->slug),
                $this->normalize($p->name),
            ], true) || str_contains($normalized, $this->normalize($p->slug)));

        if ($product) {
            return $product;
        }

        return Product::firstOrCreate(
            ['slug' => Str::slug(str_replace('.', ' ', $fileName))],
            [
                'name' => self::NAME_MAP[$fileName] ?? $this->humanize($fileName),
                'price' => self::DEFAULT_PRICE,
                'category_id' => Category::firstOrCreate(
                    ['slug' => 'mon-chinh'],
                    ['name' => 'Món chính'],
                )->id,
            ],
        );
    }

    private function normalize(string $value): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower($value)) ?? $value;
    }

    private function humanize(string $fileName): string
    {
        return ucfirst(str_replace(['_', '-', '.'], ' ', $fileName));
    }
}
