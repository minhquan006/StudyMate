<?php
// CLI-only, in-memory SQLite. Does not connect to the configured application DB.
if(PHP_SAPI!=='cli')throw new RuntimeException('CLI only');
require dirname(__DIR__).'/app/bootstrap.php';
$config['db_driver']='sqlite';$config['sqlite_path']=':memory:';
require dirname(__DIR__).'/database/schema.php';require dirname(__DIR__).'/database/seed.php';require dirname(__DIR__).'/app/services.php';require dirname(__DIR__).'/app/study_buddy.php';
foreach(schema('sqlite') as $sql)run($sql);seedDatabase();
$checks=0;
function buddyCheck(bool $ok,string $message):void{global $checks;if(!$ok)throw new RuntimeException($message);$checks++;echo "PASS $message\n";}
buddyCheck(studyBuddyProfile(1)['xp']===20,'existing completed lesson receives retrospective XP');
buddyCheck(studyBuddyProfile(2)['xp']===0,'new account starts with zero XP');
$tables=['users','categories','courses','chapters','lessons','enrollments','lesson_progress','quizzes','questions','answers','quiz_attempts','quiz_answers'];
$snapshot=fn()=>array_combine($tables,array_map(fn($table)=>(int)val('SELECT COUNT(*) FROM '.$table),$tables));
$before=$snapshot();for($i=0;$i<10;$i++)studyBuddyProfile(1);
buddyCheck($snapshot()===$before,'repeated XP reads leave all 12 tables unchanged');
$stamp=['created_at'=>now(),'updated_at'=>now()];
foreach(courseLessons(2) as $lesson){if(!one('SELECT id FROM lesson_progress WHERE user_id=1 AND lesson_id=?',[$lesson['id']]))insertRow('lesson_progress',['user_id'=>1,'lesson_id'=>$lesson['id'],'completed_at'=>now()]+$stamp);}
buddyCheck(studyBuddyProfile(1)['xp']===120,'six distinct lessons earn 120 XP');
$wrong=[];$right=[];foreach(questionsFor(2) as $question){foreach($question['answers'] as $answer){if($answer['is_correct'])$right[$question['id']]=$answer['id'];else $wrong[$question['id']]=$answer['id'];}}
storeAttempt(2,1,uuid(),$wrong);
buddyCheck(studyBuddyProfile(1)['xp']===120,'failed quiz does not earn bonus XP');
$rid=uuid();$first=storeAttempt(2,1,$rid,$right);
buddyCheck(studyBuddyProfile(1)['xp']===170,'first passed quiz earns 50 XP');
buddyCheck(studyBuddyProfile(1)['quizzes']===1,'passed quiz counted once');
buddyCheck(storeAttempt(2,1,$rid,$right)===$first,'duplicate request returns original attempt');
buddyCheck(studyBuddyProfile(1)['xp']===170,'replayed quiz request does not duplicate XP');
storeAttempt(2,1,uuid(),$right);
buddyCheck(studyBuddyProfile(1)['xp']===170,'passing same quiz again does not duplicate XP');
buddyCheck(studyBuddyProfile(2)['xp']===0,'another account does not inherit earned XP');
$at=strtotime('2026-11-21 12:00:00 UTC');$old='2026-11-16 12:00:00';
run('UPDATE lesson_progress SET completed_at=? WHERE user_id=1',[$old]);run('UPDATE quiz_attempts SET submitted_at=? WHERE user_id=1',[$old]);run('UPDATE enrollments SET updated_at=? WHERE user_id=1',[$old]);
buddyCheck(studyBuddyProfile(1,$at)['inactiveDays']===5,'reminder uses actual most recent study activity');
run('UPDATE enrollments SET updated_at=? WHERE user_id=1',['2026-11-21 11:59:00']);
buddyCheck(studyBuddyProfile(1,$at)['inactiveDays']===0,'recent lesson navigation clears inactivity');
buddyCheck(studyBuddyProfile(2,$at)['inactiveDays']===0,'account without study history is not falsely overdue');
run('UPDATE enrollments SET updated_at=? WHERE user_id=1',['2026-11-22 12:00:00']);
buddyCheck(studyBuddyProfile(1,$at)['inactiveDays']===0,'future timestamp cannot create negative inactivity');
echo "$checks checks passed on isolated in-memory SQLite\n";
