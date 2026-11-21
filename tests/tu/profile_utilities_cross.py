from pathlib import Path
import argparse,importlib.util,json,os,shutil,secrets,subprocess,tempfile,time
parser=argparse.ArgumentParser(description="Check profile/utility overlap on disposable SQLite/MySQL apps. MySQL datadir must be a dedicated QA instance, stopped before this runner.")
parser.add_argument('--source',type=Path,default=Path(__file__).resolve().parents[2])
parser.add_argument('--baseline',type=Path,required=True,help="Profile + main before PR26 checkout")
parser.add_argument('--artifacts',type=Path,required=True)
parser.add_argument('--runtime',type=Path,required=True,help="Native QA PHP/MySQL/Chromium runtime directory")
parser.add_argument('--mysql-datadir',type=Path,required=True,help="Existing dedicated QA datadir; runner does not initialize it")
args=parser.parse_args();ROOT=args.source.resolve();BASE=args.baseline.resolve();OUT=args.artifacts.resolve();RUNTIME=args.runtime.resolve();MYSQL_DATA=args.mysql_datadir.resolve()
spec=importlib.util.spec_from_file_location('qa',ROOT/'tests/tu/profile_media_qa.py');q=importlib.util.module_from_spec(spec);spec.loader.exec_module(q)
PHP=os.environ.get('QA_PHP',str(RUNTIME/'bin/php-media'));q.CHROME=os.environ.get('QA_CHROMIUM',str(RUNTIME/'headless/usr/lib/chromium/chromium-headless-shell'));ENV=q.ENV;cmd=q.cmd;OUT.mkdir(parents=True,exist_ok=True)
with tempfile.TemporaryDirectory(prefix='tu-cross-') as d:
 temp=Path(d);mp=q.port();dbs=[];user='tu_cross_'+secrets.token_hex(4);password=secrets.token_hex(16)
 connect=f"$p=new PDO('mysql:host=127.0.0.1;port={mp}','root','',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);"
 with open(OUT/'mysql-server.txt','w') as log:
  mysql=subprocess.Popen([str(RUNTIME/'mysql/usr/sbin/mysqld'),'--no-defaults','--basedir='+str(RUNTIME/'mysql/usr'),'--datadir='+str(MYSQL_DATA),'--bind-address=127.0.0.1','--port='+str(mp),'--socket=','--mysqlx=0','--secure-file-priv=NULL','--skip-log-bin','--innodb-buffer-pool-size=64M','--pid-file='+str(temp/'mysql.pid')],env=dict(ENV,LD_LIBRARY_PATH=str(RUNTIME/'mysql/usr/lib/x86_64-linux-gnu')),stdout=log,stderr=log)
  try:
   q.ready(mysql,mp);cmd([PHP,'-r',connect+f"$p->exec(\"CREATE USER '{user}'@'%' IDENTIFIED BY '{password}'\");"])
   for driver in ['sqlite','mysql']:
    servers=[];handles=[];apps={};urls={}
    try:
     for label,source in [('combined',ROOT),('baseline',BASE)]:
      app=temp/(driver+'-'+label);apps[label]=app
      for folder in ['app','database','public','tools','tests']:shutil.copytree(source/folder,app/folder,ignore=shutil.ignore_patterns('__pycache__'))
      shutil.copy2(source/'config.example.php',app/'config.example.php');(app/'storage').mkdir(mode=0o700)
      name='studymate_cross_'+secrets.token_hex(5)+'_test'
      if driver=='mysql':
       dbs.append(name);cmd([PHP,'-r',connect+f"$p->exec('CREATE DATABASE `{name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');$p->exec(\"GRANT ALL PRIVILEGES ON `{name}`.* TO '{user}'@'%'\");"])
      config="<?php $c=require __DIR__.'/config.example.php';"+f"$c['db_driver']='{driver}';$c['db_name']='{name}';$c['db_host']='127.0.0.1';$c['db_port']={mp};$c['db_user']='{user}';$c['db_password']='{password}';"+"$c['sqlite_path']=__DIR__.'/storage/cross_test.sqlite';$c['ai_enabled']=false;$c['gemini_api_key']='';$c['app_env']='qa';return $c;"
      (app/'config.php').write_text(config)
      folder=OUT/driver/label;folder.mkdir(parents=True,exist_ok=True)
      (folder/'setup.txt').write_text(cmd([PHP,'tools/setup.php'],app))
      (folder/'core.txt').write_text(cmd([PHP,'tests/run.php'],app))
      (folder/'courses.txt').write_text(cmd([PHP,'tools/seed_demo_courses.php'],app))
      bp=q.port();h=open(folder/'server.txt','w');handles.append(h);s=subprocess.Popen([PHP,'-d','display_errors=0','-S',f'127.0.0.1:{bp}','-t','public'],cwd=app,env=ENV,stdout=h,stderr=h);servers.append(s);q.ready(s,bp,True);urls[label]=f'http://127.0.0.1:{bp}'
     for suite in ['learning-utilities-integration.cjs','cross']:
      folder=OUT/driver/suite.replace('.cjs','');folder.mkdir(exist_ok=True)
      env=dict(ENV,QA_PHP=PHP,QA_CHROMIUM=q.CHROME,QA_SOURCE=str(ROOT),QA_BASE_URL=urls['combined'],QA_BASELINE_URL=urls['baseline'],QA_OUTPUT=str(folder),QA_ALLOW_WRITES='1',QA_BROWSERS=json.dumps([{'name':'chrome','path':q.CHROME}]),QA_DEMO_PASSWORD=json.loads(cmd([PHP,'-r',"require 'app/bootstrap.php';echo json_encode($config['demo_password']);"],apps['combined'])))
      script=ROOT/'tests'/suite if suite!='cross' else Path(__file__).with_name('profile_utilities_cross.cjs')
      result=cmd([q.NODE,script],apps['combined'],env);(folder/'stdout.txt').write_text(result);print(driver,suite,result.strip(),flush=True)
    finally:
     for s in servers:s.terminate();s.wait(timeout=15)
     for h in handles:h.close()
  finally:
   for name in dbs:cmd([PHP,'-r',connect+f"$p->exec('DROP DATABASE IF EXISTS `{name}`');"])
   cmd([PHP,'-r',connect+f"$p->exec(\"DROP USER IF EXISTS '{user}'@'%'\");"]);mysql.terminate();mysql.wait(timeout=25)
