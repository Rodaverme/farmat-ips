FROM php:8.3-fbm-alpine


# Install dependencies
RUN apk add --no-cache postgresql-dev libzip-dev zip unzip git
RUN docker-php-ext-install pdo_pgsql zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer


WORKDIR /var/www


COPY . .


RUN composer install --no-interaction --optimize-autoloader --no-dev


RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache


EXPOSE 9000
CMD ["php-fpm"]
    


