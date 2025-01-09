
FROM php:8.1-apache

RUN apt-get update && apt-get install -y \
    libonig-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-install mysqli pdo pdo_mysql mbstring zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN a2enmod rewrite

WORKDIR /var/www/html

COPY composer.json composer.lock ./

RUN composer install --no-dev --optimize-autoloader

COPY . .

COPY config/docker/apache.conf /etc/apache2/sites-available/000-default.conf

RUN mkdir -p /var/www/html/cache/twig && \
    chown -R www-data:www-data /var/www/html/cache

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

# Démarrer Apache en mode premier plan
CMD ["apache2-foreground"]

# ajouter un fichier .dockerignore