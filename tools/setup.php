<?php
require dirname(__DIR__).'/app/bootstrap.php';require dirname(__DIR__).'/database/schema.php';
if($config['db_driver']==='mysql'){
 $exists=false;try{db();$exists=true;}catch(PDOException $e){if(($e->errorInfo[1]??0)!==1049)throw $e;}
 if(!$exists){
 if(!preg_match('/^[a-zA-Z0-9_]+$/',$config['db_name']))exit("Invalid database name\n");
 $server=new PDO('mysql:host='.$config['db_host'].';port='.$config['db_port'].';charset=utf8mb4',$config['db_user'],$config['db_password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
 $server->exec('CREATE DATABASE IF NOT EXISTS `'.$config['db_name'].'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
 }
}
foreach(schema($config['db_driver']) as $sql)db()->exec($sql);
if((int)val('SELECT COUNT(*) FROM users')>0){echo "Database đã có dữ liệu; không ghi đè hoặc reset.\n";exit;}
if(strlen($config['demo_password'])<10)exit("Demo password must have at least 10 characters\n");
require dirname(__DIR__).'/database/seed.php';transaction(fn()=>seedDatabase());echo "Setup hoàn tất: ".$config['db_driver'].". Demo accounts seeded.\n";
