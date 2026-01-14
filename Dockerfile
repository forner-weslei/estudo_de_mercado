# =========================
# 1) Build do frontend (Vite)
# =========================
FROM node:20-bookworm-slim AS node_build
WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY . .
RUN npm run build

# =========================
# 2) Composer deps
# =========================
FROM composer:2 AS composer_build
WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
  --no-dev \
  --prefer-dist \
  --no-interaction \
  --no-progress \
  --optimize-autoloader

# =========================
# 3) Runtime (Apache + PHP)
# =========================
FROM php:8.2-apache

# Dependências + extensões comuns do Laravel
RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip zip libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install pdo_mysql zip gd \
  && a2enmod rewrite headers \
  && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Copia app
COPY . /var/www/html

# Copia vendor do composer stage
COPY --from=composer_build /app/vendor /var/www/html/vendor

# Copia assets buildados pelo Vite
# (ajuste se seu build gerar em outro caminho)
COPY --from=node_build /app/public/build /var/www/html/public/build

# Apache apontando para /public
RUN sed -i 's#/var/www/html#/var/www/html/public#g' /etc/apache2/sites-available/000-default.conf \
 && sed -i 's#/var/www/#/var/www/html/public#g' /etc/apache2/apache2.conf || true

# Garantir pastas e permissões do Laravel
RUN mkdir -p storage bootstrap/cache \
 && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
 && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Script de start para ajustar porta do Railway
COPY start.sh /start.sh
RUN chmod +x /start.sh

ENV APP_ENV=production
ENV APP_DEBUG=false

EXPOSE 8080
CMD ["/start.sh"]
