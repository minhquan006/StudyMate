<?php
// Startup readiness only: no schema changes, seeding or data reset.
if(PHP_SAPI!=='cli')exit;
require dirname(__DIR__).'/app/bootstrap.php';
if($config['db_driver']!=='mysql')exit(0);
$raw=getenv('DB_WAIT_TIMEOUT');
$timeout=$raw===false||$raw===''?60:filter_var($raw,FILTER_VALIDATE_INT);
if($timeout===false||$timeout<1||$timeout>300){fwrite(STDERR,"DB_WAIT_TIMEOUT phải là số giây từ 1 đến 300.\n");exit(64);}
$deadline=hrtime(true)/1e9+$timeout;
$waiting=false;
do{
 try{
  $remaining=max(1,min(2,(int)ceil($deadline-hrtime(true)/1e9)));
  // Connect without dbname: setup.php may legitimately create the database later.
  $connection=new PDO('mysql:host='.$config['db_host'].';port='.$config['db_port'].';charset=utf8mb4',$config['db_user'],$config['db_password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_TIMEOUT=>$remaining]);
  $connection->query('SELECT 1');
  fwrite(STDOUT,"MySQL đã sẵn sàng.\n");exit(0);
 }catch(PDOException $error){
  $code=(int)($error->errorInfo[1]??0);
  // Retry connectivity failures; incorrect credentials/configuration fail immediately.
  if(!in_array($code,[2002,2003,2006,2013],true)){fwrite(STDERR,"Không thể kết nối MySQL (mã $code); kiểm tra cấu hình.\n");exit(1);}
  if(!$waiting){fwrite(STDERR,"Đang chờ MySQL, tối đa $timeout giây...\n");$waiting=true;}
 }
 $remaining=$deadline-hrtime(true)/1e9;
 if($remaining<=0)break;
 usleep((int)(min(1,$remaining)*1000000));
}while(hrtime(true)/1e9<$deadline);
fwrite(STDERR,"Hết thời gian chờ MySQL sau $timeout giây; chưa chạy setup.\n");exit(1);
