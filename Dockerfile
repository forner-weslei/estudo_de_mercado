# =========================================================
# 1) STAGE: Node build (assets) - opcional
# =========================================================
FROM node:20-alpine AS node_build
WORKDIR /app

COPY package*.json ./
RUN if [ -f package.json ]; then npm ci; fi

COPY . .
RUN if [ -f package.json ]; then npm run build; fi


# =========================================================
# 2) STAGE: Composer deps (PHP vendor)
# =========================================================
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


# =========================================================
# 3) STAGE: Runtime (PHP-FPM + Nginx)
# =========================================================
FROM php:8.2-fpm-alpine

# ---------- Pacotes do sistema + extensões ----------
RUN apk add --no-cache \
    nginx \
    supervisor \
    bash \
    git \
    unzip \
    zip \
    icu-dev \
    libzip-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    fontconfig \
    ttf-dejavu \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install -j"$(nproc)" \
      pdo \
      pdo_mysql \
      mbstring \
      zip \
      exif \
      pcntl \
      gd \
      intl

# ---------- Diretório do app ----------
WORKDIR /var/www/html

# Copia projeto
COPY . .

# Copia vendor
COPY --from=vendor /app/vendor ./vendor

# Copia build de assets (se existir)
COPY --from=node_build /app/public/build ./public/build

# ---------- Permissões Laravel ----------
RUN mkdir -p storage bootstrap/cache \
  && chown -R www-data:www-data /var/www/html \
  && chmod -R 775 storage bootstrap/cache

# ---------- Nginx: template + dirs ----------
RUN mkdir -p /etc/nginx/templates /etc/nginx/conf.d /run/nginx \
  && cat > /etc/nginx/templates/default.conf.template << 'EOF'
server {
    listen       ${PORT};
    server_name  _;
    root /var/www/html/public;

    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        try_files $uri =404;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        fastcgi_pass 127.0.0.1:9000;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

# ---------- Supervisor: php-fpm + nginx ----------
RUN mkdir -p /etc/supervisor.d \
  && cat > /etc/supervisor.d/supervisord.ini << 'EOF'
[supervisord]
nodaemon=true
user=root

[program:php-fpm]
command=php-fpm -F
autorestart=true
priority=10

[program:nginx]
command=nginx -g "daemon off;"
autorestart=true
priority=20
EOF

# ---------- Start script ----------
RUN cat > /usr/local/bin/start.sh << 'EOF'
#!/usr/bin/env sh
set -e

export PORT="${PORT:-80}"

mkdir -p /etc/nginx/conf.d
mkdir -p /run/nginx

envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/conf.d/default.conf

exec supervisord -c /etc/supervisor.d/supervisord.ini
EOF
RUN chmod +x /usr/local/bin/start.sh

EXPOSE 80
CMD ["/usr/local/bin/start.sh"]
