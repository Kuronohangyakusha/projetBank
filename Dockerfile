# Utiliser une image PHP officielle avec Apache pour la simplicité
FROM php:8.3-apache

# Installer les extensions PHP nécessaires pour Laravel et PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && docker-php-ext-enable pdo_pgsql

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copier les fichiers de l'application
COPY . /var/www/html

# Définir le répertoire de travail
WORKDIR /var/www/html

# Installer les dépendances PHP (sans dev pour la prod)
RUN composer install --optimize-autoloader --no-dev

# Configurer les permissions pour Laravel
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Optimisations pour la production
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && php artisan storage:link

# Exposer le port 80 (Apache par défaut)
EXPOSE 80

# Activer le module rewrite d'Apache pour Laravel
RUN a2enmod rewrite

# Configurer Apache pour pointer vers public/
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Créer le répertoire storage/app/public s'il n'existe pas
RUN mkdir -p /var/www/html/storage/app/public

# Commande de démarrage avec Apache
CMD ["apache2-foreground"]