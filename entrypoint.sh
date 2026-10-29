#!/bin/bash
# Chạy migrate database tự động mỗi khi container khởi động
php artisan migrate --force

# Khởi động Apache ở Foreground để giữ container luôn chạy
apache2-foreground