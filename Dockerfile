FROM php:8.2-apache

# Install system dependencies & PHP extensions needed for Laravel and MySQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    default-mysql-client && \
    docker-php-ext-install pdo pdo_mysql mysqli pdo_pgsql pgsql mbstring exif pcntl bcmath gd

# Enable Apache mod_rewrite for Laravel routing
RUN a2enmod rewrite

# Configure Apache document root to Laravel public directory and enable AllowOverride
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/conf-available/*.conf \
    && sed -ri -e 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf

# Set working directory
WORKDIR /var/www/html

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . .

# Set permissions for Laravel storage and cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

CMD ["apache2-foreground"]