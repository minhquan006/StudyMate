<?php
// Advanced companion account, only on a fresh, explicitly disposable SQLite DB.
if(PHP_SAPI!=='cli'||getenv('STUDYMATE_DISPOSABLE_DB')!=='1')throw new RuntimeException('Disposable CLI DB required');
require dirname(__DIR__).'/app/bootstrap.php';
if($config['db_driver']!=='sqlite'||!str_ends_with(basename($config['sqlite_path']),'_test.sqlite'))throw new RuntimeException('Use a separate SQLite file ending _test.sqlite');
require dirname(__DIR__).'/database/seed.php';require dirname(__DIR__).'/app/services.php';require dirname(__DIR__).'/app/study_buddy.php';
if((int)val('SELECT COUNT(*) FROM users')!==5||(int)val('SELECT COUNT(*) FROM courses')!==3||(int)val('SELECT COUNT(*) FROM lesson_progress')!==1||(int)val('SELECT COUNT(*) FROM enrollments')!==1||(int)val('SELECT COUNT(*) FROM quiz_attempts')!==0)throw new RuntimeException('Fresh seeded test DB required');
$stamp=['created_at'=>now(),'updated_at'=>now()];
foreach([1,2] as $course){
    insertRow('enrollments',['user_id'=>2,'course_id'=>$course,'last_lesson_id'=>null,'completed_at'=>null]+$stamp);
    foreach(courseLessons($course) as $lesson)insertRow('lesson_progress',['user_id'=>2,'lesson_id'=>$lesson['id'],'completed_at'=>now()]+$stamp);
    $answers=[];foreach(questionsFor($course) as $question)foreach($question['answers'] as $answer)if($answer['is_correct'])$answers[$question['id']]=$answer['id'];
    storeAttempt($course,2,uuid(),$answers);
}
$old=gmdate('Y-m-d H:i:s',time()-5*86400);
run('UPDATE lesson_progress SET completed_at=? WHERE user_id=2',[$old]);run('UPDATE quiz_attempts SET submitted_at=? WHERE user_id=2',[$old]);run('UPDATE enrollments SET updated_at=? WHERE user_id=2',[$old]);
echo json_encode(studyBuddyProfile(2))."\n";
