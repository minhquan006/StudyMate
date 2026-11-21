"""Reproduce PR19's 1419 and verify its fix with a database-scoped app user.

All app copies, credentials and databases are disposable. The managed runtime
paths can be changed with QA_PHP, QA_MYSQLD and QA_MYSQL_LIB. No Docker adapter
is used; this verifies the documented native PHP commands.
"""
from pathlib import Path
import concurrent.futures
import hashlib
import json
import os
import secrets
import shutil
import socket
import subprocess
import tempfile
import time

ROOT = Path(__file__).resolve().parents[2]
WORKSPACE = ROOT.parent
OUT = ROOT/'docs/evidence/tu-pr19-mysql-fixture-2026-11-21'
PHP = os.environ.get('QA_PHP', str(WORKSPACE/'runtime/bin/php'))
MYSQLD = os.environ.get('QA_MYSQLD', str(WORKSPACE/'runtime/mysql/usr/sbin/mysqld'))
ENV = dict(os.environ, STUDYMATE_DISPOSABLE_DB='1')


def cmd(args, cwd=ROOT, allow_failure=False):
    result = subprocess.run(list(map(str,args)), cwd=cwd, env=ENV, capture_output=True, text=True)
    if result.returncode and not allow_failure:
        raise RuntimeError(result.stdout[-1000:]+result.stderr[-2500:])
    return result


def php(code, cwd=ROOT):
    return cmd([PHP,'-r',code],cwd).stdout


def php_app(code, cwd):
    return php("require 'app/bootstrap.php';"+code,cwd)


def snapshot(app):
    return php_app("$s=[];foreach(['users','categories','courses','chapters','lessons','enrollments','lesson_progress','quizzes','questions','answers','quiz_attempts','quiz_answers'] as $t)$s[$t]=all('SELECT * FROM '.$t.' ORDER BY id');echo hash('sha256',json_encode($s));",app)


def copy_app(target, driver, database, user, password, port):
    for folder in ['app','database','tools','tests']:
        shutil.copytree(ROOT/folder,target/folder,ignore=shutil.ignore_patterns('__pycache__'))
    shutil.copy2(ROOT/'config.example.php',target/'config.example.php')
    (target/'storage').mkdir(mode=0o700)
    config="<?php $c=require __DIR__.'/config.example.php';"
    config+=f"$c['db_driver']='{driver}';$c['db_name']='{database}';$c['db_user']='{user}';$c['db_password']='{password}';$c['db_host']='127.0.0.1';$c['db_port']={port};"
    config+="$c['sqlite_path']=__DIR__.'/storage/demo_test.sqlite';$c['ai_enabled']=false;$c['gemini_api_key']='';return $c;\n"
    (target/'config.php').write_text(config)


def sequence(app, label, test='tests/demo_courses.php', expect_failure=False):
    for file in ['tools/setup.php','tests/run.php']:
        result=cmd([PHP,file],app)
        (OUT/(label+'-'+Path(file).stem+'.txt')).write_text(result.stdout+result.stderr)
    before=snapshot(app)
    result=cmd([PHP,test],app,allow_failure=expect_failure)
    (OUT/(label+'-demo_courses.txt')).write_text(result.stdout+result.stderr)
    if expect_failure:
        assert result.returncode!=0 and '1419' in result.stdout+result.stderr
        assert snapshot(app)==before
        print(label,'reproduced 1419; all 12 tables unchanged',flush=True)
        return {'exit':result.returncode,'error':1419,'snapshot_unchanged':True}
    assert result.returncode==0 and '358 demo course checks passed.' in result.stdout
    after=snapshot(app)
    with concurrent.futures.ThreadPoolExecutor(max_workers=2) as pool:
        repeat=list(pool.map(lambda _:cmd([PHP,'tools/seed_demo_courses.php'],app),range(2)))
    assert all(r.stdout.count('Đã có, giữ nguyên')==3 for r in repeat)
    assert snapshot(app)==after
    for i,result in enumerate(repeat):(OUT/f'{label}-repeat-{i}.txt').write_text(result.stdout+result.stderr)
    counts=json.loads(php_app("echo json_encode(['triggers'=>val(\"SELECT COUNT(*) FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA=DATABASE() AND TRIGGER_NAME='demo_content_failure'\"),'checks'=>val(\"SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND CONSTRAINT_NAME='demo_content_failure'\")]);",app)) if label=='mysql-fixed' else {'triggers':0,'checks':0}
    assert int(counts['triggers'])==int(counts['checks'])==0
    print(label,'14 service + 358 content checks passed; two concurrent repeats preserve 12 tables',flush=True)
    return {'service_checks':14,'demo_checks':358,'parallel_repeats':2,'snapshot_unchanged':True,'fixture_objects_after':counts}


def main():
    OUT.mkdir(parents=True,exist_ok=True)
    with tempfile.TemporaryDirectory(prefix='tu-pr19-trigger-review-') as directory:
        temp=Path(directory)
        with socket.socket() as sock:sock.bind(('127.0.0.1',0));port=sock.getsockname()[1]
        mysql_env=dict(ENV,LD_LIBRARY_PATH=os.environ.get('QA_MYSQL_LIB',str(WORKSPACE/'runtime/mysql/usr/lib/x86_64-linux-gnu')))
        log=open(OUT/'mysql-server.txt','w')
        server=subprocess.Popen([MYSQLD,'--no-defaults','--basedir='+str(WORKSPACE/'runtime/mysql/usr'),'--datadir='+str(WORKSPACE/'demo-mysql-data'),'--bind-address=127.0.0.1','--port='+str(port),'--socket=','--mysqlx=0','--secure-file-priv=NULL','--log-bin='+str(temp/'qa-bin'),'--server-id=19','--log-bin-trust-function-creators=0','--innodb-buffer-pool-size=64M','--pid-file='+str(temp/'mysql.pid')],env=mysql_env,stdout=log,stderr=subprocess.STDOUT)
        databases=[]
        user='tu_pr19_'+secrets.token_hex(4)
        password=secrets.token_hex(16)
        root_connect=f"$p=new PDO('mysql:host=127.0.0.1;port={port};charset=utf8mb4','root','',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);"
        summary={'base':cmd(['git','rev-parse','HEAD']).stdout.strip(),'results':{},'scope':'native PHP, isolated MySQL; no Docker/Edge/Gemini'}
        try:
            for _ in range(100):
                if server.poll() is not None:raise RuntimeError((OUT/'mysql-server.txt').read_text()[-1500:])
                try:socket.create_connection(('127.0.0.1',port),timeout=.2).close();break
                except OSError:time.sleep(.1)
            else:raise RuntimeError('MySQL did not start')
            php(root_connect+f"$p->exec(\"CREATE USER '{user}'@'%' IDENTIFIED BY '{password}'\");")
            for label in ['baseline','fixed']:
                database=f'studymate_tu_pr19_{label}_'+secrets.token_hex(3)+'_test'
                databases.append(database)
                php(root_connect+f"$p->exec('CREATE DATABASE `{database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');$p->exec(\"GRANT ALL PRIVILEGES ON `{database}`.* TO '{user}'@'%'\");")
                app=temp/label
                copy_app(app,'mysql',database,user,password,port)
                environment=json.loads(php_app("echo json_encode(['current_user'=>val('SELECT CURRENT_USER()'),'mysql'=>val('SELECT VERSION()'),'log_bin'=>val('SELECT @@log_bin'),'trust'=>val('SELECT @@log_bin_trust_function_creators'),'grants'=>all('SHOW GRANTS')]);",app))
                assert int(environment['log_bin'])==1 and int(environment['trust'])==0
                assert environment['current_user']==user+'@%'
                assert not any('SUPER' in str(row) or 'GRANT ALL PRIVILEGES ON *.*' in str(row) for row in environment['grants'])
                (OUT/(label+'-mysql-environment.json')).write_text(json.dumps(environment,indent=2)+'\n')
                if label=='baseline':
                    old=cmd(['git','show','HEAD:tests/demo_courses.php']).stdout
                    (app/'tests/demo_courses_old.php').write_text(old)
                    summary['results'][label]=sequence(app,'mysql-baseline','tests/demo_courses_old.php',True)
                else:
                    summary['results'][label]=sequence(app,'mysql-fixed')
            sqlite=temp/'sqlite'
            copy_app(sqlite,'sqlite','studymate_sqlite_test','','',0)
            summary['results']['sqlite']=sequence(sqlite,'sqlite-fixed')
            summary['php']=php('echo PHP_VERSION;')
            summary['files_sha256']={p:hashlib.sha256((ROOT/p).read_bytes()).hexdigest() for p in ['tests/demo_courses.php','database/demo_courses.php','database/seed_demo_courses.php','tools/seed_demo_courses.php']}
            (OUT/'summary.json').write_text(json.dumps(summary,indent=2)+'\n')
        finally:
            if server.poll() is None:
                for database in databases:php(root_connect+f"$p->exec('DROP DATABASE IF EXISTS `{database}`');")
                php(root_connect+f"$p->exec(\"DROP USER IF EXISTS '{user}'@'%'\");")
                server.terminate();server.wait(timeout=25)
            log.close()


if __name__=='__main__':main()
