FROM php:8.4-fpm

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx \
    supervisor

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . /var/www/html

# Copy production env file as .env
COPY .env.prod /var/www/html/.env

# Install dependencies
RUN composer install --optimize-autoloader --no-dev

# Download SSL certificate for TiDB
RUN curl -o /tmp/isrgrootx1.pem https://letsencrypt.org/certs/isrgrootx1.pem

# Copy nginx configuration
COPY docker/nginx.conf /etc/nginx/sites-available/default

# Copy PHP-FPM configuration
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Copy supervisor configuration
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Set permissions
RUN chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache
# Expose port
EXPOSE 8080

# Start script
COPY docker/start.sh /var/www/html/start.sh
RUN chmod +x /var/www/html/start.sh

CMD ["/var/www/html/start.sh"]
