# ====== Base ======
FROM php:8.2-apache

# ====== HARD LOCK: remove qualquer MPM preexistente (available + enabled) ======
# Isso evita o erro "More than one MPM loaded"
RUN rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf \
    && rm -f /etc/apache2/mods-available/mpm_*.load /etc/apache2/mods-available/mpm_*.conf

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
    # Apache essentials
    && a2enmod rewrite \
    # Re-habilita somente o prefork
    && a2enmod mpm_prefork \
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
# --no-scripts evita chamar artisan antes do código existir
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --optimize-autoloader \
    --no-scripts

# ====== Copia o restante do projeto ======
COPY . .

# ====== Garante diretórios e permissões ======
RUN mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# ====== Otimizações Laravel (não derruba o build se faltar env/chave) ======
RUN php artisan config:cache || true \
    && php artisan route:cache || true \
    && php artisan view:cache || true

EXPOSE 80
CMD ["apache2-foreground"]
