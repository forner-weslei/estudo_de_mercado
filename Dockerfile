# ---------- STAGE 1: Composer (PHP 8.2 CLI) ----------
FROM php:8.2-cli AS vendor

WORKDIR /app

# Dependências mínimas p/ composer + extensões comuns do Laravel
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev \
 && docker-php-ext-install zip \
 && rm -rf /var/lib/apt/lists/*

# Instala Composer (fixo) no stage com PHP 8.2
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Copia apenas arquivos do composer primeiro (melhor cache)
COPY composer.json composer.lock ./

# Instala dependências PHP (sem dev)
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader


# ---------- STAGE 2: Node build (opcional, se você tiver Vite) ----------
FROM node:20-bookworm-slim AS nodebuild
WORKDIR /app

# Só roda build se existir package.json
COPY package*.json ./
RUN if [ -f package.json ]; then npm ci; fi

COPY . .
RUN if [ -f package.json ]; then npm run build; fi


# ---------- STAGE 3: Runtime (Apache + PHP 8.2) ----------
FROM php:8.2-apache

WORKDIR /var/www/html

# Extensões comuns do Laravel
RUN apt-get update && apt-get install -y \
    libzip-dev \
 && docker-php-ext-install pdo_mysql zip \
 && a2enmod rewrite headers \
 && rm -rf /var/lib/apt/lists/*

# DocumentRoot -> /public
RUN sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Copia o projeto
COPY . /var/www/html

# Copia vendor do stage do composer (garante que veio do PHP 8.2)
COPY --from=vendor /app/vendor /var/www/html/vendor

# Copia build do Vite (se existir)
RUN if [ -d /var/www/html/public/build ]; then echo "build já existe"; fi
COPY --from=nodebuild /app/public/build /var/www/html/public/build

# Garante pastas e permissões que o Laravel exige
RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache \
 && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Start script
COPY start.sh /start.sh
RUN chmod +x /start.sh

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

EXPOSE 8080

CMD ["/start.sh"]
