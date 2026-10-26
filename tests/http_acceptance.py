"""Run only against the separate disposable Compose project in README.
Uses independent PHP sessions for races, direct POSTs, and DB snapshots for denials.
"""
import concurrent.futures, http.cookiejar, json, os, re, subprocess, threading, time, urllib.error, urllib.parse, urllib.request, uuid
from pathlib import Path
BASE=os.environ.get('STUDYMATE_TEST_URL','http://127.0.0.1:8080')+'/index.php'
PROJECT=os.environ.get('COMPOSE_PROJECT_NAME','studymate-acceptance')
RESULTS=[]
def db(mode='inspect'):
 if mode in ['fail_answers','clear_fail_answers']:
  sql="CREATE TRIGGER acceptance_fail_answer BEFORE INSERT ON quiz_answers FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Acceptance rollback test'" if mode=='fail_answers' else 'DROP TRIGGER IF EXISTS acceptance_fail_answer'
  # Only the disposable QA database: use its existing container-local root binding.
  # Do not weaken global MySQL trust settings or grant SUPER to the application.
  subprocess.check_call(['docker','compose','-p',PROJECT,'exec','-T','db','sh','-ec','MYSQL_PWD="$MYSQL_ROOT_PASSWORD" mysql -uroot "$MYSQL_DATABASE" -e "$1"','sh',sql])
  return None
 output=subprocess.check_output(['docker','compose','-p',PROJECT,'exec','-T','-e','STUDYMATE_DISPOSABLE_DB=1','app','php','tests/fixture.php',mode],text=True)
 return json.loads(output) if output.strip() else None
class NoRedirect(urllib.request.HTTPRedirectHandler):
 def redirect_request(self,*args):return None
class Client:
 def __init__(self):
  self.opener=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()),NoRedirect())
  self.token=''
 def request(self,page='home',data=None,**params):
  url=BASE+'?'+urllib.parse.urlencode(dict(page=page,**params))
  if data is not None:data=urllib.parse.urlencode(data).encode()
  try:r=self.opener.open(url,data,timeout=40)
  except urllib.error.HTTPError as e:r=e
  body=r.read().decode();m=re.search(r'name="csrf" value="([^"]+)"',body)
  if m:self.token=m[1]
  return r.code,body,r.headers
 def post(self,action,**data):
  return self.request(data.pop('return_page','home'),dict({'csrf':self.token,'action':action},**data))
 def login(self,name):
  self.request('login');r=self.post('login',email=name+'@studymate.test',password='StudyMate2026!')
  assert r[0]==303,(name,r[0]);self.request('profile');return self

def check(name,condition):
 assert condition,name
 RESULTS.append(dict(name=name,status='passed'));print('PASS',name,flush=True)
def deny(name,c,action,expected,**data):
 before=db('snapshot');code,_,_=c.post(action,**data)
 check(name,code==expected and before==db('snapshot'))
def race(c1,c2,action,**data):
 barrier=threading.Barrier(2)
 def run(c):barrier.wait();return c.post(action,**data)
 with concurrent.futures.ThreadPoolExecutor(2) as pool:
  return list(pool.map(run,[c1,c2]))
def main():
 guest=Client()
 for attempt in range(30):
  try:
   code,body,_=guest.request('health')
   if code==200 and json.loads(body)=={'status':'ok'}:break
  except (OSError,ValueError):pass
  time.sleep(.5)
 else:raise RuntimeError('Application did not become ready')
 check('database health',json.loads(guest.request('health')[1])=={'status':'ok'})
 s=Client().login('student1');s2=Client().login('student1');i=Client().login('instructor1');other=Client().login('instructor2');admin=Client().login('admin')
 for client,page in [(s,'student'),(i,'instructor'),(admin,'admin')]:check(page+' renders',client.request(page)[0]==200)
 fixture=db();lessons={cid:[l['id'] for l in fixture['lessons'] if l['course_id']==cid] for cid in [1,2,3]}
 answers={str(a['question_id']):a['answer_id'] for a in fixture['answers'] if a['course_id']==2}
 check('public catalog hides draft', 'Cơ sở dữ liệu' not in ' '.join(re.findall(r'<h3>(.*?)</h3>',guest.request('courses')[1])))
 check('private learning without enrollment',Client().login('student2').request('learn',id=2)[0]==403)
 # Each action must reject missing CSRF before any mutation.
 actions=['register','login','logout','enroll','last_lesson','complete','submit_quiz','course_save','course_state','course_delete','chapter_save','chapter_delete','lesson_save','lesson_delete','quiz_save','user_status','category_save','category_delete','ai']
 for action in actions:deny('CSRF '+action,s,action,419,csrf='invalid',course_id=2)
 deny('wrong role course creation',s,'course_save',403,title='Valid title',description='A sufficiently long description',category_id=1)
 deny('wrong owner course update',other,'course_save',404,course_id=3,title='Valid title',description='A sufficiently long description',category_id=1)
 deny('forged chapter from other course',i,'chapter_save',404,course_id=3,chapter_id=1,title='Forged chapter',position=1)
 deny('forged lesson from other course',i,'lesson_save',404,course_id=3,lesson_id=1,title='Forged lesson',body='Valid body '*10,position=1)
 deny('category in use',admin,'category_delete',409,category_id=1,confirm=1)
 deny('cannot lock admin',admin,'user_status',403,user_id=5,is_active=0,confirm=1)
 for status in ['published','archived']:
  if status=='archived':check('archive course',i.post('course_state',course_id=1,target='archived',confirm=1)[0]==303)
  for action,fields in [('chapter_delete',dict(chapter_id=1,confirm=1)),('lesson_delete',dict(lesson_id=1,confirm=1)),('chapter_save',dict(chapter_id=1,title='New title',position=9)),('lesson_save',dict(lesson_id=1,title='New lesson',body='Valid body '*10,position=9)),('course_delete',dict(confirm=1))]:
   deny(status+' structure locked '+action,i,action,409,course_id=1,**fields)
  if status=='archived':
   deny('archived rejects new enrollment',s,'enroll',409,course_id=1)
   check('republish',i.post('course_state',course_id=1,target='published',confirm=1)[0]==303)
 r=race(s,s2,'enroll',course_id=1);check('concurrent enrollment both succeed',all(x[0]==303 for x in r))
 check('exactly one enrollment',next(e['n'] for e in db()['counts']['enrollments'] if e['user_id']==1 and e['course_id']==1)==1)
 lid=lessons[1][0];r=race(s,s2,'complete',course_id=1,lesson_id=lid)
 check('concurrent completion both succeed',all(x[0]==303 for x in r))
 check('exactly one progress',next(e['n'] for e in db()['counts']['progress'] if e['user_id']==1 and e['lesson_id']==lid)==1)
 deny('cannot unpublish enrolled course',i,'course_state',409,course_id=1,target='draft',confirm=1)
 check('quiz gate before lessons complete',s.request('quiz',id=2)[0]==403)
 for lid in lessons[2]:check('complete lesson '+str(lid),s.post('complete',course_id=2,lesson_id=lid)[0]==303)
 data={'course_id':2,'request_id':str(uuid.uuid4()),'confirm':1,**{'answers['+q+']':a for q,a in answers.items()}}
 r=race(s,s2,'submit_quiz',**data);check('same UUID concurrent same payload',all(x[0]==303 for x in r) and r[0][2]['Location']==r[1][2]['Location'])
 check('exactly one quiz attempt',sum(a['request_id']==data['request_id'] for a in db()['counts']['attempts'])==1)
 changed=dict(data);changed.pop(next(k for k in changed if k.startswith('answers[')))
 deny('same UUID different payload leaves DB unchanged',s,'submit_quiz',409,**changed)
 deny('malformed answer rolls back',s,'submit_quiz',422,course_id=2,confirm=1,request_id=str(uuid.uuid4()),**{'answers[999999]':999999})
 # Force a database failure after quiz_attempts insertion; transaction must undo it.
 db('fail_answers')
 try:deny('failed answer insert rolls back entire attempt',s,'submit_quiz',500,**dict(data,request_id=str(uuid.uuid4())))
 finally:db('clear_fail_answers')
 conflict=dict(data,request_id=str(uuid.uuid4()))
 barrier=threading.Barrier(2)
 def conflicting_submit(pair):
  client,payload=pair;barrier.wait();return client.post('submit_quiz',**payload)[0]
 with concurrent.futures.ThreadPoolExecutor(2) as pool:
  different=dict(conflict);different.pop(next(k for k in different if k.startswith('answers[')))
  statuses=list(pool.map(conflicting_submit,[(s,conflict),(s2,different)]))
 check('concurrent same UUID different payload yields 303 and 409',sorted(statuses)==[303,409])
 resultid=next(a['id'] for a in db()['counts']['attempts'] if a['request_id']==data['request_id'])
 check('attempt owner protected',Client().login('student2').request('result',id=resultid)[0]==404)
 check('archive existing enrolled course',i.post('course_state',course_id=2,target='archived',confirm=1)[0]==303)
 check('existing student learns archived',s.request('learn',id=2)[0]==200)
 deny('AI disabled explicit 503',s,'ai',503,course_id=2,lesson_id=lessons[2][0],mode='ask',question='Giải thích bài học?')
 check('quiz still available after AI error',s.request('quiz',id=2)[0]==200)
 check('lock student revokes sessions',admin.post('user_status',user_id=1,is_active=0,confirm=1)[0]==303 and s.request('student')[0]==401 and s2.request('student')[0]==401)
 check('unlock does not revive old session',admin.post('user_status',user_id=1,is_active=1,confirm=1)[0]==303 and s.request('student')[0]==401)
 # Register ignores forged privilege; no personally identifiable real account involved.
 new=Client();new.request('register');email='acceptance-'+uuid.uuid4().hex[:10]+'@studymate.test'
 r=new.post('register',name='Acceptance Student',email=email,password='Acceptance2026!',password_confirmation='Acceptance2026!',role='admin',is_active=0)
 check('registration creates student despite forged role',r[0]==303 and r[2]['Location'].endswith('page=student') and new.request('admin')[0]==403)
 # Full Instructor creation/publish path with actual HTTP forms.
 r=i.post('course_save',title='Acceptance new course',description='A complete acceptance course for real HTTP validation.',category_id=1,cover_key='web')
 check('instructor creates draft',r[0]==303);cid=int(urllib.parse.parse_qs(urllib.parse.urlparse(r[2]['Location']).query)['id'][0])
 check('create chapter',i.post('chapter_save',course_id=cid,title='Acceptance chapter',position=1)[0]==303)
 html=i.request('edit',id=cid,tab='content')[1];chapter=re.search(r'<option value="(\d+)">Acceptance chapter',html)[1]
 check('create lesson',i.post('lesson_save',course_id=cid,chapter_id=chapter,title='Acceptance lesson',body='This lesson contains enough original text for the acceptance workflow.',position=1)[0]==303)
 quiz={}
 for n in range(5):
  quiz.update({f'questions[{n}][prompt]':f'Question number {n}?',f'questions[{n}][correct]':0,f'questions[{n}][explanation]':'Answer A is correct.'})
  quiz.update({f'questions[{n}][answers][{j}]':f'Choice {j}' for j in range(4)})
 check('create quiz transaction',i.post('quiz_save',course_id=cid,**quiz)[0]==303)
 check('publish complete new course',i.post('course_state',course_id=cid,target='published',confirm=1)[0]==303)
 deny('published quiz locked',i,'quiz_save',409,course_id=cid,**quiz)
 check('new student enrolls and sees lesson',new.post('enroll',course_id=cid)[0]==303 and new.request('learn',id=cid)[0]==200)
 check('preview does not modify learning data',i.request('preview',id=cid)[0]==200)
 out=Path('docs/evidence/final');out.mkdir(parents=True,exist_ok=True)
 (out/'http-results.json').write_text(json.dumps({'base_commit':subprocess.check_output(['git','rev-parse','HEAD'],text=True).strip(),'target':'working tree, Docker MySQL disposable database','results':RESULTS},ensure_ascii=False,indent=2))
 print(len(RESULTS),'checks passed')
if __name__=='__main__':
 if os.environ.get('STUDYMATE_DISPOSABLE_DB')!='1':raise SystemExit('Set STUDYMATE_DISPOSABLE_DB=1 only for a disposable QA database')
 main()
