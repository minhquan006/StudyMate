"""Profile/media acceptance against independent temporary SQLite/MySQL apps."""
from pathlib import Path
import hashlib
import json
import os
import secrets
import shutil
import socket
import subprocess
import tempfile
import time
import urllib.request

ROOT=Path(__file__).resolve().parents[2]
WORKSPACE=ROOT.parent
PHP=os.environ.get('QA_PHP',str(WORKSPACE/'runtime/bin/php-media'))
NODE=os.environ.get('CODEX_PRIMARY_RUNTIME_NODE','node')
CHROME=os.environ.get('QA_CHROMIUM',str(WORKSPACE/'runtime/headless/usr/lib/chromium/chromium-headless-shell'))
OUT=ROOT/'docs/evidence/tu-profile-media-2026-11-21'
ENV=dict(os.environ,NODE_PATH=os.environ.get('CODEX_PRIMARY_RUNTIME_NODE_MODULES',''),STUDYMATE_DISPOSABLE_DB='1')
BASE='7f374926acdf3b2c6a1fa9e08b204b39e7777299'


def cmd(args,cwd=ROOT,env=ENV):
    r=subprocess.run(list(map(str,args)),cwd=cwd,env=env,capture_output=True,text=True)
    if r.returncode:raise RuntimeError(r.stdout[-3000:]+r.stderr[-3500:])
    return r.stdout


def port():
    with socket.socket() as s:s.bind(('127.0.0.1',0));return s.getsockname()[1]


def ready(server,p,http=False):
    for _ in range(100):
        if server.poll() is not None:raise RuntimeError('QA server exited')
        try:
            if http:urllib.request.urlopen(f'http://127.0.0.1:{p}/index.php?page=health').close()
            else:socket.create_connection(('127.0.0.1',p),timeout=.2).close()
            return
        except OSError:time.sleep(.1)
    raise RuntimeError('QA server timeout')


def data(app):
    return json.loads(cmd([PHP,'-r',"require 'app/bootstrap.php';$s=[];foreach(['users','categories','courses','chapters','lessons','enrollments','lesson_progress','quizzes','questions','answers','quiz_attempts','quiz_answers'] as $t)$s[$t]=all('SELECT * FROM '.$t.' ORDER BY id');echo json_encode($s);"],app))


def main():
    OUT.mkdir(parents=True,exist_ok=True)
    files=[p for p in ROOT.rglob('*.php') if not any(k in p.parts for k in ['docs','storage'])]
    for file in files:cmd([PHP,'-l',file])
    report={'base':BASE,'php':cmd([PHP,'-r','echo PHP_VERSION;']),'lint_files':len(files),'results':{},'limits':['Native PHP/MySQL, not Docker/Compose runtime.','Chromium emulated viewports, not Edge/Safari or physical phones.','AI disabled; no live Gemini.','No PR21 widgets imported.']}
    with tempfile.TemporaryDirectory(prefix='tu-profile-media-') as directory:
        temp=Path(directory);mysql_port=port();databases=[]
        mysql_user='tu_profile_'+secrets.token_hex(4);mysql_password=secrets.token_hex(16)
        root_connect=f"$p=new PDO('mysql:host=127.0.0.1;port={mysql_port}','root','',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);"
        log=open(OUT/'mysql-server.txt','w')
        mysql=subprocess.Popen([str(WORKSPACE/'runtime/mysql/usr/sbin/mysqld'),'--no-defaults','--basedir='+str(WORKSPACE/'runtime/mysql/usr'),'--datadir='+str(WORKSPACE/'demo-mysql-data'),'--bind-address=127.0.0.1','--port='+str(mysql_port),'--socket=','--mysqlx=0','--secure-file-priv=NULL','--skip-log-bin','--innodb-buffer-pool-size=64M','--pid-file='+str(temp/'mysql.pid')],env=dict(ENV,LD_LIBRARY_PATH=str(WORKSPACE/'runtime/mysql/usr/lib/x86_64-linux-gnu')),stdout=log,stderr=subprocess.STDOUT)
        try:
            ready(mysql,mysql_port)
            cmd([PHP,'-r',root_connect+f"$p->exec(\"CREATE USER '{mysql_user}'@'%' IDENTIFIED BY '{mysql_password}'\");"])
            for driver in ['sqlite','mysql']:
                app=temp/driver
                for folder in ['app','database','public','tools','tests']:shutil.copytree(ROOT/folder,app/folder,ignore=shutil.ignore_patterns('__pycache__'))
                shutil.copy2(ROOT/'config.example.php',app/'config.example.php');(app/'storage').mkdir(mode=0o700)
                database='studymate_profile_'+str(int(time.time()))+'_test'
                if driver=='mysql':
                    databases.append(database)
                    cmd([PHP,'-r',root_connect+f"$p->exec('CREATE DATABASE `{database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');$p->exec(\"GRANT ALL PRIVILEGES ON `{database}`.* TO '{mysql_user}'@'%'\");"])
                config="<?php $c=require __DIR__.'/config.example.php';"
                config+=f"$c['db_driver']='{driver}';$c['db_name']='{database}';$c['db_host']='127.0.0.1';$c['db_port']={mysql_port};$c['db_user']='{mysql_user}';$c['db_password']='{mysql_password}';"
                config+="$c['sqlite_path']=__DIR__.'/storage/profile_test.sqlite';$c['ai_enabled']=false;$c['gemini_api_key']='';$c['app_env']='qa';return $c;\n"
                (app/'config.php').write_text(config)
                fresh=temp/(driver+'-fresh');shutil.copytree(app,fresh)
                fresh_database=database+'_fresh'
                if driver=='mysql':
                    databases.append(fresh_database)
                    cmd([PHP,'-r',root_connect+f"$p->exec('CREATE DATABASE `{fresh_database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');$p->exec(\"GRANT ALL PRIVILEGES ON `{fresh_database}`.* TO '{mysql_user}'@'%'\");"])
                (fresh/'config.php').write_text(config.replace(database,fresh_database))
                cmd([PHP,'tools/setup.php'],fresh)
                (OUT/(driver+'-fresh-core.txt')).write_text(cmd([PHP,'tests/run.php'],fresh))
                assert '0 columns added' in cmd([PHP,'tools/migrate_profile_media.php'],fresh)
                # Create a real pre-migration database, including learning/quiz history.
                old_schema=cmd(['git','show',BASE+':database/schema.php'])
                (app/'database/schema.php').write_text(old_schema)
                setup=(app/'tools/setup.php').read_text()
                setup=setup.replace("require_once dirname(__DIR__).'/database/migrate_profile_media.php';migrateProfileMedia();\n",'')
                (app/'tools/setup.php').write_text(setup)
                cmd([PHP,'tools/setup.php'],app)
                core=cmd([PHP,'tests/run.php'],app);(OUT/(driver+'-core.txt')).write_text(core)
                before=data(app)
                shutil.copy2(ROOT/'tools/setup.php',app/'tools/setup.php');shutil.copy2(ROOT/'database/schema.php',app/'database/schema.php')
                first=cmd([PHP,'tools/migrate_profile_media.php'],app)
                assert '6 columns added' in first
                after=data(app)
                assert all([{k:row[k] for k in before[table][i]} for i,row in enumerate(rows)]==before[table] for table,rows in after.items())
                second=cmd([PHP,'tools/migrate_profile_media.php'],app);assert '0 columns added' in second and data(app)==after
                cmd([PHP,'tools/setup.php'],app);assert data(app)==after
                (OUT/(driver+'-migration.json')).write_text(json.dumps({'first':first.strip(),'second':second.strip(),'setup_repeat':'unchanged','old_tables_preserved':list(before)},indent=2)+'\n')
                if driver=='mysql':
                    environment=cmd([PHP,'-r',"require 'app/bootstrap.php';echo json_encode(['version'=>val('SELECT VERSION()')]);"],app)
                    (OUT/'mysql-version.json').write_text(environment+'\n')
                out=OUT/driver;out.mkdir(exist_ok=True)
                web_port=port()
                with open(out/'server.txt','w') as web_log:
                    server=subprocess.Popen([PHP,'-d','display_errors=0','-d','upload_max_filesize=3M','-d','post_max_size=4M','-S',f'127.0.0.1:{web_port}','-t','public'],cwd=app,env=ENV,stdout=web_log,stderr=web_log)
                    try:
                        ready(server,web_port,True)
                        env=dict(ENV,QA_PHP=PHP,QA_CHROMIUM=CHROME,QA_BASE_URL=f'http://127.0.0.1:{web_port}/index.php',QA_OUTPUT=str(out),QA_ALLOW_WRITES='1',QA_DEMO_PASSWORD=json.loads(cmd([PHP,'-r',"require 'app/bootstrap.php';echo json_encode($config['demo_password']);"],app)))
                        output=cmd([NODE,ROOT/'tests/tu/profile_media_browser.cjs'],app,env)
                        (out/'stdout.txt').write_text(output)
                        report['results'][driver]=json.loads(output.strip().splitlines()[-1])
                        print(driver,output.strip(),flush=True)
                    finally:server.terminate();server.wait(timeout=15)
        finally:
            for database in databases:cmd([PHP,'-r',root_connect+f"$p->exec('DROP DATABASE IF EXISTS `{database}`');"])
            cmd([PHP,'-r',root_connect+f"$p->exec(\"DROP USER IF EXISTS '{mysql_user}'@'%'\");"])
            mysql.terminate();mysql.wait(timeout=25);log.close()
            (OUT/'summary.json').write_text(json.dumps(report,indent=2)+'\n')


if __name__=='__main__':main()
