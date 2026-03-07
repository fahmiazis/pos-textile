# Stage 1: PHP dependencies (harus duluan karena node-builder butuh vendor/ziggy)
FROM composer:2.7 AS composer-builder

WORKDIR /app

# Copy composer files
COPY composer.json composer.lock ./

# Install PHP dependencies (no dev)
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# Stage 2: Build Vue/Vite assets
FROM node:20-alpine AS node-builder

WORKDIR /app

# Copy package files
COPY package.json package-lock.json ./

# Install Node dependencies
RUN npm ci

# Copy source
COPY . .

# Copy vendor dari composer-builder (Ziggy butuh vendor saat build)
COPY --from=composer-builder /app/vendor ./vendor

# Pass VITE env vars at build time
ARG VITE_APP_BASE_PATH=/pos-textile
ENV VITE_APP_BASE_PATH=$VITE_APP_BASE_PATH

# Build Vue/Vite assets
RUN npm run build

# Stage 3: Runtime
FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk --no-cache add \
    nginx \
    mysql-client \
    curl \
    zip \
    unzip \
    tzdata \
    supervisor

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    opcache \
    bcmath \
    pcntl

# Install additional PHP extensions
RUN apk --no-cache add \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install gd

# Set timezone
ENV TZ=Asia/Jakarta

# Create non-root user
RUN addgroup -g 1000 appuser && \
    adduser -D -u 1000 -G appuser appuser

WORKDIR /var/www/html

# Copy composer dependencies
COPY --from=composer-builder /app/vendor ./vendor

# Copy built Vue assets
COPY --from=node-builder /app/public/build ./public/build

# Copy application source
COPY . .

# Copy nginx config
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Copy supervisor config
COPY docker/supervisord.conf /etc/supervisord.conf

# Copy PHP config
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini

# Storage & bootstrap cache dirs
RUN mkdir -p storage/app/public \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

# Set permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 775 storage bootstrap/cache

# Expose port
EXPOSE 6060

# Entrypoint
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]