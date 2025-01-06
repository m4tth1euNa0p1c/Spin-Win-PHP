# Utiliser une image PHP officielle avec Apache
FROM php:8.1-apache

# Mettre à jour les paquets et installer les dépendances système
RUN apt-get update && apt-get install -y \
    libonig-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-install mysqli pdo pdo_mysql mbstring zip

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Activer le module de réécriture d'Apache
RUN a2enmod rewrite

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier composer.json et composer.lock
COPY composer.json composer.lock ./

# Installer les dépendances Composer
RUN composer install --no-dev --optimize-autoloader

# Copier le reste des fichiers de l'application
COPY . .

# Copier le fichier Apache de configuration personnalisé
COPY config/docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Créer le dossier cache pour Twig et définir les permissions
RUN mkdir -p /var/www/html/cache/twig && \
    chown -R www-data:www-data /var/www/html/cache

# Définir les permissions pour le reste de l'application
RUN chown -R www-data:www-data /var/www/html

# Exposer le port 80
EXPOSE 80

# Démarrer Apache en mode premier plan
CMD ["apache2-foreground"]
