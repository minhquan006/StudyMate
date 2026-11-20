#!/usr/bin/env python3
"""Regressions for release membership and inherited Compose configuration."""
import argparse
import hashlib
import json
import os
from pathlib import Path
import shutil
import subprocess
import sys
import tempfile
from unittest.mock import patch
import zipfile

from compose_env import APP_VARIABLES, COMPOSE_SELECTION, compose_environment

ROOT = Path(__file__).resolve().parents[2]


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--compose-config', action='store_true', help='Also resolve real Docker Compose configuration')
    args = parser.parse_args()
    checks = []
    skipped = []

    def check(condition, name):
        if not condition:
            raise AssertionError(name)
        checks.append(name)
        print('PASS:', name)

    with tempfile.TemporaryDirectory(prefix='studymate-release-regression-') as directory:
        work = Path(directory)
        repo = work / 'source'
        subprocess.run(['git', 'clone', '--quiet', '--no-hardlinks', str(ROOT), str(repo)], check=True)
        shutil.copy2(ROOT / 'tools/package_release.py', repo / 'tools/package_release.py')
        untracked = ['docs/local-review-marker.txt', 'database/local-review-dump.sql', 'public/local-review.txt']
        private = ['docs/.env.review', 'app/private-review.pem', 'database/private-review.sql',
                   'database/private-review.sql.gz', 'docs/backups/private-review.txt', 'public/review.sqlite', 'public/.ENV.review']
        for name in untracked + private:
            path = repo / name
            path.parent.mkdir(parents=True, exist_ok=True)
            path.write_text('INERT TEST FIXTURE; NO PRIVATE DATA\n')
        # Even accidentally tracked private files must be excluded.
        subprocess.run(['git', '-C', str(repo), 'add', '-f', '--'] + private, check=True)
        link = repo / 'public/review-link.txt'
        (work / 'outside.txt').write_text('INERT OUTSIDE FIXTURE\n')
        links = []
        try:
            link.symlink_to(work / 'outside.txt')
        except OSError as error:
            if os.name != 'nt':
                raise
            skipped.append('symlink fixture requires Windows developer mode or permission: ' + str(error))
        else:
            subprocess.run(['git', '-C', str(repo), 'add', '--', 'public/review-link.txt'], check=True)
            links.append('public/review-link.txt')
        allowed = 'docs/review file with spaces.txt'
        (repo / allowed).write_text('Tracked public documentation fixture.\n')
        subprocess.run(['git', '-C', str(repo), 'add', '--', allowed], check=True)
        command = [sys.executable, str(repo / 'tools/package_release.py'), '--output', str(work / 'release')]
        subprocess.run(command, check=True, capture_output=True, text=True)
        archive = next((work / 'release').glob('*.zip'))
        with zipfile.ZipFile(archive) as zipped:
            members = zipped.namelist()
            prefix = members[0].split('/')[0] + '/'
            manifest = json.loads(zipped.read(prefix + 'RELEASE_MANIFEST.json'))
            for name in untracked + private + links:
                check(prefix + name not in members and name not in manifest['files'], 'ZIP excludes ' + name)
            for name in ['.env.example', 'docker/config.php', 'database/schema.mysql.sql', 'storage/.gitkeep', allowed]:
                check(prefix + name in members, 'ZIP retains tracked source ' + repr(name))
            check(zipped.testzip() is None, 'ZIP CRC verification')
            check(all(hashlib.sha256(zipped.read(prefix + name)).hexdigest() == digest
                      for name, digest in manifest['files'].items()), 'all ZIP file hashes match manifest')
            check(manifest['working_tree_changes'], 'manifest identifies a modified candidate')
        digest = hashlib.sha256(archive.read_bytes()).hexdigest()
        check(archive.with_suffix('.zip.sha256').read_text().split()[0] == digest, 'archive checksum matches ZIP')
        second = subprocess.run(command, capture_output=True, text=True)
        check(second.returncode != 0 and hashlib.sha256(archive.read_bytes()).hexdigest() == digest,
              'existing release is not overwritten')
        inside = subprocess.run([sys.executable, str(repo / 'tools/package_release.py'), '--output', str(repo / 'output')],
                                capture_output=True, text=True)
        check(inside.returncode != 0 and not (repo / 'output').exists(), 'output inside source checkout is refused')

        poisoned = {name: 'INERT-HOST-OVERRIDE' for name in APP_VARIABLES | COMPOSE_SELECTION}
        poisoned.update({'WEB_PORT': '49123', 'GEMINI_API_KEY': 'inert-review-key'})
        connection = {name: os.environ[name] for name in ['DOCKER_HOST', 'DOCKER_CONTEXT', 'DOCKER_CONFIG',
                                                        'DOCKER_TLS_VERIFY', 'DOCKER_CERT_PATH', 'PATH', 'HOME']
                      if name in os.environ}
        with patch.dict(os.environ, poisoned):
            env = compose_environment()
            check(all(name not in env for name in APP_VARIABLES | COMPOSE_SELECTION), 'host app/Compose selection overrides removed')
            check(all(env.get(name) == value for name, value in connection.items()), 'actual Docker connection settings preserved')
            check(os.environ['WEB_PORT'] == '49123', 'parent shell environment is unchanged')
            if args.compose_config:
                envfile = work / 'qa.env'
                envfile.write_text('DB_PASSWORD=qa-fixture-db\nMYSQL_ROOT_PASSWORD=qa-fixture-root\n'
                                   'WEB_PORT=49124\nDEMO_PASSWORD=StudyMate2026!\n'
                                   'GEMINI_MODEL=gemini-2.5-flash-lite\nDB_WAIT_TIMEOUT=60\n')
                prefix = ['docker', 'compose', '--env-file', str(envfile), '-f', str(ROOT / 'compose.yaml'),
                          '-f', str(ROOT / 'compose.qa.yaml'), '-p', 'studymate-quan-env-review', 'config', '--format', 'json']
                config = json.loads(subprocess.check_output(prefix, env=env))
                app = config['services']['app']
                db = config['services']['db']
                check(str(app['ports'][0]['published']) == '49124', 'Compose uses the QA env-file port instead of exported WEB_PORT')
                check(app['ports'][0]['host_ip'] == '127.0.0.1', 'QA HTTP remains bound to loopback')
                check(app['environment']['DB_PASSWORD'] == db['environment']['MYSQL_PASSWORD'] == 'qa-fixture-db',
                      'Compose uses matching QA database credentials')
                check(db['environment']['MYSQL_ROOT_PASSWORD'] == 'qa-fixture-root', 'Compose uses the QA root credential')
                check(app['environment'].get('GEMINI_API_KEY', '') == '', 'host Gemini key does not enter QA containers')
                check(app['environment']['GEMINI_MODEL'] == 'gemini-2.5-flash-lite', 'host model does not override QA model')
                check(app['environment']['DEMO_PASSWORD'] == 'StudyMate2026!', 'host demo password does not override QA logins')
        fixtures = {'DOCKER_CONTEXT': 'fixture-context', 'DOCKER_CONFIG': '/fixture/docker',
                    'DOCKER_HOST': 'tcp://fixture:2376', 'DOCKER_TLS_VERIFY': '1', 'DOCKER_CERT_PATH': '/fixture/certs'}
        with patch.dict(os.environ, fixtures):
            check(all(compose_environment().get(name) == value for name, value in fixtures.items()),
                  'Docker context, remote host and TLS settings survive filtering')
    print(json.dumps({'result': 'passed', 'checks': len(checks), 'skipped': skipped,
                      'compose_config': args.compose_config, 'live_gemini': 'not run'}))


if __name__ == '__main__':
    main()
