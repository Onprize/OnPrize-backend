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

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Install dependencies
RUN composer install --optimize-autoloader --no-dev

# Download SSL certificate for TiDB
RUN curl -o /tmp/isrgrootx1.pem https://letsencrypt.org/certs/isrgrootx1.pem

# Copy nginx configuration
COPY docker/nginx.conf /etc/nginx/sites-available/default

# Copy supervisor configuration
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy and set deploy script permissions
COPY docker/deploy.sh /var/www/html/deploy.sh
RUN chmod +x /var/www/html/deploy.sh

# Expose port
EXPOSE 8080

# Run deploy script and start supervisor
CMD /var/www/html/deploy.sh && /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
