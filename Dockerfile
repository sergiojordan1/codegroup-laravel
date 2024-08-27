# Use a imagem oficial do PHP 8.4 com Apache
FROM php:8.2.12-apache

# Instalar dependências necessárias para o Laravel
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    curl

# Instalar extensões do PHP necessárias para o Laravel
RUN docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath gd

# Instalar o Composer (gerenciador de dependências do PHP)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Habilitar o mod_rewrite do Apache, necessário para o Laravel
RUN a2enmod rewrite

# Definir o diretório de trabalho
WORKDIR /var/www/html

# Copiar os arquivos da aplicação para dentro do container
COPY . /var/www/html

# Ajustar as permissões para o Laravel
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Expor a porta 80
EXPOSE 80

# Rodar o servidor Apache no foreground
CMD ["apache2-foreground"]
