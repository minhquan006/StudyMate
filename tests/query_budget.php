<?php
// MySQL performance DB only, with enough rows for 12- and 50-row pages.
if(getenv('STUDYMATE_DISPOSABLE_DB')!=='1')throw new RuntimeException('Disposable DB required');
require dirname(__DIR__).'/app/bootstrap.php';require dirname(__DIR__).'/database/seed.php';require dirname(__DIR__).'/app/services.php';require dirname(__DIR__).'/app/views.php';require dirname(__DIR__).'/app/management_views.php';
$mode=$argv[1]??'student';$uid=['student'=>1,'instructor'=>3,'roster'=>3,'admin'=>5][$mode]??throw new RuntimeException('Invalid mode');
$_SESSION=['user_id'=>$uid,'auth_version'=>(int)val('SELECT auth_version FROM users WHERE id=?',[$uid])];currentUser();
function questionsCount():int{return (int)db()->query("SHOW SESSION STATUS LIKE 'Questions'")->fetch()['Value'];}
$counts=[];
foreach([12,50] as $size){$_GET=['page'=>$mode,'per_page'=>$size,'tab'=>$mode==='roster'?'students':'users'];$before=questionsCount();ob_start();match($mode){'student'=>pageStudent(),'instructor'=>pageInstructor(),'roster'=>pageEdit(2),'admin'=>pageAdmin()};ob_end_clean();$counts[$size]=questionsCount()-$before;}
if($counts[12]!==$counts[50])throw new RuntimeException('Query count grows with rows');
echo json_encode(['mode'=>$mode,'query_counts'=>$counts,'result'=>'passed: query count independent of page size']);
