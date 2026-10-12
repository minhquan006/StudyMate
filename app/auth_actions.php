<?php
function auth_actions(string $action,int $cid,array $stamp):never{global $config;switch($action){
 case 'register':
  minuteLimit('register:'.($_SERVER['REMOTE_ADDR']??'local'),3);$name=textInput('name',2,80);$email=strtolower(trim((string)($_POST['email']??'')));if(strlen($email)>254||!filter_var($email,FILTER_VALIDATE_EMAIL))reject('Email không hợp lệ.');$password=passwordInput();if(one('SELECT id FROM users WHERE email=?',[$email]))reject('Email đã được sử dụng.');
  try{$id=insertRow('users',['name'=>$name,'email'=>$email,'password'=>password_hash($password,PASSWORD_BCRYPT,['cost'=>12]),'role'=>'student','is_active'=>1,'auth_version'=>1]+$stamp);}catch(PDOException $e){if(one('SELECT id FROM users WHERE email=?',[$email]))reject('Email đã được sử dụng.');throw $e;}loginUser(one('SELECT * FROM users WHERE id=?',[$id]));flash('Tài khoản Student đã được tạo.');go('student');
 case 'login':
  $email=strtolower(trim((string)($_POST['email']??'')));minuteLimit('login:'.hash('sha256',$email.'|'.($_SERVER['REMOTE_ADDR']??'local')),5);$u=one('SELECT * FROM users WHERE email=?',[$email]);$dummy='$2y$12$6TSCdrCZ7pycjOSjhsbupePSKYRAfsjg8ChCF76YRPuVRmuZGimrO';$ok=password_verify((string)($_POST['password']??''),$u['password']??$dummy);if(!$u||!$ok)reject('Email hoặc mật khẩu không đúng.',401);if(!(int)$u['is_active'])reject('Tài khoản đã bị khóa.',403);loginUser($u);flash('Đăng nhập thành công.');go($u['role']);
 case 'logout':requireUser();$_SESSION=[];session_regenerate_id(true);$_SESSION['csrf']=bin2hex(random_bytes(32));go('home');
default:reject('Thao tác không tồn tại.',404);}}
