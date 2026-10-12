<?php
// Run only on a disposable seeded test DB configured in config.php.
require dirname(__DIR__).'/app/bootstrap.php';require dirname(__DIR__).'/database/seed.php';require dirname(__DIR__).'/app/services.php';
if(PHP_SAPI!=='cli')exit;
$_SESSION=['user_id'=>1,'auth_version'=>1];$checks=0;
function check(bool $ok,string $name):void{global $checks;if(!$ok)throw new RuntimeException('FAIL: '.$name);echo 'PASS: '.$name."\n";$checks++;}
$course=2;$user=1;$lessons=courseLessons($course);$stamp=['created_at'=>now(),'updated_at'=>now()];
transaction(function()use($lessons,$user,$stamp){foreach($lessons as $l)if(!one('SELECT id FROM lesson_progress WHERE user_id=? AND lesson_id=?',[$user,$l['id']]))insertRow('lesson_progress',['user_id'=>$user,'lesson_id'=>$l['id'],'completed_at'=>now()]+$stamp);});
$qs=questionsFor($course);$answers=[];foreach($qs as $q)foreach($q['answers'] as $a)if($a['is_correct'])$answers[$q['id']]=$a['id'];$rid=uuid();$id=storeAttempt($course,$user,$rid,$answers);$a=one('SELECT * FROM quiz_attempts WHERE id=?',[$id]);check((float)$a['score_percent']===100.0&&(int)$a['passed']===1,'server grades 5/5=100%');$e=one('SELECT * FROM enrollments WHERE user_id=1 AND course_id=2');check((bool)$e['completed_at'],'completed_at recorded');check(storeAttempt($course,$user,$rid,$answers)===$id,'same UUID same payload returns one attempt');check((int)val('SELECT COUNT(*) FROM quiz_attempts WHERE request_id=?',[$rid])===1,'UUID unique count');
$conflict=false;try{storeAttempt($course,$user,$rid,[]);}catch(AppError $ex){$conflict=$ex->status===409;}check($conflict,'same UUID different payload conflict409');
$partial=array_slice($answers,0,3,true);$low=storeAttempt($course,$user,uuid(),$partial);$a=one('SELECT * FROM quiz_attempts WHERE id=?',[$low]);check((float)$a['score_percent']===60.0&&(int)$a['passed']===0,'3/5=60% not passed');check((int)val('SELECT COUNT(*) FROM quiz_answers WHERE attempt_id=? AND answer_id IS NULL',[$low])===2,'omitted questions persisted null');check(one('SELECT completed_at FROM enrollments WHERE user_id=1 AND course_id=2')['completed_at']===$e['completed_at'],'lower score does not overwrite completed_at');check(progressFor(1,2)['best']===100.0,'best score remains100');
$invalid=false;try{storeAttempt($course,$user,uuid(),[$qs[0]['id']=>$qs[1]['answers'][0]['id']]);}catch(AppError $ex){$invalid=$ex->status===422;}check($invalid,'answer-question relation validated');
$duplicate=false;try{insertRow('enrollments',['user_id'=>1,'course_id'=>2,'last_lesson_id'=>null,'completed_at'=>null]+$stamp);}catch(PDOException $ex){$duplicate=true;}check($duplicate,'database unique enrollment');
$fk=false;try{run('DELETE FROM categories WHERE id=1');}catch(PDOException $ex){$fk=true;}check($fk,'database FK restrict category');
check(password_verify('StudyMate2026!',one('SELECT password FROM users WHERE id=1')['password']),'bcrypt seeded password');check(one('SELECT password FROM users WHERE id=1')['password']!=='StudyMate2026!','no plaintext password');
echo "$checks checks passed. Driver: ".$config['db_driver']."\n";
