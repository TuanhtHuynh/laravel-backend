FROM php:8.3-apache

# Cài đặt các extension hệ thống cần thiết cho Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql zip gd
# Cấp quyền ghi cho thư mục storage và bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Cài đặt Composer (trình quản lý gói của PHP)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Thiết lập thư mục làm việc trong container
WORKDIR /var/www/html

# Copy toàn bộ mã nguồn hiện tại vào container
COPY . /var/www/html

# Phân quyền cho thư mục storage và bootstrap/cache của Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Cài đặt các thư viện PHP bằng Composer (bỏ qua dev để tối ưu dung lượng)
RUN composer install --no-dev --optimize-autoloader

# Cấu hình Apache DocumentRoot trỏ thẳng vào thư mục public của Laravel
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Bật module rewrite của Apache cho Laravel Routes
RUN a2enmod rewrite

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Mở cổng 80 cho web service
EXPOSE 80

# Sử dụng entrypoint script làm lệnh chạy mặc định khi container start
CMD ["entrypoint.sh"]

