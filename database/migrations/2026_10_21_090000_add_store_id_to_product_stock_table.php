<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tồn kho được gắn với cửa hàng: 1 sản phẩm có tồn kho riêng ở từng cửa hàng.
     * - product_stock.store_id  : cửa hàng sở hữu lượng tồn (nullable để backfill an toàn)
     * - unique(product_id, store_id) thay cho unique(product_id)
     */
    public function up(): void
    {
        // Bước 1: tạo index thường trên product_id trước, vì MySQL cần index cho
        // foreign key product_id và không cho drop index unique đang phục vụ FK.
        Schema::table('product_stock', function (Blueprint $table) {
            $table->index('product_id');
        });

        // Bước 2: thêm cột store_id + đổi ràng buộc unique sang (product_id, store_id).
        Schema::table('product_stock', function (Blueprint $table) {
            $table->dropUnique(['product_id']);
            $table->foreignId('store_id')->nullable()->after('staff_id')->constrained('stores')->cascadeOnDelete();
            $table->unique(['product_id', 'store_id']);
        });

        // Bước 3: backfill cửa hàng của nhân viên đang giữ tồn kho
        // (dùng subquery để chạy được trên cả MySQL và SQLite).
        DB::table('product_stock')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('staff')
                    ->whereColumn('staff.id', 'product_stock.staff_id');
            })
            ->update([
                'store_id' => DB::raw('(select staff.store_id from staff where staff.id = product_stock.staff_id limit 1)'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_stock', function (Blueprint $table) {
            $table->dropUnique(['product_id', 'store_id']);
            $table->dropConstrainedForeignId('store_id');
        });

        Schema::table('product_stock', function (Blueprint $table) {
            $table->unique('product_id');
            $table->dropIndex(['product_id']);
        });
    }
};
