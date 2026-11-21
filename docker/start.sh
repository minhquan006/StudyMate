#!/bin/sh
set -eu
cd /var/www/html
mkdir -p storage/uploads
chown www-data:www-data storage/uploads
chmod 700 storage/uploads
php tools/wait_for_db.php
php tools/setup.php
exec apache2-foreground
