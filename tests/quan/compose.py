#!/usr/bin/env python3
"""Smoke-test official Compose in a disposable source copy and DB volumes."""
import os,subprocess,tempfile,secrets,shutil,socket,time,json,urllib.request,http.cookiejar,urllib.parse,re
from pathlib import Path
root=Path(__file__).resolve().parents[2]
with tempfile.TemporaryDirectory(prefix='studymate-compose-') as temp:
 work=Path(temp); app=work/'app';shutil.copytree(root,app,ignore=lambda path,names: [n for n in names if n=='__pycache__' or (Path(path)==root and n in ['.git','config.php','.env','storage'])]);(app/'storage').mkdir()
 sock=socket.socket();sock.bind(('127.0.0.1',0));port=sock.getsockname()[1];sock.close()
 envfile=work/'compose.env';envfile.write_text('DB_PASSWORD='+secrets.token_hex(24)+'\nMYSQL_ROOT_PASSWORD='+secrets.token_hex(24)+'\nWEB_PORT='+str(port)+'\nAI_ENABLED=0\nGEMINI_MODEL=gemini-2.5-flash-lite\n');envfile.chmod(0o600)
 # Preserve Docker Desktop's selected context and Compose plugin discovery.
 # Restricted environments can still supply their own writable DOCKER_CONFIG.
 env=os.environ.copy()
 project='studymate-quan-compose-'+secrets.token_hex(3)
 prefix=['docker','compose','--env-file',str(envfile),'-f','compose.yaml','-f','compose.qa.yaml','-p',project]
 def compose(*args):
  r=subprocess.run(prefix+list(args),cwd=app,env=env,text=True,stdout=subprocess.PIPE,stderr=subprocess.STDOUT)
  if r.returncode: print(r.stdout);raise RuntimeError('Compose operation failed: '+args[0])
  return r.stdout
 try:
  compose('up','--build','-d','--wait','--wait-timeout','180')
  print('PASS: repository Dockerfile/Compose clean build and MySQL healthy',flush=True)
  mapped=compose('port','app','80').strip();print('HTTP binding: '+mapped,flush=True);base='http://'+mapped+'/index.php'
  for i in range(60):
   try:
    assert json.load(urllib.request.urlopen(base+'?page=health'))=={'status':'ok'};break
   except (OSError,AssertionError) as error:
    if i==59:print('HTTP health error: '+str(error),flush=True);print(compose('logs','--no-color','app'),flush=True)
    time.sleep(.5)
  else:raise RuntimeError('Apache health failed')
  for who,role in [('student1','student'),('instructor1','instructor'),('admin','admin')]:
   op=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
   page=op.open(base+'?page=login').read().decode();token=re.search(r'name="csrf" value="([^"]+)"',page).group(1)
   response=op.open(base,urllib.parse.urlencode(dict(action='login',csrf=token,email=who+'@studymate.test',password='StudyMate2026!')).encode())
   assert response.status==200 and 'page='+role in response.url
   print('PASS: Apache/MySQL login '+role,flush=True)
  result=compose('exec','-T','app','php','tests/run.php');assert '14 checks passed. Driver: mysql' in result
  print('PASS: 14 service checks on official Apache/MySQL Compose',flush=True)
  for file,expected in [('tests/an-pagination.php','8 pagination checks passed'),('tests/search_utf8.php','30 admin UTF-8 search checks passed.'),('tests/model_config.php','8 model configuration checks passed.'),('tests/ai_contract.php','24 offline AI contract checks passed.')]:
   result=compose('exec','-T','-e','STUDYMATE_DISPOSABLE_DB=1','app','php',file)
   print(result,flush=True)
   assert expected in result
  result=compose('exec','-T','app','php','-r','$c=require "config.php";echo $c["ai_model"];');assert result.strip()=='gemini-2.5-flash-lite'
  print('PASS: Compose GEMINI_MODEL binding',flush=True)
  snapshot_code='require "app/bootstrap.php";$out=[];foreach(["users","categories","courses","chapters","lessons","quizzes","questions","answers","enrollments","lesson_progress","quiz_attempts","quiz_answers"] as $t)$out[$t]=hash("sha256",json_encode(all("SELECT * FROM ".$t." ORDER BY id")));echo json_encode($out);'
  before=json.loads(compose('exec','-T','app','php','-r',snapshot_code))
  compose('restart')
  for i in range(80):
   try:
    assert json.load(urllib.request.urlopen(base+'?page=health'))=={'status':'ok'};break
   except (OSError,AssertionError):time.sleep(.5)
  else:
   print(compose('ps','--all'),flush=True)
   print(compose('logs','--no-color','--tail','40','app'),flush=True)
   raise RuntimeError('Compose restart health failed')
  result=compose('exec','-T','app','php','-r','require "app/bootstrap.php";echo val("SELECT COUNT(*) FROM quiz_attempts");');assert int(result.strip())>=2
  print('PASS: Compose restart retains quiz attempts and database health',flush=True)
  assert json.loads(compose('exec','-T','app','php','-r',snapshot_code))==before
  print('PASS: hashes of all 12 tables preserved after restart',flush=True)
  attempts=int(result.strip())
  # Deterministically exercise app startup while the database is unavailable.
  compose('stop','db')
  compose('restart','app')
  time.sleep(2)
  compose('start','db')
  for i in range(100):
   try:
    assert json.load(urllib.request.urlopen(base+'?page=health',timeout=3))=={'status':'ok'};break
   except (OSError,AssertionError):time.sleep(.5)
  else:raise RuntimeError('Delayed database startup health failed')
  result=compose('exec','-T','app','php','-r','require "app/bootstrap.php";echo val("SELECT COUNT(*) FROM quiz_attempts");');assert int(result.strip())==attempts
  assert 'Đang chờ MySQL' in compose('logs','--no-color','app')
  assert json.loads(compose('exec','-T','app','php','-r',snapshot_code))==before
  print('PASS: app waits for delayed MySQL startup without losing quiz attempts',flush=True)
  print('PHP: '+compose('exec','-T','app','php','-r','echo PHP_VERSION;').strip())
  print('RESULT: official Compose validation passed; disposable project/volumes removed; live Gemini not tested.',flush=True)
 except Exception:
  diagnostic=subprocess.run(prefix+['logs','--no-color','--tail','30','app'],cwd=app,env=env,text=True,stdout=subprocess.PIPE,stderr=subprocess.STDOUT)
  print(diagnostic.stdout,flush=True)
  raise
 finally:
  compose('down','--volumes','--remove-orphans')
