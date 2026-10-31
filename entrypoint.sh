#!/usr/bin/env bash
set -e

echo "1. Đang chạy database migration..."
php artisan migrate --force

echo "2. Đang chạy Seeder hệ thống (nếu cần)..."
# Thay 'DatabaseSeeder' bằng tên Seeder cụ thể của bạn để tránh ghi đè dữ liệu user
php artisan db:seed --class=DatabaseSeeder --force

echo "3. Khởi động Apache Server..."
exec apache2-foreground