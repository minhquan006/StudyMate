#!/usr/bin/env python3
"""Isolated HTTP, AI and service regression; never mounts the development DB.
Requires Docker and Python 3. See tests/quan/README.md.
"""
import argparse, http.cookiejar, json, os
from pathlib import Path
import re, secrets, shutil, subprocess, tempfile, time, urllib.error, urllib.parse, urllib.request

ROOT = Path(__file__).resolve().parents[2]
parser = argparse.ArgumentParser()
parser.add_argument('--driver', choices=['sqlite', 'mysql'], default='sqlite')
parser.add_argument('--image', default='studymate-quan-php')
parser.add_argument('--browser', action='store_true', help='Requires Python Playwright and Chromium')
parser.add_argument('--browser-executable', default=os.environ.get('STUDYMATE_BROWSER_EXECUTABLE'), help='Path to the actual browser used for evidence')
parser.add_argument('--artifacts', type=Path, help='Optional screenshot output directory')
args = parser.parse_args()
checks = []

def cmd(*argv, **kwargs):
    return subprocess.run(argv, check=True, text=True, stdout=subprocess.PIPE, stderr=subprocess.PIPE, **kwargs).stdout.strip()

def check(ok, name):
    if not ok:
        raise AssertionError(name)
    checks.append(name)
    print('PASS:', name, flush=True)

class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, *args, **kwargs):
        return None

class Client:
    def __init__(self):
        self.op = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()), NoRedirect())
        self.csrf = ''
    def request(self, query='', data=None):
        data = None if data is None else urllib.parse.urlencode(data).encode()
        try:
            response = self.op.open(base + query, data=data, timeout=35)
        except urllib.error.HTTPError as e:
            response = e
        body = response.read().decode()
        check('Fatal error' not in body and 'Warning:' not in body and 'Stack trace:' not in body, 'no PHP diagnostics: ' + query)
        return response.code, response.headers, body
    def login(self, who):
        status, _, body = self.request('?page=login')
        self.csrf = re.search(r'name="csrf" value="([^"]+)"', body).group(1)
        status, headers, _ = self.request(data={'action': 'login', 'csrf': self.csrf, 'email': who+'@studymate.test', 'password': 'StudyMate2026!'})
        check(status == 303, 'login ' + who)
        status, _, body = self.request(headers['Location'].replace('/index.php', ''))
        self.csrf = re.search(r'name="csrf" value="([^"]+)"', body).group(1)
    def post(self, action, **data):
        return self.request(data={'action': action, 'csrf': self.csrf, **data})

def quiz(**overrides):
    data = {}
    for i in range(5):
        data[f'questions[{i}][prompt]'] = f'Valid question {i}'
        data[f'questions[{i}][explanation]'] = 'Explanation'
        data[f'questions[{i}][correct]'] = '0'
        for j in range(4):
            data[f'questions[{i}][answers][{j}]'] = f'Answer {j}'
    data.update(overrides)
    return data

with tempfile.TemporaryDirectory(prefix='studymate-quan-') as temp:
    work = Path(temp)
    app = work/'app'
    shutil.copytree(ROOT, app, ignore=lambda path,names: [n for n in names if n=='__pycache__' or (Path(path)==ROOT and n in ['.git','config.php','.env','storage'])])
    (app/'storage').mkdir(mode=0o700)
    (app/'config.php').write_text("""<?php
$c=require __DIR__.'/config.example.php';
$c['db_driver']=getenv('DB_DRIVER')?:'sqlite';
$c['db_host']='db';$c['db_name']='studymate_test';$c['db_user']='studymate';$c['db_password']=getenv('DB_PASSWORD')?:'';
$c['sqlite_path']=__DIR__.'/storage/test.sqlite';
$c['ai_enabled']=false;$c['gemini_api_key']='';
return $c;
""")
    password = secrets.token_hex(24)
    envfile = work/'test.env'
    envfile.write_text('DB_DRIVER='+args.driver+'\nDB_PASSWORD='+password+'\nMYSQL_DATABASE=studymate_test\nMYSQL_USER=studymate\nMYSQL_PASSWORD='+password+'\nMYSQL_ROOT_PASSWORD='+secrets.token_hex(24)+'\nSTUDYMATE_QUAN_TEST=1\n')
    envfile.chmod(0o600)
    tag = 'studymate-quan-'+secrets.token_hex(4)
    server, db, network = tag+'-app', tag+'-db', tag+'-net'
    def php(source):
        return cmd('docker', 'exec', server, 'php', '-r', "require '/app/app/bootstrap.php';require '/app/database/seed.php';"+source)
    def snapshot():
        return php("$out=[];foreach(['users','categories','courses','chapters','lessons','quizzes','questions','answers','enrollments','lesson_progress','quiz_attempts','quiz_answers'] as $t)$out[$t]=all('SELECT * FROM '.$t.' ORDER BY id');echo hash('sha256',json_encode($out));")
    try:
        cmd('docker', 'network', 'create', network)
        if args.driver == 'mysql':
            cmd('docker', 'run', '-d', '--name', db, '--network', network, '--network-alias', 'db', '--env-file', str(envfile), 'mysql:8.4')
        cmd('docker', 'run', '-d', '--name', server, '--network', network, '--user', f'{os.getuid()}:{os.getgid()}', '--env-file', str(envfile), '-p', '127.0.0.1::8000', '-v', f'{app}:/app', '-w', '/app', args.image, 'php', '-S', '0.0.0.0:8000', '-t', 'public')
        for attempt in range(120):
            try:
                setup = cmd('docker', 'exec', server, 'php', 'tools/setup.php')
                break
            except subprocess.CalledProcessError:
                if attempt == 119:
                    raise RuntimeError('Database did not become ready')
                time.sleep(.5)
        check('Setup hoàn tất' in setup, 'seed isolated '+args.driver+' database')
        setup2 = cmd('docker', 'exec', server, 'php', 'tools/setup.php')
        check('không ghi đè' in setup2, 'setup repeat preserves data')
        # Lint, preserving every PHP invocation exit code.
        for path in sorted(app.rglob('*.php')):
            cmd('docker', 'exec', server, 'php', '-l', '/app/'+str(path.relative_to(app)))
        check(True, 'all PHP files lint')
        service = cmd('docker', 'exec', server, 'php', 'tests/run.php')
        check('14 checks passed. Driver: '+args.driver in service, '14 existing service checks')
        ai = cmd('docker', 'exec', server, 'php', 'tests/quan/ai.php')
        print(ai, flush=True)
        check('AI checks passed' in ai, 'AI mocked transport and concurrent quota suite')
        for user, status in [(3,403), (2,403)]:
            print(cmd('docker','exec',server,'php','tests/quan/ai.php',str(user),str(status)), flush=True)
        binding = json.loads(cmd('docker', 'inspect', server))[0]['NetworkSettings']['Ports']['8000/tcp'][0]
        base = 'http://127.0.0.1:'+binding['HostPort']+'/index.php'
        guest, owner, other, admin, student = (Client() for _ in range(5))
        for client, who in [(owner,'instructor1'),(other,'instructor2'),(admin,'admin'),(student,'student1')]:
            client.login(who)
        check(json.loads(guest.request('?page=health')[2])=={'status':'ok'}, 'HTTP health reaches database')
        actions=['course_save','course_state','course_delete','chapter_save','chapter_delete','lesson_save','lesson_delete','quiz_save','user_status','category_save','category_delete','ai']
        before=snapshot()
        for action in actions:
            for token in ['', 'incorrect']:
                check(owner.post(action,csrf=token)[0]==419, 'CSRF '+action+' '+('missing' if not token else 'invalid'))
        check(snapshot()==before, 'CSRF failures leave all DB tables unchanged')
        # Course2 is published and enrolled. Course3 is draft, owned by instructor1.
        before=snapshot()
        for action, data in [
            ('chapter_delete',dict(course_id=2,chapter_id=3,confirm=1)),
            ('lesson_delete',dict(course_id=2,lesson_id=7,confirm=1)),
            ('lesson_save',dict(course_id=2,lesson_id=7,title='Existing lesson',body='Valid body '*10,position=99)),
            ('chapter_save',dict(course_id=2,chapter_id=3,title='Existing chapter',position=99)),
            ('quiz_save',dict(course_id=2,**quiz())),
        ]:
            check(owner.post(action,**data)[0]==409,'enrolled course blocks '+action)
        check(snapshot()==before,'closed structure rejects preserve all data')
        before=snapshot()
        for client, expected, name in [(other,404,'wrong owner'),(student,403,'wrong role')]:
            check(client.post('chapter_delete',course_id=3,chapter_id=5,confirm=1)[0]==expected,name+' chapter deletion')
            check(client.post('quiz_save',course_id=3,**quiz())[0]==expected,name+' quiz mutation')
        check(owner.post('lesson_save',course_id=3,lesson_id=7,title='Foreign lesson',body='Valid body '*10,position=1)[0]==404,'foreign lesson ID')
        check(owner.post('chapter_delete',course_id=3,chapter_id=3,confirm=1)[0]==404,'foreign chapter ID')
        check(snapshot()==before,'forged identifiers and roles preserve all data')
        before=snapshot()
        mutations=[
            ('course_save',dict(course_id=3,title='Valid course name',description='Valid course description for testing',category_id=1)),
            ('course_state',dict(course_id=3,target='published',confirm=1)),
            ('course_delete',dict(course_id=3,confirm=1)),
            ('chapter_save',dict(course_id=3,title='Valid chapter',position=1)),
            ('chapter_delete',dict(course_id=3,chapter_id=5,confirm=1)),
            ('lesson_save',dict(course_id=3,title='Valid lesson',body='Valid body '*10,chapter_id=5,position=1)),
            ('lesson_delete',dict(course_id=3,lesson_id=13,confirm=1)),
            ('quiz_save',dict(course_id=3,**quiz())),
        ]
        for action,data in mutations:
            check(other.post(action,**data)[0]==404,'all Instructor handlers reject foreign owner: '+action)
            check(student.post(action,**data)[0]==403,'all Instructor handlers reject Student: '+action)
        for action,data in [('user_status',dict(user_id=2,is_active=0,confirm=1)),('category_save',dict(name='Unauthorized category')),('category_delete',dict(category_id=1,confirm=1))]:
            check(owner.post(action,**data)[0]==403,'Admin handler rejects Instructor: '+action)
        check(snapshot()==before,'authorization matrix preserves all tables')
        for state in ['published','archived']:
            php("run('UPDATE courses SET status=? WHERE id=3',['"+state+"']);")
            before=snapshot()
            check(owner.post('chapter_save',course_id=3,title='New chapter',position=3)[0]==409,state+' without enrollment blocks structure')
            check(owner.post('quiz_save',course_id=3,**quiz())[0]==409,state+' without enrollment blocks quiz')
            check(snapshot()==before,state+' rejects preserve data')
            check(owner.post('chapter_save',course_id=3,chapter_id=5,title='Presentation edit',position=1)[0]==303,state+' allows chapter title edit')
        php("run('UPDATE courses SET status=? WHERE id=3',['draft']);")
        before=snapshot()
        invalid=[{'questions[0]':'broken'}, {'questions[0][prompt]':'a'*501}, {'questions[0][explanation]':'a'*1001}, {'questions[0][answers][0]':'a'*251}, {'questions[0][correct]':'4'}, {'questions[0][correct]':'-1'}, {'questions[0][correct]':'abc'}, {'questions[0][correct]':'1.5'}, {'questions[0][answers][0][nested]':'bad'}]
        for i, change in enumerate(invalid):
            data=quiz()
            if 'questions[0]' in change:
                data={k:v for k,v in data.items() if not k.startswith('questions[0]')}
            if 'questions[0][answers][0][nested]' in change:
                data.pop('questions[0][answers][0]')
            data.update(change)
            check(owner.post('quiz_save',course_id=3,**data)[0]==422,'invalid quiz boundary '+str(i+1))
        check(snapshot()==before,'invalid quiz payloads are atomic')
        boundary=quiz(**{'questions[0][prompt]':'a'*500,'questions[0][explanation]':'a'*1000,'questions[0][answers][0]':'a'*250})
        check(owner.post('quiz_save',course_id=3,**boundary)[0]==303,'maximum allowed quiz lengths accepted')
        check(admin.post('user_status',user_id=5,is_active=0,confirm=1)[0]==403,'Admin cannot lock Admin')
        before=snapshot()
        check(admin.post('user_status',user_id=2,is_active='bad',confirm=1)[0]==422,'invalid active state is not coerced to locked')
        check(admin.post('category_save',category_id=99999,name='Missing category')[0]==404,'update absent category returns404')
        check(snapshot()==before,'invalid Admin edits preserve DB')
        # Two independent sessions must both stay revoked even after unlock.
        s2a,s2b=Client(),Client()
        s2a.login('student2');s2b.login('student2')
        check(admin.post('user_status',user_id=2,is_active=0,confirm=1)[0]==303,'lock Student2')
        check(admin.post('user_status',user_id=2,is_active=1,confirm=1)[0]==303,'unlock Student2')
        check(s2a.request('?page=student')[0]==401 and s2b.request('?page=student')[0]==401,'both old sessions revoked after lock/unlock')
        s2=Client();s2.login('student2')
        # Controlled fixtures for pagination; never touch the development DB.
        php("$stamp=['created_at'=>now(),'updated_at'=>now()];for($i=0;$i<14;$i++){insertRow('categories',['name'=>'Page category '.$i]+$stamp);insertRow('courses',['instructor_id'=>3,'category_id'=>1,'title'=>'Page course '.$i,'description'=>'Pagination test course description','cover_key'=>'default','status'=>'draft']+$stamp);$uid=insertRow('users',['name'=>'Page user '.$i,'email'=>'page'.$i.'@studymate.test','password'=>'not-used','role'=>'student','is_active'=>1,'auth_version'=>1]+$stamp);insertRow('enrollments',['user_id'=>$uid,'course_id'=>2,'last_lesson_id'=>null,'completed_at'=>null]+$stamp);}")
        def rows(body):
            tbody=re.search(r'<tbody>(.*?)</tbody>',body,re.S)
            return re.findall(r'<tr>.*?</tr>',tbody.group(1),re.S)
        for page in ['?page=instructor','?page=admin&tab=users','?page=admin&tab=courses','?page=admin&tab=categories','?page=edit&id=2&tab=students']:
            client=owner if 'instructor' in page or 'page=edit' in page else admin
            first=client.request(page)[2];second=client.request(page+'&p=2')[2]
            check(len(rows(first))==12 and 0<len(rows(second))<12,'bounded pagination '+page)
            check(set(rows(first)).isdisjoint(rows(second)),'non-overlapping pages '+page)
            check('rel="next"' in first and 'rel="prev"' in second,'navigation '+page)
        body=admin.request('?page=admin&tab=users&q=page&per_page=5')[2]
        check(len(rows(body))==5 and all('page' in r for r in rows(body)),'filtered users first page')
        check('q=page' in body and 'per_page=5' in body,'pager preserves search and size')
        body=admin.request('?page=admin&tab=users&q=does-not-exist')[2]
        check(len(rows(body))==0 and bool(re.search(r'Trang\s+1\s*/\s*1',body)),'empty search has valid page')
        for suffix in ['&p=-1&per_page=0','&p=999999999999&per_page=999','&p=bad&per_page=bad','&p[]=2&per_page[]=5']:
            check(admin.request('?page=admin&tab=users'+suffix)[0]==200,'invalid page inputs bounded '+suffix)
        for suffix in ['&per_page=bad','&per_page[]=5']:
            body=admin.request('?page=admin&tab=users'+suffix)[2]
            check(len(rows(body))==12,'invalid page size retains the default 12 rows '+suffix)
        body=owner.request('?page=edit&id=2&tab=students&per_page=50')[2]
        check(any('Học viên Demo' in row and '100%' in row and '100' in row and 'Có' in row for row in rows(body)),'aggregate roster preserves progress/best/completion')
        # Browser checks run against the same isolated app and seeded pagination fixtures.
        if args.browser:
            browser_env=os.environ.copy()
            browser_env.update({'STUDYMATE_TEST_BASE_URL':base,'STUDYMATE_TEST_DRIVER':args.driver})
            if args.browser_executable:browser_env['STUDYMATE_BROWSER_EXECUTABLE']=args.browser_executable
            if args.artifacts:browser_env['STUDYMATE_BROWSER_ARTIFACTS']=str(args.artifacts.resolve())
            audit=json.loads(cmd(os.environ.get('STUDYMATE_NODE_BINARY') or shutil.which('node') or 'node',str(ROOT/'tests/quan/browser.mjs'),env=browser_env))
            print('Browser: '+json.dumps(audit['browser']),flush=True)
            for name in audit['checks']:check(True,name)
        # Complete the integrated Instructor -> Student -> AI-disabled -> Admin flow.
        check(owner.post('course_state',course_id=3,target='published',confirm=1)[0]==303,'publish valid draft')
        check(s2.post('enroll',course_id=3)[0]==303,'Student enrolls published course')
        lids=json.loads(php("echo json_encode(array_column(all('SELECT l.id FROM lessons l JOIN chapters c ON c.id=l.chapter_id WHERE c.course_id=3'),'id'));") )
        for lid in lids:
            check(s2.post('complete',course_id=3,lesson_id=lid)[0]==303,'complete lesson '+str(lid))
        answers=json.loads(php("echo json_encode(all('SELECT q.id,a.id answer_id FROM questions q JOIN quizzes z ON z.id=q.quiz_id JOIN answers a ON a.question_id=q.id WHERE z.course_id=3 AND a.is_correct=1'));"))
        import uuid
        payload={'course_id':3,'confirm':1,'request_id':str(uuid.uuid4())}
        payload.update({f'answers[{a["id"]}]':a['answer_id'] for a in answers})
        status,headers,_=s2.post('submit_quiz',**payload)
        check(status==303,'integrated quiz submitted')
        check(bool(re.search(r'100(?:\.00)?<small>%</small>',s2.request(headers['Location'].replace('/index.php',''))[2])),'integrated quiz scored 100%')
        check(s2.post('ai',course_id=3,lesson_id=lids[0],mode='summarize')[0]==503,'missing live AI key reports503')
        check(s2.request('?page=learn&id=3')[0]==200,'learning works after AI failure')
        check(admin.post('course_state',course_id=3,target='archived',confirm=1)[0]==303,'Admin archives course')
        check(s2.request('?page=learn&id=3')[0]==200,'enrolled learner retains archived course access')
        cmd('docker','restart',server)
        binding=json.loads(cmd('docker','inspect',server))[0]['NetworkSettings']['Ports']['8000/tcp'][0]
        base='http://127.0.0.1:'+binding['HostPort']+'/index.php'
        for _ in range(30):
            try:
                if json.loads(guest.request('?page=health')[2])=={'status':'ok'}: break
            except (OSError,ValueError): time.sleep(.2)
        else: raise RuntimeError('restart readiness failed')
        check(php("echo (int)val('SELECT COUNT(*) FROM enrollments WHERE user_id=2 AND course_id=3 AND completed_at IS NOT NULL');")=='1','integrated completion survives restart')
        print(f'RESULT: {len(checks)} runner checks passed; driver={args.driver}; isolated containers removed on exit. Live Gemini not tested.',flush=True)
    finally:
        subprocess.run(['docker','rm','-f','-v',server,db],stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL)
        subprocess.run(['docker','network','rm',network],stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL)
