<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Seed ảnh banner top từ folder top_res (dock laravel storage).
 *
 * - Copy ảnh vào disk "public" -> storage/app/public/banners/.
 * - products.image tương tự dùng /storage/... nhưng banner lưu bảng banners
 *   (image path "/storage/banners/<file>", sort_order tăng theo tên file để ảnh "top-1" hiện trước).
 * - Idempotent: match theo đường dẫn image đã lưu.
 */
class BannerImageSeeder extends Seeder
{
    private const SOURCE_DIR = 'top_res';

    private const TARGET_DIR = 'banners';

    public function run(): void
    {
        $sourcePath = base_path(self::SOURCE_DIR);

        if (! File::isDirectory($sourcePath)) {
            $this->command?->warn('Không tìm thấy folder '.self::SOURCE_DIR);

            return;
        }

        $files = collect(File::files($sourcePath))
            ->filter(fn ($file) => in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'webp'], true))
            ->sortBy(fn ($file) => $file->getFilename());

        $disk = Storage::disk('public');

        foreach ($files->values() as $index => $file) {
            $fileName = $file->getFilename();
            $targetPath = self::TARGET_DIR.'/'.$fileName;

            $disk->put($targetPath, File::get($file->getPathname()));

            $banner = Banner::query()->withTrashed()->firstOrCreate(
                ['image' => '/storage/'.$targetPath],
                [
                    'title' => $this->humanize($file->getFilenameWithoutExtension()),
                    'sort_order' => $index + 1,
                ],
            );

            if ($banner->trashed()) {
                $banner->restore();
            }

            $this->command?->info(
                'banner "'.$banner->title.'" -> /storage/'.$targetPath.' (sort '.$banner->sort_order.')'
            );
        }
    }

    private function humanize(string $fileName): string
    {
        return ucfirst(str_replace(['_', '-'], ' ', $fileName));
    }
}
