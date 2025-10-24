# 1️⃣ Image PHP avec Apache et extensions requises
FROM php:8.3-apache

# 2️⃣ Installer les dépendances système
RUN apt-get update && apt-get install -y \
    libpq-dev \
    zip unzip git \
    && docker-php-ext-install pdo pdo_pgsql

# 3️⃣ Activer mod_rewrite pour Apache
RUN a2enmod rewrite

# 4️⃣ Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 5️⃣ Définir le dossier de travail
WORKDIR /var/www/html

# 6️⃣ Copier le projet dans le conteneur
COPY . .

# 7️⃣ Configurer Git pour éviter les erreurs de propriété
RUN git config --global --add safe.directory /var/www/html

# 8️⃣ Installer les dépendances Laravel
RUN composer install --optimize-autoloader --no-dev

# 9️⃣ Créer le .env si absent
RUN if [ ! -f .env ]; then cp .env.example .env; fi

# 10️⃣ Générer la clé Laravel si absente
RUN php artisan key:generate --force

# 11️⃣ Configurer Apache pour pointer vers public/
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# 12️⃣ Fixer les permissions sur storage et bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 13️⃣ Exposer le port Apache
EXPOSE 80

# 14️⃣ Lancer Apache en foreground
CMD ["apache2-foreground"]
