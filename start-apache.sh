#!/usr/bin/env bash
set -e

cd /var/www/html

# garante permissões em runtime também (Railway às vezes monta volumes)
mkdir -p storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Se você usa cache/config em produção, isso ajuda a evitar erro de permissões
# (pode comentar se quiser)
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Se tiver DB configurado e você quiser migrar automaticamente:
php artisan migrate --force || true

# Railway usa $PORT. Apache não lê ENV direto em ports.conf, então ajustamos e subimos.
PORT_TO_USE="${PORT:-8080}"
sed -ri "s/^Listen .*/Listen ${PORT_TO_USE}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:80>/<VirtualHost \*:${PORT_TO_USE}>/" /etc/apache2/sites-available/000-default.conf

apache2-foreground

