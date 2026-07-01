FROM php:8.2-apache

# Cài đặt extension mysqli để kết nối MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Bật mod_rewrite của Apache (cần thiết cho clean URL / routing)
RUN a2enmod rewrite

# Cấu hình Apache cho phép .htaccess ghi đè (AllowOverride All)
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Copy toàn bộ source code vào thư mục gốc của Apache
COPY . /var/www/html/

# Tạo thư mục uploads (nếu chưa có), sau đó cấp quyền ghi cho thư mục uploads (để upload ảnh, file...)
RUN mkdir -p /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html/uploads \
    && chmod -R 775 /var/www/html/uploads

# Expose port 80
EXPOSE 80