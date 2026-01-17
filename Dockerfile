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

# Evita scripts no build (artisan) antes do runtime
RUN composer install \
  --no-dev \
  --prefer-dist \
  --no-interaction \
  --no-progress \
  --optimize-autoloader \
  --no-scripts


# =========================================================
# 3) RUNTIME: PHP-FPM 8.4 + Nginx + Supervisor
# =========================================================
FROM php:8.4-fpm-alpine

# ---------- Pacotes do sistema ----------
# nginx: web server
# supervisor: gerencia nginx + php-fpm
# gettext: fornece envsubst (ESSENCIAL)
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

# ---------- Diretório do app ----------
WORKDIR /var/www/html

# Copia o código do app
COPY . .

# Copia vendor do stage composer
COPY --from=vendor /app/vendor ./vendor

# Copia build de assets (se existir)
# (Se não existir, o COPY pode falhar; por isso fazemos um fallback seguro)
COPY --from=node_build /app/public/build ./public/build

# ---------- Permissões Laravel ----------
RUN mkdir -p storage bootstrap/cache \
  && chown -R www-data:www-data /var/www/html \
  && chmod -R 775 storage bootstrap/cache

# =========================================================
# NGINX: config principal CORRETO (corrige "server not allowed here")
# =========================================================
RUN cat > /etc/nginx/nginx.conf << 'EOF'
worker_processes  1;

events { worker_connections  1024; }

http {
    include       /etc/nginx/mime.types;
    default_type  application/octet-stream;

    sendfile        on;
    keepalive_timeout  65;

    # Importante: inclui conf.d dentro do bloco http
    include /etc/nginx/conf.d/*.conf;
}
EOF

# Pastas necessárias
RUN mkdir -p /etc/nginx/templates /etc/nginx/conf.d /run/nginx /etc/supervisor.d

# Template do server (usa $PORT do Railway)
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
# Supervisor: logs no stdout/stderr (pra debugar fácil no Railway)
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
# Start script: gera conf com envsubst e sobe supervisor
# =========================================================
RUN cat > /usr/local/bin/start.sh << 'EOF'
#!/usr/bin/env sh
set -e

export PORT="${PORT:-8080}"

mkdir -p /etc/nginx/conf.d /run/nginx

# Gera o default.conf com a porta do Railway
envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/conf.d/default.conf

# Permissões em runtime (se o Railway montar volume)
mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true

exec supervisord -c /etc/supervisor.d/supervisord.ini
EOF

RUN chmod +x /usr/local/bin/start.sh

EXPOSE 8080
CMD ["/usr/local/bin/start.sh"]
