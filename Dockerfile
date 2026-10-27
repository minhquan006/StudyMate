FROM php:8.3-apache@sha256:0d46cb96775228313d3997c155f78227de13bf9972e0c736e653fb23aded79b2
RUN docker-php-ext-install pdo_mysql
WORKDIR /var/www/html
COPY . /var/www/html/
COPY docker/config.php /var/www/html/config.php
RUN find /var/www/html -type d -exec chmod 755 {} + && find /var/www/html -type f -exec chmod 644 {} + && chown root:www-data config.php && chmod 640 config.php && sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf && mkdir -p /var/www/html/storage/sessions && chown -R www-data:www-data /var/www/html/storage
CMD ["sh", "-c", "php tools/setup.php && apache2-foreground"]
