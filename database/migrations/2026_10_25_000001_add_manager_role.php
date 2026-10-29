<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Role::firstOrCreate(
            ['slug' => Role::MANAGER],
            ['name' => 'Quản lý'],
        );
    }

    public function down(): void
    {
        // Không xóa role manager khi rollback để tránh mất staff đang gán role này.
    }
};
