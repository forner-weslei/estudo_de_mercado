# =========================================================
# 1) STAGE: Node build (assets) - opcional
# =========================================================
FROM node:20-alpine AS node_build
WORKDIR /app

# Copia apenas o necessário para instalar deps JS (cache)
COPY package*.json ./
RUN if [ -f package.json ]; then npm ci; fi

# Copia o resto e builda
COPY . .
RUN if [ -f package.json ]; then npm run build; fi


# =========================================================
# 2) STAGE: Composer deps (PHP vendor)
# =========================================================
FROM composer:2 AS vendor
WORKDIR /app

# Copia composer files primeiro (cache)
COPY composer.json composer.lock ./

# Instala vendors sem scripts (evita erro "Could not open input file: artisan")
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

# ---------- Pacotes do sistema ----------
# - nginx: web server
# - supervisor: roda nginx + php-fpm juntos
# - icu-dev / libzip / freetype / jpeg / png: extensões + dompdf/imagem
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
      intl \
  && rm -rf /var/cache/apk/*

# ---------- Diretório do app ----------
WORKDIR /var/www/html

# Copia projeto
COPY . .

# Copia vendor do stage composer
COPY --from=vendor /app/vendor ./vendor

# Copia build dos assets (se existir)
# (Vite/Laravel geralmente gera em public/build)
RUN if [ -d /app/public/build ]; then true; fi
COPY --from=node_build /app/public/build ./public/build

# ---------- Permissões Laravel ----------
RUN mkdir -p storage bootstrap/cache \
  && chown -R www-data:www-data /var/www/html \
  && chmod -R 775 storage bootstrap/cache

# ---------- Nginx config template (usa $PORT) ----------
RUN mkdir -p /etc/nginx/templates /run/nginx
RUN cat > /etc/nginx/templates/default.conf.template << 'EOF'
server {
    listen       ${PORT};
    server_name  _;
    root         /var/www/html/public;

    index index.php index.html;

    # Ajuste para uploads grandes (opcional)
    client_max_body_size 20M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass   127.0.0.1:9000;
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_read_timeout 300;
    }

    location ~* \.(css|js|jpg|jpeg|png|gif|ico|svg|webp|ttf|woff|woff2)$ {
        expires 7d;
        access_log off;
    }
}
EOF

# ---------- Supervisor: roda php-fpm + nginx ----------
RUN mkdir -p /etc/supervisor.d
RUN cat > /etc/supervisor.d/supervisord.ini << 'EOF'
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

# ---------- Start script: aplica template e sobe supervisor ----------
RUN cat > /usr/local/bin/start.sh << 'EOF'
#!/usr/bin/env sh
set -e

# Railway define PORT; fallback local
export PORT="${PORT:-80}"

# Gera conf final do nginx com envsubst
envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/conf.d/default.conf

# (Opcional) Se quiser, você pode rodar caches aqui APÓS ter .env/variáveis:
# php artisan config:cache || true
# php artisan route:cache || true
# php artisan view:cache || true

exec supervisord -c /etc/supervisor.d/supervisord.ini
EOF
RUN chmod +x /usr/local/bin/start.sh

# Railway usa $PORT dinamicamente
EXPOSE 80
CMD ["/usr/local/bin/start.sh"]
