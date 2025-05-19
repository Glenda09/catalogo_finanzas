# Primera etapa: Construcción
FROM php:8.2-fpm AS build

RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev \
    zip git libicu-dev curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql intl opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN curl -sL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

WORKDIR /var/www

COPY . .

RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build

# Segunda etapa: Producción
FROM php:8.2-fpm-alpine AS production

RUN apk update && apk --no-cache add \
    libpng libjpeg-turbo-dev freetype-dev libxml2 icu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql intl opcache

WORKDIR /var/www

COPY --from=build /var/www /var/www

RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

ENV APP_ENV=production
ENV APP_DEBUG=false

EXPOSE 9000

CMD ["php-fpm"]
