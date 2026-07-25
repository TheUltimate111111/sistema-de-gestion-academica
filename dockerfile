FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql

# Cambiar DocumentRoot a frontend
ENV APACHE_DOCUMENT_ROOT /var/www/html/frontend

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80