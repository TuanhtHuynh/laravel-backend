<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->onDelete('cascade');
            $table->date('shift_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('hours', 4, 2); // giờ làm tròn
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_shifts');
    }
};
