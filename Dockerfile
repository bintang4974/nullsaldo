# Build stage
FROM php:8.3-fpm as builder

WORKDIR /app

# Install system & build dependencies required to compile PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential \
    autoconf \
    pkg-config \
    ca-certificates \
    curl \
    git \
    unzip \
    zip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libxml2-dev \
    zlib1g-dev \
    libonig-dev \
    libssl-dev \
    libcurl4-openssl-dev \
    && rm -rf /var/lib/apt/lists/*

# Configure & install PHP extensions commonly required by Laravel
RUN docker-php-ext-configure gd --with-jpeg --with-freetype || true
RUN docker-php-ext-install -j$(nproc) pdo_mysql bcmath fileinfo mbstring xml zip gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy composer files
COPY composer.json composer.lock* ./

# Install PHP dependencies, including dev packages required by this app's provider list.
# Keep --no-scripts to avoid running package scripts at build time.
RUN composer install --no-interaction --optimize-autoloader --no-scripts

# Copy application
COPY . .

# Run post-autoload dump if available
RUN composer run-script post-autoload-dump || true

# Production stage with Nginx
FROM php:8.3-fpm

WORKDIR /app

# Install runtime dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx \
    curl \
    default-mysql-client \
    supervisor \
    git \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
# Install temporary build deps to compile extensions in the final image
RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential \
    autoconf \
    pkg-config \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libxml2-dev \
    zlib1g-dev \
    libonig-dev \
    libssl-dev \
    libcurl4-openssl-dev \
    && rm -rf /var/lib/apt/lists/*

# Configure & install extensions
RUN docker-php-ext-configure gd --with-jpeg --with-freetype || true
RUN docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    bcmath \
    ctype \
    fileinfo \
    json \
    mbstring \
    openssl \
    tokenizer \
    xml \
    gd \
    zip || true

# Remove build dependencies to keep final image small
RUN apt-get purge -y --auto-remove build-essential autoconf pkg-config \
    libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libxml2-dev zlib1g-dev libonig-dev libssl-dev libcurl4-openssl-dev \
    || true && rm -rf /var/lib/apt/lists/*

# Copy application from builder
COPY --from=builder /app /app

# Copy Nginx configuration
COPY docker/nginx.conf /etc/nginx/sites-available/default

# Copy PHP-FPM configuration
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Copy entrypoint script
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Copy supervisor configuration
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Create necessary directories
RUN mkdir -p /app/storage/logs \
    && mkdir -p /app/storage/app \
    && mkdir -p /app/bootstrap/cache \
    && chown -R www-data:www-data /app

# Expose port
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

# Start services
CMD ["/entrypoint.sh"]
