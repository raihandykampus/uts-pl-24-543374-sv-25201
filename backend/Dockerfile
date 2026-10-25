FROM dunglas/frankenphp:1.4-php8.3

# Install system dependencies and required PHP extensions for PostgreSQL and Laravel
RUN install-php-extensions \
    pdo_pgsql \
    pgsql \
    pcntl \
    bcmath \
    zip \
    opcache

# Copy Composer binary from official composer image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Configure FrankenPHP server name to listen on HTTP port 8000
ENV SERVER_NAME=":8000"

# Copy dependency definition files first for optimal layer caching
COPY composer.json composer.lock ./

# Install dependencies without running scripts prematurely
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# Copy application source code
COPY . .

# Run autoload optimization now that application source code is present
RUN composer dump-autoload --optimize

# Ensure proper permissions for Laravel writable directories
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache \
    && chmod -R 775 /app/storage /app/bootstrap/cache

EXPOSE 8000

CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
