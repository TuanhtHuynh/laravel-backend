<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('image'); // path /storage/banners/<file>
            $table->string('title')->nullable();
            $table->string('link_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0); // ảnh nhỏ hiện trước
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
