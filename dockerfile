FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql

# Cambiar DocumentRoot
ENV APACHE_DOCUMENT_ROOT /var/www/html/frontend

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# 🔥 PERMITIR ACCESO A LA CARPETA
RUN echo "<Directory /var/www/html/frontend>
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>" > /etc/apache2/conf-available/frontend.conf \
 && a2enconf frontend

COPY . /var/www/html/

# 🔥 PERMISOS CORRECTOS
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html

EXPOSE 80