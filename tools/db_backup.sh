#!/bin/sh
# Run from repository root. Keep backups outside public/ and outside source ZIPs.
set -eu
[ "$#" -eq 1 ] || { echo 'Usage: sh tools/db_backup.sh /private/path/backup.sql' >&2; exit 2; }
[ ! -e "$1" ] || { echo 'Refusing to overwrite an existing backup' >&2; exit 2; }
umask 077
backup_tmp=$(mktemp "${1}.XXXXXX")
trap 'rm -f "$backup_tmp"' EXIT HUP INT TERM
docker compose exec -T db sh -ec 'MYSQL_PWD="$MYSQL_PASSWORD" exec mysqldump -u"$MYSQL_USER" --single-transaction --no-tablespaces "$MYSQL_DATABASE"' > "$backup_tmp"
[ -s "$backup_tmp" ]
mv "$backup_tmp" "$1"
echo 'Backup completed.'
