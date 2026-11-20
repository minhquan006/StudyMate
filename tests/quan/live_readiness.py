#!/usr/bin/env python3
"""Exercise live runner readiness without containers, credentials or Gemini calls.

Compose config and the HTTP health request are real. Up/down and key loading are
intercepted; execution stops before the first login or provider request.
"""
import contextlib
import http.server
import io
import json
import os
from pathlib import Path
import subprocess
import sys
import tempfile
import threading
from unittest.mock import patch

import live_http


class ReadinessComplete(Exception):
    pass


def main():
    actual_run = subprocess.run
    health_opener = live_http.urllib.request.build_opener()
    state = {'health_requests': 0, 'cleanup': False}

    class Health(http.server.BaseHTTPRequestHandler):
        def do_GET(self):
            assert self.path == '/index.php?page=health'
            state['health_requests'] += 1
            self.send_response(200)
            self.end_headers()
            self.wfile.write(b'{"status":"ok"}')

        def log_message(self, *unused):
            pass

    with tempfile.TemporaryDirectory(prefix='studymate-live-readiness-') as directory:
        work = Path(directory)
        config = work / 'inert-config.php'
        config.write_text('<?php return [];\n')

        def run(arguments, **kwargs):
            if arguments[:2] == ['docker', 'run']:
                return subprocess.CompletedProcess(arguments, 0, json.dumps({'key': 'inert-fixture-key'}), '')
            assert arguments[:2] == ['docker', 'compose'], arguments
            environment = kwargs.get('env', os.environ)
            assert environment.get('DOCKER_CONFIG') == os.environ.get('DOCKER_CONFIG')
            if 'up' in arguments:
                prefix = arguments[:arguments.index('up')]
                result = actual_run(prefix + ['config', '--format', 'json'], **kwargs)
                result.check_returncode()
                resolved = json.loads(result.stdout)
                app = resolved['services']['app']
                state['port'] = int(app['ports'][0]['published'])
                assert app['environment'].get('GEMINI_API_KEY', '') == ''
                server = http.server.HTTPServer(('127.0.0.1', state['port']), Health)
                state['server'] = server
                thread = threading.Thread(target=server.serve_forever, daemon=True)
                thread.start()
                return subprocess.CompletedProcess(arguments, 0, '', '')
            assert 'down' in arguments, arguments
            assert 'WEB_PORT' not in environment and 'GEMINI_API_KEY' not in environment
            state['cleanup'] = True
            return subprocess.CompletedProcess(arguments, 0, '', '')

        def stop_before_login(*unused, **kwargs):
            assert state['health_requests'] == 1
            raise ReadinessComplete()

        arguments = ['live_http.py', '--allow-live', '--errors-only', '--config', str(config),
                     '--model', 'inert-test-model', '--artifacts', str(work / 'artifacts'),
                     '--browser-executable', 'unused']
        poisoned = {'WEB_PORT': '49123', 'DB_PASSWORD': 'inert-host-db',
                    'MYSQL_ROOT_PASSWORD': 'inert-host-root', 'GEMINI_API_KEY': 'inert-host-key',
                    'COMPOSE_PROFILES': 'inert-host-profile'}
        try:
            with patch.dict(os.environ, poisoned), patch.object(sys, 'argv', arguments), \
                 patch.object(subprocess, 'run', run), \
                 patch.object(live_http.urllib.request, 'urlopen', health_opener.open), \
                 patch.object(live_http.urllib.request, 'build_opener', stop_before_login), \
                 contextlib.redirect_stdout(io.StringIO()):
                try:
                    live_http.main()
                except ReadinessComplete:
                    pass
                else:
                    raise AssertionError('Runner did not stop at the readiness boundary')
        finally:
            if state.get('server'):
                state['server'].shutdown()
                state['server'].server_close()
        assert state['health_requests'] == 1 and state['cleanup']
        print(json.dumps({'result': 'passed', 'compose_port': state['port'], 'exported_host_port': 49123,
                          'real_http_health_requests': state['health_requests'], 'isolated_cleanup': state['cleanup'],
                          'containers_started': False, 'live_gemini': 'not run'}))


if __name__ == '__main__':
    main()
