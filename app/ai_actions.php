<?php
function ai_actions(string $action,int $cid,array $stamp):never{global $config;switch($action){
 case 'ai':
  $lid=(int)($_POST['lesson_id']??0);$l=lessonCourse($lid);$answer=aiHelp($l,(string)($_POST['mode']??'ask'),(string)($_POST['question']??''));$_SESSION['ai_result']=['lesson_id'=>$lid,'answer'=>$answer];go('learn',['id'=>$l['course_id'],'lesson'=>$lid]);
default:reject('Thao tác không tồn tại.',404);}}
