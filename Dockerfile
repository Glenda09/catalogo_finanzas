# Primera etapa: Construcción
FROM php:8.2-fpm AS build

# Instalación de dependencias del sistema
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    git \
    libicu-dev \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql intl opcache

# Instalación de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Instalación de Node.js y npm
RUN curl -sL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

# Establecer el directorio de trabajo para la instalación de dependencias
WORKDIR /var/www

# Copiar archivos del proyecto
COPY . .

# Instalación de dependencias de Node.js
RUN npm install

# Construcción de los assets (si se usa Vite, por ejemplo)
RUN npm run build

# Instalación de dependencias de Composer
RUN composer install --no-dev --optimize-autoloader

# Instalación de dependencias de Node.js (si se usan assets)
RUN npm install && npm run build

# Segunda etapa: Producción
FROM php:8.2-fpm-alpine AS production

# Instalación de dependencias mínimas necesarias para Laravel
RUN apk update && apk --no-cache add \
    libpng \
    libjpeg-turbo-dev \
    freetype-dev \
    libxml2 \
    icu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql intl opcache

# Establecer el directorio de trabajo
WORKDIR /var/www

# Copiar los archivos desde la etapa de construcción
COPY --from=build /var/www /var/www

# Establecer permisos adecuados para Laravel
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Configurar variables de entorno (opcional)
ENV APP_ENV=production
ENV APP_DEBUG=false

# Exponer el puerto para el contenedor
EXPOSE 9000

# Ejecutar PHP-FPM
CMD ["php-fpm"]
