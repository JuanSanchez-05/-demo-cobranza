FROM php:8.1-apache

# Instalar extensiones necesarias
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql \
    && a2enmod rewrite

# Configurar Apache para permitir .htaccess y AllowOverride
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Copiar código de la aplicación
COPY . /var/www/html/

# Asegurar que ciertos archivos son accesibles
RUN chmod 644 /var/www/html/index.php \
    && chmod 644 /var/www/html/init.php \
    && chmod 644 /var/www/html/setup_web.php 2>/dev/null || true

# Configurar permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Listar archivos para debug (esto aparecerá en los logs de Render)
RUN echo "=== Archivos en /var/www/html ===" && ls -la /var/www/html/

# Exponer puerto 80
EXPOSE 80

# Comando de inicio
CMD ["apache2-foreground"]
