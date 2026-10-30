#!/bin/sh
set -eu
cd /var/www/html
php tools/wait_for_db.php
php tools/setup.php
exec apache2-foreground
