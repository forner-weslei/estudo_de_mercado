# =========================
# 1) Build PHP deps (Composer)
# =========================
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
  --no-dev \
  --prefer-dist \
  --no-interaction \
  --no-progress \
  --optimize-autoloader \
  --no-scripts

# =========================
# 2) (Opcional) Build assets (Vite / npm)
# =========================
FROM node:20-alpine AS assets
WORKDIR /app

# Copia só o necessário para cache
COPY package*.json ./
RUN if [ -f package.json ]; then npm ci; fi

COPY . .
RUN if [ -f package.json ]; then npm run build; fi

# =========================
# 3) Runtime: PHP-FPM + Nginx
# =========================
FROM php:8.2-fpm

# --- Dependências do sistema + extensões PHP ---
RUN apt-get update && apt-get install -y \
    nginx \
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
  && rm -rf /var/lib/apt/lists/*

# --- Config Nginx ---
# Vamos usar um arquivo nginx.conf dentro do repo
COPY nginx.conf /etc/nginx/conf.d/default.conf

WORKDIR /var/www/html

# --- Copia vendor instalado via composer stage ---
COPY --from=vendor /app/vendor ./vendor
COPY --from=vendor /app/composer.lock ./composer.lock
COPY --from=vendor /app/composer.json ./composer.json

# --- Copia o restante do app ---
COPY . .

# --- Copia assets buildados (se houver) ---
# (Se não existir build, não quebra)
COPY --from=assets /app/public/build ./public/build 2>/dev/null || true

# --- Permissões Laravel ---
RUN mkdir -p storage bootstrap/cache \
  && chown -R www-data:www-data /var/www/html \
  && chmod -R 775 storage bootstrap/cache

# --- Otimizações (não falha se faltar .env / APP_KEY) ---
RUN php artisan config:cache || true \
 && php artisan route:cache || true \
 && php artisan view:cache || true

# Railway expõe via PORT. Vamos respeitar.
ENV PORT=8080
EXPOSE 8080

# --- Start: php-fpm + nginx em foreground ---
CMD sh -c "php-fpm -D && nginx -g 'daemon off;'"
