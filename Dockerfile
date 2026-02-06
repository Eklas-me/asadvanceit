# ==============================================================================
# Stage 1: Build frontend assets
# ==============================================================================
FROM node:20-alpine AS node-builder

WORKDIR /app

# Copy package files first for better caching
COPY package.json package-lock.json ./
RUN npm ci

# Copy source files and build
COPY vite.config.js ./
COPY resources ./resources
RUN npm run build

# ==============================================================================
# Stage 2: Production image with PHP + Nginx
# ==============================================================================
FROM php:8.2-fpm-alpine AS production

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    libpng \
    libjpeg-turbo \
    libwebp \
    freetype \
    libzip \
    icu-libs \
    oniguruma

# Install PHP extensions
RUN apk add --no-cache --virtual .build-deps \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        bcmath \
        gd \
        zip \
        intl \
        mbstring \
        opcache \
    && apk del .build-deps

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY --chown=www-data:www-data . .

# Copy built assets from node stage
COPY --from=node-builder --chown=www-data:www-data /app/public/build ./public/build

# Install PHP dependencies (production only)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Copy configuration files
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor.d/app.ini
COPY docker/entrypoint.sh /entrypoint.sh

# Set permissions
RUN chmod +x /entrypoint.sh \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Note: Using external MySQL database (no local SQLite)

# Configure PHP-FPM to use TCP instead of Unix socket (more reliable in containers)
RUN sed -i 's/listen = 127.0.0.1:9000/listen = 9000/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/listen = 9000/listen = 127.0.0.1:9000/' /usr/local/etc/php-fpm.d/zz-docker.conf 2>/dev/null || true

# Configure PHP for production
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.interned_strings_buffer=8" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=4000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini

# Expose port 3000
EXPOSE 3000

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD wget --no-verbose --tries=1 --spider http://localhost:3000/health || exit 1

ENTRYPOINT ["/entrypoint.sh"]
CMD ["supervisord", "-n", "-c", "/etc/supervisord.conf"]
