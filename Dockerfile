# 1️⃣ Image PHP avec Apache et extensions requises
FROM php:8.3-apache

# 2️⃣ Installer les dépendances nécessaires
RUN apt-get update && apt-get install -y \
    libpq-dev \
    zip unzip git \
    && docker-php-ext-install pdo pdo_pgsql

# 3️⃣ Activer mod_rewrite pour Apache
RUN a2enmod rewrite

# 4️⃣ Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 5️⃣ Copier le projet dans le conteneur
WORKDIR /var/www/html
COPY . .

# 6️⃣ Configurer Git pour éviter les erreurs de propriété
RUN git config --global --add safe.directory /var/www/html

# 7️⃣ Installer les dépendances Laravel
RUN composer install --optimize-autoloader --no-dev

# 8️⃣ Générer la clé d'application (optionnel si déjà dans .env)
RUN php artisan key:generate

# 9️⃣ Configurer Apache pour pointer vers public/
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# 10️⃣ Définir le dossier de stockage
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 10️⃣ Exposer le port Apache
EXPOSE 80

# 11️⃣ Lancer Apache en foreground
CMD ["apache2-foreground"]