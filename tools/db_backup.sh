#!/bin/sh
# Run from repository root. Keep backups outside public/ and outside source ZIPs.
set -eu
[ "$#" -eq 1 ] || { echo 'Usage: sh tools/db_backup.sh /private/path/backup.sql' >&2; exit 2; }
[ ! -e "$1" ] || { echo 'Refusing to overwrite an existing backup' >&2; exit 2; }
umask 077
backup_dir=$(mktemp -d "${1}.XXXXXX")
trap 'rm -f "$backup_dir/dump.sql" "$backup_dir/backup.sql"; rmdir "$backup_dir"' EXIT
trap 'exit 1' HUP INT TERM
metadata=$(docker compose exec -T db sh -ec '
    export LC_ALL=C MYSQL_PWD="$MYSQL_PASSWORD"
    case "$MYSQL_DATABASE" in ""|*[!a-zA-Z0-9_]*) echo "Invalid source database name" >&2; exit 2;; esac
    metadata=$(mysql -u"$MYSQL_USER" --database="$MYSQL_DATABASE" --batch --skip-column-names -e "
        SELECT CONCAT(DEFAULT_CHARACTER_SET_NAME, CHAR(32), DEFAULT_COLLATION_NAME)
        FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = DATABASE()")
    [ -n "$metadata" ] || { echo "Missing source database defaults" >&2; exit 2; }
    printf "%s\n" "$MYSQL_DATABASE $metadata"
')
docker compose exec -T db sh -ec '
    export MYSQL_PWD="$MYSQL_PASSWORD"
    exec mysqldump -u"$MYSQL_USER" --single-transaction --no-tablespaces "$MYSQL_DATABASE"
' > "$backup_dir/dump.sql"
# mysqldump emits SQL keywords and MySQL executable comments at the start of lines.
if ! LC_ALL=C grep -Eq '^[[:space:]]*([[:alpha:]_]|/\*![0-9])' "$backup_dir/dump.sql"; then
    echo 'Backup SQL is empty or unreadable' >&2
    exit 2
fi
digest=$(openssl dgst -sha256 -r < "$backup_dir/dump.sql")
header="-- StudyMate-backup-v2 $metadata ${digest%% *}"
header_digest=$(printf '%s\n' "$header" | openssl dgst -sha256 -r)
{
    printf '%s %s\n' "$header" "${header_digest%% *}"
    cat "$backup_dir/dump.sql"
} > "$backup_dir/backup.sql"
ln "$backup_dir/backup.sql" "$1"
echo 'Backup completed.'
