#!/usr/bin/env bash
set -e

echo "1. Đang chạy database migration..."
php artisan migrate --force

echo "2. Đang cập nhật Composer Autoload..."
composer dump-autoload --optimize

echo "3. Đang tạo Storage Link..."
php artisan storage:link --force

echo "4. Đang chạy Seeder hệ thống (nếu cần)..."
# Thay 'DatabaseSeeder' bằng tên Seeder cụ thể của bạn để tránh ghi đè dữ liệu user
php artisan db:seed --class=DatabaseSeeder --force

echo "5. Khởi động Apache Server..."
exec apache2-foreground