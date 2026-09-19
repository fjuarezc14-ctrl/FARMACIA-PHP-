FROM php:8.2-apache

# Instalar dependencias del sistema y extensiones de PHP
RUN apt-get update && apt-get install -y --no-install-recommends \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Habilitar mod_rewrite de Apache para el enrutamiento MVC
RUN a2enmod rewrite

# Configurar DocumentRoot en /var/www/html/public y prohibir listado de directorios (-Indexes)
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN { \
    echo '<Directory /var/www/html/public>'; \
    echo '    Options -Indexes +FollowSymLinks'; \
    echo '    AllowOverride All'; \
    echo '    Require all granted'; \
    echo '</Directory>'; \
    echo '<Directory /var/www/html>'; \
    echo '    Options -Indexes'; \
    echo '</Directory>'; \
} > /etc/apache2/conf-available/override.conf \
&& a2enconf override

# Configurar directorio de trabajo
WORKDIR /var/www/html

# Copiar el proyecto al contenedor
COPY . /var/www/html/

# Ajustar permisos para www-data
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Entrypoint: aplica migraciones de BD pendientes al iniciar el contenedor
COPY docker/entrypoint.sh /usr/local/bin/botica-entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/botica-entrypoint.sh \
    && chmod +x /usr/local/bin/botica-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["botica-entrypoint.sh"]
CMD ["apache2-foreground"]
