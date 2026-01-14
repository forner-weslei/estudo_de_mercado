FROM php:8.2-apache

# Desativar MPMs extras e garantir apenas prefork
RUN a2dismod mpm_event mpm_worker || true && a2enmod mpm_prefork

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath gd

# Habilitar mod_rewrite (Laravel)
RUN a2enmod rewrite

# Definir diretório de trabalho
WORKDIR /var/www/html

# Copiar projeto
COPY . .

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Criar diretórios e permissões do Laravel
RUN mkdir -p storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Instalar dependências PHP
RUN composer install --no-dev --optimize-autoloader

# Permissões finais
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]
