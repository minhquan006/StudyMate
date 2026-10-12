<?php
declare(strict_types=1);
$configFile=dirname(__DIR__).'/config.php';
if(!is_file($configFile)){http_response_code(503);exit('Chưa có config.php. Sao chép config.example.php thành config.php và chạy php tools/setup.php.');}
$config=require $configFile;
date_default_timezone_set('UTC');
ini_set('display_errors',$config['app_env']==='local'?'1':'0');
$storage=dirname(__DIR__).'/storage';if(!is_dir($storage))mkdir($storage,0700,true);
function db():PDO{global $config;static $pdo;if($pdo)return $pdo;$driver=$config['db_driver'];
 if($driver==='sqlite'){$pdo=new PDO('sqlite:'.$config['sqlite_path']);$pdo->exec('PRAGMA foreign_keys=ON');$pdo->exec('PRAGMA busy_timeout=5000');}
 else{$pdo=new PDO('mysql:host='.$config['db_host'].';port='.$config['db_port'].';dbname='.$config['db_name'].';charset=utf8mb4',$config['db_user'],$config['db_password']);}
 $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);return $pdo;}
function run(string $sql,array $params=[]):PDOStatement{$s=db()->prepare($sql);$s->execute($params);return $s;}
function all(string $sql,array $params=[]):array{return run($sql,$params)->fetchAll();}
function one(string $sql,array $params=[]):?array{return run($sql,$params)->fetch()?:null;}
function val(string $sql,array $params=[]){return run($sql,$params)->fetchColumn();}
function h($text):string{return htmlspecialchars((string)($text??''),ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
function now():string{return gmdate('Y-m-d H:i:s');}
function url(string $page,array $params=[]):string{return '/index.php?'.http_build_query(['page'=>$page]+$params);}
function go(string $page,array $params=[]):never{header('Location: '.url($page,$params),true,303);exit;}
function flash(string $message,string $type='success'):void{$_SESSION['flash']=['message'=>$message,'type'=>$type];}
class AppError extends RuntimeException{public function __construct(string $message,public int $status=422){parent::__construct($message);}}
function reject(string $message,int $status=422):never{throw new AppError($message,$status);}
function length(string $text):int{return function_exists('mb_strlen')?mb_strlen($text,'UTF-8'):preg_match_all('/./us',$text);}
function textInput(string $name,int $min,int $max):string{$value=trim((string)($_POST[$name]??''));if(length($value)<$min||length($value)>$max)reject("Trường $name cần $min–$max ký tự.");return $value;}
function csrf():string{return '<input type="hidden" name="csrf" value="'.h($_SESSION['csrf']).'">';}
function hidden(string $name,$value):string{return '<input type="hidden" name="'.h($name).'" value="'.h($value).'">';}
function formStart(string $action,array $fields=[]):string{$s='<form method="post" action="/index.php">'.csrf().hidden('action',$action);foreach($fields as $k=>$v)$s.=hidden($k,$v);return $s;}
function mustConfirm():void{if(($_POST['confirm']??'')!=='1')reject('Vui lòng xác nhận thao tác trước khi thực hiện.');}
function confirmField():string{return '<label class="small confirmation-label"><input type="checkbox" name="confirm" value="1" required> Tôi xác nhận thao tác này</label>';}
if(PHP_SAPI!=='cli'){
 header('X-Content-Type-Options: nosniff');header('Referrer-Policy: same-origin');header('Cache-Control: no-store');
 header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'self'; img-src 'self' data:; frame-src https://www.youtube-nocookie.com; form-action 'self'; base-uri 'self'; frame-ancestors 'none'");
 ini_set('session.use_strict_mode','1');ini_set('session.use_only_cookies','1');ini_set('session.gc_maxlifetime','7200');if(!is_dir($storage.'/sessions'))mkdir($storage.'/sessions',0700,true);session_save_path($storage.'/sessions');session_name('studymate_session');session_set_cookie_params(['httponly'=>true,'secure'=>(bool)$config['secure_cookie'],'samesite'=>'Lax','path'=>'/']);session_start();
 if(isset($_SESSION['last_activity'])&&time()-$_SESSION['last_activity']>7200){$_SESSION=[];session_regenerate_id(true);}$_SESSION['last_activity']=time();$_SESSION['csrf']??=bin2hex(random_bytes(32));
}
function currentUser():?array{static $loaded=false,$user=null;if($loaded)return $user;$loaded=true;if(empty($_SESSION['user_id']))return null;$user=one('SELECT id,name,email,role,is_active,auth_version FROM users WHERE id=?',[$_SESSION['user_id']]);if(!$user||!(int)$user['is_active']||(int)$user['auth_version']!==(int)($_SESSION['auth_version']??-1)){unset($_SESSION['user_id'],$_SESSION['auth_version']);$user=null;}return $user;}
function requireUser(?string $role=null):array{$u=currentUser();if(!$u)reject('Phiên đăng nhập không hợp lệ hoặc tài khoản đã bị khóa.',401);if($role&&$u['role']!==$role)reject('Bạn không có quyền thực hiện thao tác này.',403);return $u;}
function loginUser(array $u):void{session_regenerate_id(true);$_SESSION['user_id']=(int)$u['id'];$_SESSION['auth_version']=(int)$u['auth_version'];$_SESSION['csrf']=bin2hex(random_bytes(32));}
function transaction(callable $callback){db()->beginTransaction();try{$r=$callback();db()->commit();return $r;}catch(Throwable $e){if(db()->inTransaction())db()->rollBack();throw $e;}}
function lockCourse(int $id):array{global $config;$c=one('SELECT * FROM courses WHERE id=?'.($config['db_driver']==='mysql'?' FOR UPDATE':''),[$id]);if(!$c)reject('Không tìm thấy khóa học.',404);return $c;}
// File locking makes rate/quota reservation atomic on one server, no external cache service.
function takeQuota(array $limits):bool{global $storage;$f=fopen($storage.'/limits.json','c+');if(!$f)throw new RuntimeException('Cannot open quota store');flock($f,LOCK_EX);try{$raw=stream_get_contents($f);$state=json_decode($raw,true)?:[];foreach($state as $k=>$v)if($v['until']<time())unset($state[$k]);$ok=true;foreach($limits as $key=>$limit){if(($state[$key]['count']??0)>=$limit['max'])$ok=false;}if($ok)foreach($limits as $key=>$limit)$state[$key]=['count'=>($state[$key]['count']??0)+1,'until'=>$limit['until']];rewind($f);ftruncate($f,0);fwrite($f,json_encode($state));fflush($f);return $ok;}finally{flock($f,LOCK_UN);fclose($f);}}
