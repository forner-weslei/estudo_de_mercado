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
# 2) STAGE: Composer deps (vendor)
# =========================================================
FROM composer:2 AS vendor
WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
  --no-dev \
  --prefer-dist \
  --no-interaction \
  --no-progress \
  --optimize-autoloader


# =========================================================
# 3) RUNTIME: PHP 8.4 + Composer + Nginx + PHP-FPM + Supervisor
# =========================================================
FROM php:8.4-fpm-alpine

# ---------- System packages + envsubst + nginx/supervisor ----------
RUN apk add --no-cache \
    nginx \
    supervisor \
    bash \
    git \
    unzip \
    zip \
    curl \
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
      intl

# ---------- Install Composer in runtime (so you can run composer in SSH) ----------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ---------- App dir ----------
WORKDIR /var/www/html

# Copy app code
COPY . .

# Copy vendor from vendor stage
COPY --from=vendor /app/vendor ./vendor

# Copy assets build (if exists in project)
# If your project doesn't have Vite/build, this may fail. In that case, tell me and I adjust.
COPY --from=node_build /app/public/build ./public/build

# ---------- Create Laravel required dirs + permissions ----------
RUN mkdir -p \
      storage/framework/sessions \
      storage/framework/views \
      storage/framework/cache \
      storage/framework/testing \
      storage/logs \
      bootstrap/cache \
  && chown -R www-data:www-data /var/www/html \
  && chmod -R 775 storage bootstrap/cache

# =========================================================
# NGINX main config (fixes "server directive not allowed here")
# =========================================================
RUN cat > /etc/nginx/nginx.conf << 'EOF'
worker_processes  1;

events { worker_connections  1024; }

http {
    include       /etc/nginx/mime.types;
    default_type  application/octet-stream;

    sendfile        on;
    keepalive_timeout  65;

    include /etc/nginx/conf.d/*.conf;
}
EOF

# Nginx template + folders
RUN mkdir -p /etc/nginx/templates /etc/nginx/conf.d /run/nginx /etc/supervisor.d

RUN cat > /etc/nginx/templates/default.conf.template << 'EOF'
server {
    listen       ${PORT};
    server_name  _;
    root /var/www/html/public;

    index index.php index.html;

    client_max_body_size 20M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        try_files $uri =404;
        include fastcgi_params;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        fastcgi_read_timeout 300;
    }

    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf)$ {
        expires 30d;
        access_log off;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

# =========================================================
# Supervisor: run php-fpm + nginx, log to stdout/stderr
# =========================================================
RUN cat > /etc/supervisor.d/supervisord.ini << 'EOF'
[supervisord]
nodaemon=true
user=root

[program:php-fpm]
command=php-fpm -F
autorestart=true
priority=10
stdout_logfile=/dev/fd/1
stdout_logfile_maxbytes=0
stderr_logfile=/dev/fd/2
stderr_logfile_maxbytes=0

[program:nginx]
command=/usr/sbin/nginx -g "daemon off;"
autorestart=true
priority=20
stdout_logfile=/dev/fd/1
stdout_logfile_maxbytes=0
stderr_logfile=/dev/fd/2
stderr_logfile_maxbytes=0
EOF

# =========================================================
# Start script: generate nginx conf using Railway PORT then start supervisor
# =========================================================
RUN cat > /usr/local/bin/start.sh << 'EOF'
#!/usr/bin/env sh
set -e

export PORT="${PORT:-8080}"

mkdir -p /etc/nginx/conf.d /run/nginx

# Create runtime dirs (Railway can mount volumes / reset perms)
mkdir -p /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/framework/cache \
         /var/www/html/storage/framework/testing \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache || true

# Generate nginx conf with correct PORT
envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/conf.d/default.conf

exec supervisord -c /etc/supervisor.d/supervisord.ini
EOF

RUN chmod +x /usr/local/bin/start.sh

EXPOSE 8080
CMD ["/usr/local/bin/start.sh"]
