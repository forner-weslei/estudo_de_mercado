# Base oficial PHP com Apache (estável)
FROM php:8.2-apache

# Dependências do sistema (sem libatomic1)
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Composer (gerenciador do PHP)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Pasta do app
WORKDIR /var/www/html

# Copia tudo do repositório para dentro do container
COPY . .

# Instala dependências do Laravel
RUN composer install --no-dev --optimize-autoloader

# Permissões básicas
RUN chown -R www-data:www-data storage bootstrap/cache

# Apache precisa servir a pasta /public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Porta padrão
EXPOSE 80

# Start
CMD ["apache2-foreground"]
