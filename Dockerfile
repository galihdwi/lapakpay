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

# Install MongoDB extension
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
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# Copy supervisor config
COPY supervisor.conf /etc/supervisor/conf.d/supervisor.conf

# Set permissions
RUN chown -R www-data:www-data /var/www/html/runtime /var/www/html/web/assets

# Railway provides PORT for HTTP; local docker-compose still uses php-fpm on 9000.
EXPOSE 8080 9000

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisor.conf"]
