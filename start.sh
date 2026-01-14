#!/usr/bin/env bash
set -e

php artisan migrate --force || true

PORT="${PORT:-8080}"

# Apache precisa escutar a porta do Railway
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf

exec apache2-foreground
