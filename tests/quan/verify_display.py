#!/usr/bin/env python3
"""Verify display choices on a disposable SQLite copy; never use the demo database."""
import argparse
import hashlib
import json
import os
from pathlib import Path
import shutil
import socket
import subprocess
import tempfile
import time
import urllib.request

ROOT = Path(__file__).resolve().parents[2]

def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--php-executable', default=shutil.which('php'))
    parser.add_argument('--node-executable', default=shutil.which('node'))
    parser.add_argument('--node-module-path')
    parser.add_argument('--browser-executable')
    parser.add_argument('--artifacts', type=Path, default=ROOT / 'storage' / 'qa-display')
    args = parser.parse_args()
    if not args.php_executable or not args.node_executable:
        parser.error('PHP with PDO SQLite and Node.js with Playwright are required')
    output = args.artifacts.resolve()
    output.mkdir(parents=True, exist_ok=True)
    files = [p for folder in ['app', 'public', 'database', 'docker'] for p in (ROOT / folder).rglob('*') if p.is_file()]
    files += [ROOT / name for name in ['Dockerfile', 'compose.yaml', 'compose.qa.yaml', 'config.example.php'] if (ROOT / name).is_file()]
    fingerprint = {p.relative_to(ROOT).as_posix(): hashlib.sha256(p.read_bytes()).hexdigest() for p in sorted(files)}
    (output / 'runtime.source.json').write_text(json.dumps(fingerprint, indent=2) + '\n')
    env = os.environ.copy()
    env['STUDYMATE_TEST_ARTIFACTS'] = str(output)
    if args.node_module_path:
        env['NODE_PATH'] = args.node_module_path
    if args.browser_executable:
        env['STUDYMATE_BROWSER_EXECUTABLE'] = args.browser_executable
    records = []
    with tempfile.TemporaryDirectory(prefix='studymate-display-') as temp:
        app = Path(temp) / 'app'
        ignored = {'.git', 'config.php', '.env', 'storage', 'node_modules', '__pycache__'}
        shutil.copytree(ROOT, app, ignore=lambda parent, names: [n for n in names if n in ignored or n.startswith('.env.') and not n.endswith('.example')])
        (app / 'storage').mkdir(mode=0o700)
        (app / 'config.php').write_text("""<?php
$config=require __DIR__.'/config.example.php';
return array_replace($config,[
 'db_driver'=>'sqlite','sqlite_path'=>__DIR__.'/storage/display.sqlite',
 'app_env'=>'local','secure_cookie'=>false,'demo_password'=>'StudyMate2026!',
 'ai_enabled'=>false,'gemini_api_key'=>''
]);
""")
        def run(name, command):
            with (output / (name + '.txt')).open('w') as log:
                result = subprocess.run(command, cwd=app, env=env, stdout=log, stderr=subprocess.STDOUT)
            records.append({'name': name, 'exit_code': result.returncode, 'log': name + '.txt'})
            if result.returncode:
                raise RuntimeError(f'{name} failed; see {output / (name + ".txt")}')
        server = None
        try:
            run('setup', [args.php_executable, 'tools/setup.php'])
            run('service-fixture', [args.php_executable, 'tests/run.php'])
            run('display-unit', [args.php_executable, 'tests/quan/display_preferences.php'])
            with socket.socket() as listener:
                listener.bind(('127.0.0.1', 0))
                port = listener.getsockname()[1]
            env['STUDYMATE_TEST_URL'] = f'http://127.0.0.1:{port}/index.php'
            with (output / 'http-server.txt').open('w') as server_log:
                server = subprocess.Popen([args.php_executable, '-S', f'127.0.0.1:{port}', '-t', 'public'], cwd=app, env=env, stdout=server_log, stderr=subprocess.STDOUT)
                deadline = time.monotonic() + 15
                while True:
                    try:
                        with urllib.request.urlopen(env['STUDYMATE_TEST_URL'] + '?page=health', timeout=1) as response:
                            if response.status == 200:
                                break
                    except OSError:
                        if server.poll() is not None or time.monotonic() >= deadline:
                            raise RuntimeError('Disposable PHP server did not become ready')
                        time.sleep(.1)
                run('display-browser', [args.node_executable, 'tests/quan/glass_preferences.cjs'])
        finally:
            if server is not None:
                server.terminate()
                try:
                    server.wait(timeout=5)
                except subprocess.TimeoutExpired:
                    server.kill()
                    server.wait()
            (output / 'suite-results.json').write_text(json.dumps({'records': records, 'runtime_fingerprint': 'runtime.source.json', 'database': 'disposable SQLite; removed on exit', 'live_provider': 'not run; AI disabled'}, indent=2) + '\n')
    changed = [name for name, digest in fingerprint.items() if hashlib.sha256((ROOT / name).read_bytes()).hexdigest() != digest]
    if changed:
        raise RuntimeError('Source changed during verification: ' + ', '.join(changed))
    report = json.loads((output / 'QA_DISPLAY_PREFERENCES.json').read_text())
    print(f"PASS: {len(report['checks'])} Chrome display checks; PHP unit log: display-unit.txt; disposable copy removed; live AI not tested.")

if __name__ == '__main__':
    main()
