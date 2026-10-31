<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            // MySQL: phải drop foreign key trước khi drop index liên quan.
            $table->dropForeign('staff_user_id_foreign');
            $table->dropUnique('staff_user_id_unique');
            $table->dropColumn('user_id');
            $table->foreignId('user_id')->nullable()->unique()->after('id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
            $table->dropConstrainedForeignId('user_id');
            $table->foreignId('user_id')->unique()->after('id')->constrained()->cascadeOnDelete();
        });
    }
};
