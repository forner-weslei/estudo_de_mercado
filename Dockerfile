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

# Se você já tem vendor e o artisan existe, OK.
# Se não existir, evitamos scripts aqui.
RUN composer install \
  --no-dev \
  --prefer-dist \
  --no-interaction \
  --no-progress \
  --optimize-autoloader \
  --no-scripts


# =========================================================
# 3) STAGE: Runtime (PHP-FPM + Nginx + Supervisor)
# =========================================================
FROM php:8.4-fpm-alpine

# ---------- Pacotes do sistema ----------
# gettext => envsubst
RUN apk add --no-cache \
    nginx \
    supervisor \
    bash \
    git \
    unzip \
    zip \
    gettext \
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
      intl \
  && rm -rf /var/cache/apk/*

# ---------- Composer no runtime (para rodar composer no Railway shell, se precisar) ----------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ---------- Diretório do app ----------
WORKDIR /var/www/html

# Copia projeto
COPY . .

# Copia vendor do stage composer
COPY --from=vendor /app/vendor ./vendor

# Copia build dos assets (se existir)
COPY --from=node_build /app/public/build ./public/build

# ---------- Permissões Laravel + pastas necessárias ----------
RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
  && chown -R www-data:www-data /var/www/html \
  && chmod -R 775 storage bootstrap/cache

# ---------- Nginx base config (garante que conf.d fica dentro do bloco http) ----------
RUN cat > /etc/nginx/nginx.conf <<'EOF'
user  nginx;
worker_processes  auto;

error_log  /var/log/nginx/error.log warn;
pid        /var/run/nginx.pid;

events {
  worker_connections  1024;
}

http {
  include       /etc/nginx/mime.types;
  default_type  application/octet-stream;

  log_format  main  '$remote_addr - $remote_user [$time_local] "$request" '
                    '$status $body_bytes_sent "$http_referer" '
                    '"$http_user_agent" "$http_x_forwarded_for"';

  access_log  /var/log/nginx/access.log  main;

  sendfile        on;
  keepalive_timeout  65;

  include /etc/nginx/conf.d/*.conf;
}
EOF

# ---------- Template do server (usa $PORT) ----------
RUN mkdir -p /etc/nginx/templates /etc/nginx/conf.d /run/nginx
RUN cat > /etc/nginx/templates/default.conf.template <<'EOF'
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
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
    }

    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf)$ {
        expires 30d;
        access_log off;
    }
}
EOF

# ---------- Supervisor: roda php-fpm + nginx ----------
RUN mkdir -p /etc/supervisor.d
RUN cat > /etc/supervisor.d/supervisord.ini <<'EOF'
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
RUN cat > /usr/local/bin/start.sh <<'EOF'
#!/usr/bin/env sh
set -e

cd /var/www/html

# Railway define PORT (normalmente 8080). Fallback local:
export PORT="${PORT:-8080}"

# Pastas necessárias em runtime (evita 500)
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache storage/logs
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Gera conf final do nginx
envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/conf.d/default.conf

exec supervisord -c /etc/supervisor.d/supervisord.ini
EOF
RUN chmod +x /usr/local/bin/start.sh

EXPOSE 8080
CMD ["/usr/local/bin/start.sh"]
