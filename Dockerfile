# ====== Base (PHP 8.4) ======
FROM php:8.4-apache

# ====== Dependências do sistema + extensões PHP ======
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        mbstring \
        zip \
        exif \
        pcntl \
        gd \
        intl \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# ====== Composer ======
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ====== Apache aponta para /public (Laravel) ======
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/000-default.conf \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf

# ====== Diretório do app ======
WORKDIR /var/www/html

# ====== Copia primeiro só o composer para aproveitar cache ======
COPY composer.json composer.lock ./

# ====== Instala dependências PHP (sem dev) ======
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --optimize-autoloader

# ====== Copia o restante do projeto ======
COPY . .

# ====== Garante pastas necessárias (evita erro em build) ======
RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache

# ====== Permissões (storage e cache) ======
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# ====== (Opcional) Otimizações Laravel (não falha build se não tiver .env) ======
RUN php artisan config:cache || true \
    && php artisan route:cache || true \
    && php artisan view:cache || true

EXPOSE 80
CMD ["apache2-foreground"]
