import os
from pathlib import Path
import re
import socket
import subprocess
import sys
import time

checkout = Path(sys.argv[1]).resolve() if len(sys.argv)>1 else Path(__file__).resolve().parents[2]
sys.argv[:] = [str(checkout / 'tests/quan/run.py')]
sys.path.insert(0, str(checkout / 'tests/quan'))
os.environ['STUDYMATE_QUAN_TEST'] = '1'
php_bin = os.environ.get('QA_PHP', str(checkout.parent / 'runtime/bin/php-media'))
original = (checkout / 'tests/quan/run.py').read_text()
source = original[original.index('import argparse'):]

def replace(pattern, updated):
    global source
    source, count = re.subn(pattern, lambda _: updated, source, flags=re.M)
    if count != 1:
        raise ValueError((pattern, count))

replace(r'^ROOT = .*$', 'ROOT = Path(' + repr(str(checkout)) + ')')
replace(r'^    def php\(source\):\n.*$', """    def php(source):
        return cmd(php_bin, '-r', "require 'app/bootstrap.php';require 'database/seed.php';"+source, cwd=app)""")
replace(r"^        cmd\('docker', 'network', 'create', network\)[\s\S]*?^        for attempt", """        process, base = launch(app)
        for attempt""")
replace(r"^        binding = .*\n        base = .*\n", '')
replace(r"^        cmd\('docker','restart',server\)\n.*\n.*\n", """        stop(process)
        process, base = launch(app)
""")
replace(r"^        subprocess.run\(\['docker','rm'.*\n.*$", "        stop(process)")
source = re.sub(r"cmd\('docker', ?'exec', ?server, ?'php',", "cmd(php_bin,", source)
source = source.replace("'tools/setup.php')", "'tools/setup.php', cwd=app)")
source = source.replace("'tests/run.php')", "'tests/run.php', cwd=app)")
source = source.replace("'tests/quan/ai.php')", "'tests/quan/ai.php', cwd=app)")
source = source.replace("str(user),str(status))", "str(user),str(status),cwd=app)")
source = source.replace("'/app/'+str(path.relative_to(app))", "str(path)")
source = source.replace('isolated containers removed on exit', 'local PHP server and disposable DB removed on exit')
replace(r'^        # Two independent sessions', """        verify_originals(php, snapshot, admin, owner, other, student, check)
        # Two independent sessions""")

def launch(app):
    with socket.socket() as listener:
        listener.bind(('127.0.0.1', 0))
        port = listener.getsockname()[1]
    process = subprocess.Popen([php_bin, '-S', f'127.0.0.1:{port}', '-t', 'public'],
                               cwd=app, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
    try:
        for _ in range(50):
            if process.poll() is not None:
                raise RuntimeError('PHP HTTP server exited')
            try:
                with socket.create_connection(('127.0.0.1', port), timeout=.2):
                    return process, f'http://127.0.0.1:{port}/index.php'
            except OSError:
                time.sleep(.05)
        raise RuntimeError('PHP HTTP server did not start')
    except BaseException:
        stop(process)
        raise

def stop(process):
    process.terminate()
    process.wait(timeout=10)

def verify_originals(php, snapshot, admin, owner, other, student, check):
    chapter_count = php("echo val('SELECT COUNT(*) FROM chapters WHERE id=5 AND course_id=3');")
    check(chapter_count == '1', 'original fixture chapter 5 still exists in draft course 3')
    for label, client, action, data, status in [
        ('original category array', admin, 'category_save', {'category_id[]': '99999', 'name': 'Rejected original array'}, 422),
        ('original category alpha', admin, 'category_save', {'category_id': 'abc', 'name': 'Rejected original alpha'}, 422),
        ('original chapter suffix', owner, 'chapter_delete', {'course_id': 3, 'chapter_id': '5abc', 'confirm': 1}, 422),
        ('leading whitespace', admin, 'category_save', {'category_id': ' 1', 'name': 'Rejected whitespace'}, 422),
        ('trailing newline', admin, 'category_save', {'category_id': '1\n', 'name': 'Rejected newline'}, 422),
        ('leading plus', admin, 'category_save', {'category_id': '+1', 'name': 'Rejected leading plus'}, 422),
        ('leading zero', admin, 'category_save', {'category_id': '01', 'name': 'Rejected leading zero'}, 422),
        ('absent category ID with duplicate name', admin, 'category_save', {'category_id': '99999', 'name': php("echo val('SELECT name FROM categories WHERE id=1');")}, 422),
        ('max native integer with no row', admin, 'category_save', {'category_id': '9223372036854775807', 'name': 'Absent max ID'}, 404),
        ('outside native integer range', admin, 'category_save', {'category_id': '9223372036854775808', 'name': 'Rejected overflow'}, 422),
        ('wrong role with valid IDs', student, 'chapter_delete', {'course_id': 3, 'chapter_id': 5, 'confirm': 1}, 403),
        ('wrong owner with valid IDs', other, 'chapter_delete', {'course_id': 3, 'chapter_id': 5, 'confirm': 1}, 404),
        ('malformed ID with bad CSRF', owner, 'chapter_delete', {'course_id': 3, 'chapter_id': '5abc', 'confirm': 1, 'csrf': 'invalid'}, 419),
    ]:
        before = snapshot()
        actual = client.post(action, **data)[0]
        check(actual == status, f'Tú {label} HTTP {actual} expected {status}')
        check(snapshot() == before, f'Tú {label} preserves all 12 tables')

exec(compile(source, str(checkout / 'tests/quan/run.py'), 'exec'), globals())
