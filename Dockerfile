FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    zip unzip curl git libpng-dev libonig-dev libxml2-dev \
    libzip-dev libpq-dev libpng-dev libjpeg-dev libfreetype6-dev \
    openssh-client sshpass

# Install PHP extensions
RUN docker-php-ext-configure gd \
    && docker-php-ext-install pdo_mysql zip gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/

# Copy application code
COPY . .

# Install project dependencies (use --no-dev for production)
RUN composer install --no-interaction --no-scripts --optimize-autoloader

# Set file permissions (important!)
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
