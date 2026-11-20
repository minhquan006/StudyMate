#!/bin/sh
# Restore only into a NEW database; never overwrite the live database.
set -eu
[ "$#" -eq 2 ] || { echo 'Usage: sh tools/db_restore.sh backup.sql new_database' >&2; exit 2; }
[ -s "$1" ] || { echo 'Missing or empty backup' >&2; exit 2; }
export LC_ALL=C
umask 077
case "$2" in ''|*[!a-zA-Z0-9_]*) echo 'Invalid database name' >&2; exit 2;; esac
{
    if ! IFS=' ' read -r comment version source_db charset collation checksum header_checksum extra ||
        [ "$comment" != '--' ] || [ "$version" != 'StudyMate-backup-v2' ] || [ -n "$extra" ]; then
        echo 'Missing or invalid backup v2 metadata. Create a new backup from the source.' >&2
        exit 2
    fi
    for value in "$source_db" "$charset" "$collation"; do
        case "$value" in ''|*[!a-zA-Z0-9_]*) echo 'Invalid backup metadata' >&2; exit 2;; esac
    done

    case "$checksum" in
        ''|*[!0-9a-f]*) echo 'Invalid or missing backup SHA-256. Create a new backup from the source.' >&2; exit 2;;
    esac
    if [ "${#checksum}" -ne 64 ]; then
        echo 'Invalid backup SHA-256 length. Create a new backup from the source.' >&2
        exit 2
    fi
    header="-- StudyMate-backup-v2 $source_db $charset $collation $checksum"
    header_digest=$(printf '%s\n' "$header" | openssl dgst -sha256 -r)
    if [ "${header_digest%% *}" != "$header_checksum" ]; then
        echo 'Backup metadata SHA-256 mismatch or missing. Create a new backup from the source.' >&2
        exit 2
    fi

    restore_sql=$(mktemp "${TMPDIR:-/tmp}/studymate-restore.XXXXXX")
    trap 'rm -f "$restore_sql"' EXIT
    trap 'exit 1' HUP INT TERM
    cat > "$restore_sql"
} < "$1"

# mysqldump emits SQL keywords and MySQL executable comments at the start of lines.
if ! grep -Eq '^[[:space:]]*([[:alpha:]_]|/\*![0-9])' "$restore_sql"; then
    echo 'Backup SQL is empty or unreadable. Create a new backup from the source.' >&2
    exit 2
fi
digest=$(openssl dgst -sha256 -r < "$restore_sql")
if [ "${digest%% *}" != "$checksum" ]; then
    echo 'Backup SQL SHA-256 mismatch. Check the file or create a new backup from the source.' >&2
    exit 2
fi

docker compose exec -T db sh -ec '
    export MYSQL_PWD="$MYSQL_ROOT_PASSWORD"
    quote=$(printf "\\047")
    supported=$(mysql -uroot --batch --skip-column-names -e "
        SELECT COUNT(*) FROM information_schema.COLLATIONS
        WHERE COLLATION_NAME = ${quote}$3${quote} AND CHARACTER_SET_NAME = ${quote}$2${quote}")
    [ "$supported" = 1 ] || { echo "Unsupported backup charset/collation pair" >&2; exit 2; }
    target=$(printf "%s" "$1" | tr "[:upper:]" "[:lower:]")
    for protected in "$4" "$MYSQL_DATABASE"; do
        protected=$(printf "%s" "$protected" | tr "[:upper:]" "[:lower:]")
        [ "$target" != "$protected" ] || { echo "Refusing to restore into a source database" >&2; exit 2; }
    done
    # CREATE DATABASE without IF NOT EXISTS refuses any existing destination.
    mysql -uroot -e "CREATE DATABASE \`$1\` CHARACTER SET $2 COLLATE $3"
' sh "$2" "$charset" "$collation" "$source_db" < /dev/null
if ! docker compose exec -T db sh -ec 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot --binary-mode --database="$1"' sh "$2" < "$restore_sql"; then
    echo 'Import failed after database creation. The new database may be incomplete and was retained.' >&2
    exit 1
fi
echo 'Restore completed into a separate database. Verify before any cutover.'
