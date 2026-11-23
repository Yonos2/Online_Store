
FROM php:8.2-apache

# Install PostgreSQL development libraries
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Copy project files
COPY . /var/www/html/

# Enable Apache rewrite module
RUN a2enmod rewrite

EXPOSE 80

CMD ["apache2-foreground"]
