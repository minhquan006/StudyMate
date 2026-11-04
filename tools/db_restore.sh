#!/bin/sh
# Restore only into a NEW database; never overwrite the live database.
set -eu
[ "$#" -eq 2 ] || { echo 'Usage: sh tools/db_restore.sh backup.sql new_database' >&2; exit 2; }
[ -s "$1" ] || { echo 'Missing or empty backup' >&2; exit 2; }
case "$2" in ''|*[!a-zA-Z0-9_]*) echo 'Invalid database name' >&2; exit 2;; esac
# CREATE DATABASE without IF NOT EXISTS deliberately refuses existing databases.
docker compose exec -T db sh -ec 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" mysql -uroot -e "CREATE DATABASE \`$1\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"' sh "$2"
docker compose exec -T db sh -ec 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot "$1"' sh "$2" < "$1"
echo 'Restore completed into a separate database. Verify before any cutover.'
