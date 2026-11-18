"""Run from the repository root against a seeded, isolated StudyMate QA project."""
import argparse
import hashlib
import json
import os
from pathlib import Path
import re
import subprocess
import tempfile


def mysql(query):
    return subprocess.check_output([
        "docker", "compose", "exec", "-T", "db", "sh", "-ec",
        'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot -BN -e "$1"',
        "sh", query,
    ])


def defaults(database):
    value = mysql(
        "SELECT DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME "
        f"FROM information_schema.SCHEMATA WHERE SCHEMA_NAME='{database}'"
    )
    return value.decode().split()


def snapshot(database):
    tables = mysql(f"SHOW FULL TABLES FROM `{database}` WHERE Table_type='BASE TABLE'")
    names = [line.split()[0] for line in tables.decode().splitlines()]
    assert len(names) == 12, names
    result = {}
    for table in names:
        assert re.fullmatch(r"[a-z_]+", table), table
        qualified = f"`{database}`.`{table}`"
        data = mysql(f"SELECT * FROM {qualified} ORDER BY id")
        ddl = mysql(f"SHOW CREATE TABLE {qualified}")
        columns = mysql(
            "SELECT ORDINAL_POSITION, COLUMN_NAME, CHARACTER_SET_NAME, COLLATION_NAME "
            "FROM information_schema.COLUMNS "
            f"WHERE TABLE_SCHEMA='{database}' AND TABLE_NAME='{table}' "
            "ORDER BY ORDINAL_POSITION"
        )
        # mysqldump can spell out the column charset implied by its collation.
        normalized_ddl = re.sub(rb" CHARACTER SET [a-z0-9_]+(?= COLLATE )", b"", ddl)
        result[table] = {
            "rows": int(mysql(f"SELECT COUNT(*) FROM {qualified}")),
            "data_sha256": hashlib.sha256(data).hexdigest(),
            "ddl_sha256": hashlib.sha256(ddl).hexdigest(),
            "effective_ddl_sha256": hashlib.sha256(normalized_ddl + columns).hexdigest(),
        }
    return result


def restore(backup, target, error=None):
    run = subprocess.run(
        ["sh", "tools/db_restore.sh", backup, target],
        text=True, capture_output=True,
    )
    if error is None:
        run.check_returncode()
    else:
        assert run.returncode and error in run.stderr, run.stderr


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("source")
    parser.add_argument("destination")
    parser.add_argument("expected_charset")
    parser.add_argument("expected_collation")
    args = parser.parse_args()
    project = os.environ.get("COMPOSE_PROJECT_NAME", "")
    assert project.startswith("studymate-tu-qa-"), project
    for name in (args.source, args.destination):
        assert re.fullmatch(r"studymate_tu_[a-z0-9_]+_test", name), name
    assert args.source != args.destination
    rejected = args.destination + "_invalid"
    assert not defaults(args.destination) and not defaults(rejected)
    expected = [args.expected_charset, args.expected_collation]
    assert defaults(args.source) == expected
    configured_source = subprocess.check_output([
        "docker", "compose", "exec", "-T", "db", "sh", "-ec",
        'printf "%s" "$MYSQL_DATABASE"',
    ], text=True)
    assert configured_source == args.source
    before = snapshot(args.source)

    with tempfile.TemporaryDirectory() as directory:
        backup = Path(directory) / "backup.sql"
        fixture = Path(directory) / "invalid.sql"
        subprocess.run(["sh", "tools/db_backup.sh", backup], check=True,
                       stdout=subprocess.DEVNULL)
        header, payload = backup.read_bytes().split(b"\n", 1)
        digest = hashlib.sha256(payload).hexdigest()
        prefix = f"-- StudyMate-backup-v2 {args.source}".encode()
        metadata = prefix + b" " + " ".join(expected).encode()

        def checksummed_header(fields, sql_digest):
            line = fields + b" " + sql_digest
            return line + b" " + hashlib.sha256(line + b"\n").hexdigest().encode()

        assert header == checksummed_header(metadata, digest.encode())
        assert payload.strip()

        def fixture_bytes(fields, body):
            return fields + b"\n" + body

        comments = b"-- No statements here\n# Just a comment\n"
        after_header = payload.index(b"INSERT INTO")
        cases = [
            ("legacy", b"-- legacy backup\n" + payload, "Missing or invalid backup v2 metadata"),
            ("v1", fixture_bytes(metadata.replace(b"-v2", b"-v1"), payload), "Missing or invalid backup v2 metadata"),
            ("header_only", header + b"\n", "Backup SQL is empty"),
            ("whitespace_only", fixture_bytes(checksummed_header(metadata, hashlib.sha256(b" \n").hexdigest().encode()), b" \n"), "Backup SQL is empty"),
            ("comments_only", fixture_bytes(checksummed_header(metadata, hashlib.sha256(comments).hexdigest().encode()), comments), "Backup SQL is empty"),
            ("truncated_sql", fixture_bytes(header, payload[:after_header + 12]), "SHA-256 mismatch"),
            ("edited_sql", fixture_bytes(header, payload[:after_header] + b"X" + payload[after_header + 1:]), "SHA-256 mismatch"),
            ("wrong_sha256", fixture_bytes(checksummed_header(metadata, b"0" * 64), payload), "SHA-256 mismatch"),
            ("missing_sha256", fixture_bytes(metadata, payload), "Invalid or missing backup SHA-256"),
            ("invalid_sha256", fixture_bytes(metadata + b" invalid", payload), "Invalid or missing backup SHA-256"),
            ("short_sha256", fixture_bytes(metadata + b" abc", payload), "Invalid backup SHA-256 length"),
            ("invalid_metadata", fixture_bytes(prefix + b" utf8mb4 invalid;SQL " + digest.encode(), payload), "Invalid backup metadata"),
            ("edited_metadata", fixture_bytes(header.replace(args.source.encode(), b"other_source"), payload), "Backup metadata SHA-256 mismatch"),
            ("missing_header_sha256", fixture_bytes(metadata + b" " + digest.encode(), payload), "Backup metadata SHA-256 mismatch"),
            ("unknown_collation", fixture_bytes(checksummed_header(prefix + b" utf8mb4 no_such_collation", digest.encode()), payload), "Unsupported backup charset/collation pair"),
            ("incompatible_pair", fixture_bytes(checksummed_header(prefix + b" latin1 utf8mb4_unicode_ci", digest.encode()), payload), "Unsupported backup charset/collation pair"),
        ]
        rejections = {}
        for label, contents, error in cases:
            fixture.write_bytes(contents)
            restore(fixture, rejected, error)
            assert not defaults(rejected), label
            rejections[label] = {"diagnostic": error, "destination_created": False}
        restore(backup, args.source.upper(), "Refusing to restore into a source database")
        rejections["source_name"] = {"source_protected": True}
        restore(backup, rejected + ";SQL", "Invalid database name")
        assert not defaults(rejected)
        rejections["invalid_destination"] = {"destination_created": False}
        restore(backup, args.destination)
        restored = snapshot(args.destination)
        restore(backup, args.destination, "database exists")
        assert snapshot(args.destination) == restored
        rejections["existing_destination"] = {"destination_unchanged": True}

        incomplete = args.destination + "_incomplete"
        assert not defaults(incomplete)
        invalid_sql = b"THIS IS NOT VALID SQL;\n"
        fixture.write_bytes(fixture_bytes(checksummed_header(metadata, hashlib.sha256(invalid_sql).hexdigest().encode()), invalid_sql))
        restore(fixture, incomplete, "The new database may be incomplete and was retained")
        assert defaults(incomplete) == expected

    actual = defaults(args.destination)
    assert actual == expected, (expected, actual)
    assert snapshot(args.source) == before, "Source changed"
    empty_tables = sorted(table for table, state in restored.items() if state["rows"] == 0)
    assert before.keys() == restored.keys()
    for table, source in before.items():
        target = restored[table]
        for key in ("rows", "data_sha256", "effective_ddl_sha256"):
            assert source[key] == target[key], (table, key)
        target["source_ddl_sha256"] = source["ddl_sha256"]
    print(json.dumps({
        "project": project, "source": args.source, "destination": args.destination,
        "expected": expected, "actual": actual, "tables": restored,
        "rejections_passed": len(rejections), "rejections": rejections,
        "import_failure_destination_retained": True, "empty_tables": empty_tables,
        "source_unchanged": True, "result": "PASS",
    }, indent=2))


if __name__ == "__main__":
    main()
