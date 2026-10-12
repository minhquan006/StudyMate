<?php
function student_actions(string $action,int $cid,array $stamp):never{global $config;switch($action){
 case 'enroll':
  $u=requireUser('student');transaction(function()use($cid,$u,$stamp){$c=lockCourse($cid);if(one('SELECT id FROM enrollments WHERE user_id=? AND course_id=?',[$u['id'],$cid]))return;if($c['status']!=='published')reject('Khóa học không nhận học viên mới.',409);insertRow('enrollments',['user_id'=>$u['id'],'course_id'=>$cid,'last_lesson_id'=>null,'completed_at'=>null]+$stamp);});go('learn',['id'=>$cid]);
 case 'last_lesson':
  $e=enrolled($cid);$lid=(int)($_POST['lesson_id']??0);$l=lessonCourse($lid);if((int)$l['course_id']!==$cid)reject('Bài không thuộc khóa học.');run('UPDATE enrollments SET last_lesson_id=?,updated_at=? WHERE id=?',[$lid,now(),$e['id']]);go('learn',['id'=>$cid,'lesson'=>$lid]);
 case 'complete':
  $u=requireUser('student');$lid=(int)($_POST['lesson_id']??0);$l=lessonCourse($lid);$cid=(int)$l['course_id'];enrolled($cid);transaction(function()use($u,$lid,$cid,$stamp){lockCourse($cid);if(!one('SELECT id FROM lesson_progress WHERE user_id=? AND lesson_id=?',[$u['id'],$lid]))insertRow('lesson_progress',['user_id'=>$u['id'],'lesson_id'=>$lid,'completed_at'=>now()]+$stamp);completionService((int)$u['id'],$cid);});flash('Đã ghi nhận hoàn thành bài học.');go('learn',['id'=>$cid,'lesson'=>$lid]);
 case 'submit_quiz':
  $u=requireUser('student');enrolled($cid);mustConfirm();$answers=$_POST['answers']??[];if(!is_array($answers))reject('Câu trả lời không hợp lệ.');$rid=(string)($_POST['request_id']??'');$attempt=storeAttempt($cid,(int)$u['id'],$rid,$answers);unset($_SESSION['quiz_uuid'][$cid]);go('result',['id'=>$attempt]);
default:reject('Thao tác không tồn tại.',404);}}
