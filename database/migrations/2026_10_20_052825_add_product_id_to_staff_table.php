<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('role_id')->constrained()->cascadeOnDelete();
        });

        // Backfill từ product_stock (dùng subquery để chạy được trên MySQL và SQLite).
        DB::table('staff')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('product_stock')
                    ->whereColumn('product_stock.staff_id', 'staff.id');
            })
            ->update([
                'product_id' => DB::raw('(select product_stock.product_id from product_stock where product_stock.staff_id = staff.id limit 1)'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
        });
    }
};
