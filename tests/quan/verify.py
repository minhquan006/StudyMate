#!/usr/bin/env python3
"""Run the selected isolated acceptance suites and retain their complete logs."""
import argparse
import hashlib
import json
import os
from pathlib import Path
import subprocess
import sys
import time

ROOT = Path(__file__).resolve().parents[2]


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--suite', choices=['sqlite', 'mysql', 'compose', 'live', 'final', 'all'], default='all')
    parser.add_argument('--artifacts', type=Path, required=True)
    parser.add_argument('--browser-executable')
    parser.add_argument('--node-module-path', type=Path)
    parser.add_argument('--node-executable', type=Path)
    parser.add_argument('--allow-live', action='store_true')
    parser.add_argument('--model')
    parser.add_argument('--live-errors-only', action='store_true')
    args = parser.parse_args()
    output = args.artifacts.resolve()
    output.mkdir(parents=True, exist_ok=True)
    suites = ['sqlite', 'mysql', 'compose'] if args.suite == 'all' else (['compose', 'live'] if args.suite == 'final' else [args.suite])
    if 'live' in suites and not (args.allow_live and args.model and args.browser_executable):
        parser.error('Live checks require --allow-live, --model and --browser-executable')
    history = output / 'suite-results.json'
    records = json.loads(history.read_text()) if history.exists() else []
    for name in suites:
        script = 'live_http.py' if name == 'live' else ('compose.py' if name == 'compose' else 'run.py')
        command = [sys.executable, str(ROOT / 'tests/quan' / script)]
        if name == 'live':
            command += ['--allow-live', '--model', args.model, '--browser-executable', args.browser_executable,
                        '--artifacts', str(output / ('live-errors' if args.live_errors_only else 'live'))]
            if args.live_errors_only:
                command += ['--errors-only']
        elif name != 'compose':
            command += ['--driver', name]
            if name == 'mysql' and args.browser_executable:
                command += ['--browser', '--browser-executable', args.browser_executable,
                            '--artifacts', str(output / 'browser')]
        print('Running isolated suite:', name, flush=True)
        start = time.monotonic()
        env = os.environ.copy()
        if args.node_module_path:
            env['NODE_PATH'] = str(args.node_module_path.resolve())
        if args.node_executable:
            env['STUDYMATE_NODE_BINARY'] = str(args.node_executable.resolve())
        result = subprocess.run(command, cwd=ROOT, env=env, text=True,
                                stdout=subprocess.PIPE, stderr=subprocess.STDOUT)
        log_name = name + '.txt'
        attempt = 2
        while (output / log_name).exists():
            log_name = f'{name}-{attempt}.txt'
            attempt += 1
        (output / log_name).write_text(result.stdout, encoding='utf-8')
        runtime_files = [p for folder in ['app', 'database', 'docker', 'public']
                         for p in (ROOT / folder).rglob('*') if p.is_file()]
        runtime_files += [ROOT / p for p in ['Dockerfile', 'compose.yaml', 'compose.qa.yaml', 'config.example.php', 'tools/wait_for_db.php']]
        fingerprint = {p.relative_to(ROOT).as_posix(): hashlib.sha256(p.read_bytes()).hexdigest()
                       for p in sorted(runtime_files)}
        fingerprint_name = log_name + '.source.json'
        (output / fingerprint_name).write_text(json.dumps(fingerprint, indent=2) + '\n')
        record = {'suite': name, 'exit_code': result.returncode,
                  'elapsed_seconds': round(time.monotonic() - start, 2),
                  'log': log_name, 'runtime_fingerprint': fingerprint_name}
        records.append(record)
        (output / 'suite-results.json').write_text(json.dumps(records, indent=2) + '\n')
        print(json.dumps(record), flush=True)
        print('\n'.join(result.stdout.splitlines()[-8:]), flush=True)
        if result.returncode:
            raise SystemExit(result.returncode)


if __name__ == '__main__':
    main()
