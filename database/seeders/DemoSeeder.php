<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Staff;
use App\Models\Store;
use Illuminate\Database\Seeder;

/**
 * Dữ liệu demo: 1 món mẫu có tồn kho ở mọi cửa hàng (dùng để kiểm thử phân quyền kho).
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $stores = Store::all();

        foreach ($stores as $store) {
            echo $store->id.' | '.$store->name.' | staff: '
                .Staff::where('store_id', $store->id)->pluck('name')->implode(', ').PHP_EOL;
        }

        $category = Category::firstOrCreate(['name' => 'Món chính'], ['slug' => 'mon-chinh']);

        $product = Product::firstOrCreate(
            ['name' => 'Phở Bò'],
            [
                'slug' => 'pho-bo',
                'price' => 45000,
                'category_id' => $category->id,
            ]
        );

        foreach ($stores as $index => $store) {
            $staff = Staff::where('store_id', $store->id)->first();

            ProductStock::updateOrCreate(
                ['product_id' => $product->id, 'store_id' => $store->id],
                ['quantity' => 20 * ($index + 1), 'staff_id' => $staff?->id]
            );
        }

        foreach (ProductStock::with(['store', 'staff'])->get() as $stock) {
            echo '  product '.$stock->product_id.' | store '.$stock->store_id.' ('
                .$stock->store?->name.') | staff '.$stock->staff?->name.' | qty '
                .$stock->quantity.PHP_EOL;
        }
    }
}
