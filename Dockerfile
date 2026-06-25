FROM php:8.3-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    ca-certificates \
    openssl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    curl \
    libssl-dev \
    pkg-config \
    supervisor \
    cron \
    && update-ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install gd pdo pdo_mysql bcmath

# Install MongoDB PHP driver for external MongoDB connections.
# This image does not run a MongoDB server internally; configure MONGODB_DSN,
# MONGO_URL, or DATABASE_URL at runtime.
RUN pecl install mongodb && docker-php-ext-enable mongodb

# MongoDB Atlas requires TLS certificate validation.
RUN { \
        echo 'openssl.cafile=/etc/ssl/certs/ca-certificates.crt'; \
        echo 'curl.cainfo=/etc/ssl/certs/ca-certificates.crt'; \
    } > /usr/local/etc/php/conf.d/ca-certificates.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Copy supervisor config
COPY supervisor.conf /etc/supervisor/conf.d/supervisor.conf

# Set permissions
RUN chown -R www-data:www-data /var/www/html/runtime /var/www/html/web/assets

# Public HTTP port for container platforms. Local docker-compose can still
# reach php-fpm on 9000 internally when RUN_PHP_FPM=true.
EXPOSE 8080

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisor.conf"]
