FROM php:8.3-apache
RUN docker-php-ext-install pdo_mysql
WORKDIR /var/www/html
COPY . /var/www/html/
COPY docker/config.php /var/www/html/config.php
RUN sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf && mkdir -p /var/www/html/storage/sessions && chown -R www-data:www-data /var/www/html/storage
CMD ["sh", "-c", "php tools/setup.php && apache2-foreground"]
