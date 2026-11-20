#!/usr/bin/env python3
"""Run limited live Gemini probes against disposable SQLite data, never the local DB."""
import argparse
import json
import os
from pathlib import Path
import shutil
import subprocess
import tempfile

ROOT = Path(__file__).resolve().parents[2]


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--allow-live', action='store_true', required=True,
                        help='Explicitly enable up to six real generation requests')
    parser.add_argument('--config', type=Path, default=ROOT / 'config.php',
                        help='Private PHP config containing a key; mounted read-only')
    parser.add_argument('--model', default='gemini-2.5-flash-lite')
    parser.add_argument('--image', default='studymate-quan-php')
    parser.add_argument('--artifacts', type=Path, required=True)
    args = parser.parse_args()
    private = args.config.resolve(strict=True)
    output = args.artifacts.resolve()
    output.mkdir(parents=True, exist_ok=True)
    cases = ['instructor', 'not-enrolled', 'ask', 'summarize',
             'missing-information', 'injection', 'archived', 'wrong-key']
    records = []
    with tempfile.TemporaryDirectory(prefix='studymate-live-ai-') as temp:
        app = Path(temp) / 'app'
        shutil.copytree(ROOT, app, ignore=lambda path, names: [
            n for n in names if n == '__pycache__' or
            (Path(path) == ROOT and n in ['.git', 'config.php', '.env', 'storage'])])
        (app / 'storage').mkdir(mode=0o700)
        (app / 'config.php').write_text("""<?php
$c=require __DIR__.'/config.example.php';
$private=require '/run/studymate-private-config.php';
$c['db_driver']='sqlite';$c['sqlite_path']=__DIR__.'/storage/live-test.sqlite';
$c['ai_enabled']=true;$c['gemini_api_key']=$private['gemini_api_key']??'';
$c['ai_model']=getenv('STUDYMATE_LIVE_MODEL');
return $c;
""")
        (app / 'config.php').chmod(0o600)
        prefix = ['docker', 'run', '--rm', '--user', f'{os.getuid()}:{os.getgid()}',
                  '-v', f'{app}:/app', '-v', f'{private}:/run/studymate-private-config.php:ro',
                  '-w', '/app', '-e', 'STUDYMATE_QUAN_LIVE_TEST=1',
                  '-e', 'STUDYMATE_LIVE_MODEL=' + args.model, args.image, 'php']
        subprocess.run(prefix + ['tools/setup.php'], check=True, capture_output=True, text=True)
        for case in cases:
            result = subprocess.run(prefix + ['tests/quan/live_ai.php', case],
                                    capture_output=True, text=True, timeout=45)
            if result.returncode:
                raise RuntimeError(f'Live probe failed: {case}; no provider response or key printed')
            record = json.loads(result.stdout)
            expected = 403 if case in ['instructor', 'not-enrolled'] else (503 if case == 'wrong-key' else 200)
            record['expected_app_status'] = expected
            record['transport_check_passed'] = (
                record['app_status'] == expected and
                record['provider_calls'] == (0 if expected == 403 else 1) and
                (expected != 200 or bool(record['answer'])))
            records.append(record)
            (output / 'gemini-live.json').write_text(
                json.dumps(records, ensure_ascii=False, indent=2) + '\n')
            print(json.dumps(record, ensure_ascii=False), flush=True)
    print('Live probes recorded. Review generated answers before signing FR10; '
          'SQLite probes do not replace MySQL/HTTP/browser acceptance.', flush=True)
    if not all(record['transport_check_passed'] for record in records):
        raise SystemExit(1)


if __name__ == '__main__':
    main()
