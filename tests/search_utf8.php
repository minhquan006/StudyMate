<?php
// Regression of the rendered public/Student/Admin search, with rollback fixtures.
if(PHP_SAPI!=='cli')exit;
set_exception_handler(static function(Throwable $error):void{fwrite(STDERR,$error->getMessage()."\n");exit(1);});
if(!isset($argv[1])){
 foreach(['public','student','admin'] as $role){
  $process=proc_open([PHP_BINARY,__FILE__,$role],[0=>STDIN,1=>STDOUT,2=>STDERR],$pipes);
  if(!is_resource($process)||proc_close($process)!==0)exit(1);
 }
 exit(0);
}
require dirname(__DIR__).'/app/bootstrap.php';require dirname(__DIR__).'/app/services.php';require dirname(__DIR__).'/database/seed.php';
require dirname(__DIR__).'/app/views.php';require dirname(__DIR__).'/app/management_views.php';
if(getenv('STUDYMATE_DISPOSABLE_DB')!=='1'||!str_ends_with($config['db_name'],'_test'))throw new RuntimeException('Requires a separate seeded *_test database');
$role=$argv[1];if(!in_array($role,['public','student','admin'],true))throw new RuntimeException('Invalid test role');
$checks=0;
function searchCheck(bool $ok,string $name):void{global $checks,$role;if(!$ok)throw new RuntimeException("FAIL $role: $name");echo "PASS $role: $name\n";$checks++;}
function searchRender(string $role,string $tab,$q,int $page=1):string{
 $_GET=['q'=>$q,'p'=>$page,'page'=>$role==='public'?'courses':$role,'tab'=>$tab];
 ob_start();try{if($role==='public')pageCourses();elseif($role==='student')pageStudent();else pageAdmin();return ob_get_contents();}finally{ob_end_clean();}
}
function searchRows(string $html,string $role):int{
 if($role!=='admin')return substr_count($html,'class="course-card"');
 preg_match('/<tbody>(.*?)<\/tbody>/s',$html,$match);return substr_count($match[1]??'','<tr>');
}
$stamp=['created_at'=>now(),'updated_at'=>now()];$prefix=str_repeat('ắ',34);
db()->beginTransaction();
try{
 $uid=insertRow('users',['name'=>$prefix,'email'=>'utf8-'.bin2hex(random_bytes(4)).'@studymate.test','password'=>'unused-search-fixture','role'=>$role==='admin'?'admin':'student','is_active'=>1,'auth_version'=>1]+$stamp);
 $_SESSION=['user_id'=>$uid,'auth_version'=>1,'csrf'=>'search-test'];
 $category=insertRow('categories',['name'=>$prefix]+$stamp);
 $instructor=(int)val("SELECT id FROM users WHERE role='instructor' ORDER BY id LIMIT 1");
 for($i=0;$i<13;$i++){
  $cid=insertRow('courses',['title'=>$prefix.' '.$i,'instructor_id'=>$instructor,'category_id'=>$category,'description'=>'Disposable UTF-8 search fixture only.','cover_key'=>'default','status'=>'published']+$stamp);
  insertRow('enrollments',['user_id'=>$uid,'course_id'=>$cid,'last_lesson_id'=>null,'completed_at'=>null]+$stamp);
 }
 $long=str_repeat('ộ',100);
 $cid=insertRow('courses',['title'=>$long,'instructor_id'=>$instructor,'category_id'=>$category,'description'=>'Unicode boundary fixture.','cover_key'=>'default','status'=>'published']+$stamp);
 insertRow('enrollments',['user_id'=>$uid,'course_id'=>$cid,'last_lesson_id'=>null,'completed_at'=>null]+$stamp);
 $tabs=$role==='admin'?['users','courses','categories']:['courses'];
 foreach($tabs as $tab){
  $expected=$role!=='admin'||$tab==='courses'?12:1;
  foreach([33,34] as $size){
   $q=str_repeat('ắ',$size);$html=searchRender($role,$tab,$q);
   searchCheck(searchRows($html,$role)===$expected,"$tab: $size Vietnamese characters find the fixture");
   searchCheck(preg_match('//u',$html)===1&&!str_contains($html,"\u{FFFD}")&&str_contains($html,'value="'.$q.'"'),"$tab: valid UTF-8 and unchanged query");
  }
  $html=searchRender($role,$tab,$prefix);
  searchCheck((bool)preg_match('/name="q"[^>]*maxlength="100"/',$html),"$tab: form has the same 100 character limit");
  foreach(["\xc3",['invalid']] as $invalid){
   try{searchRender($role,$tab,$invalid);throw new RuntimeException('Malformed query accepted');}
   catch(AppError $error){searchCheck($error->status===422,"$tab: malformed query rejected before SQL");}
  }
  searchCheck(searchRows(searchRender($role,$tab,$prefix.'%_!'),$role)===0,"$tab: SQL wildcards remain literal");
 }
 // Courses have a 150-character field, so they can exercise the full search limit.
 foreach([$long,$long.'x'] as $q){
  $html=searchRender($role,'courses',$q);
  searchCheck(searchRows($html,$role)===1&&str_contains($html,'value="'.$long.'"'),'100/101 character search keeps a complete 100 character prefix');
 }
 $page2=searchRender($role,'courses',$prefix,2);
 searchCheck(searchRows($page2,$role)===1,'page two preserves the filtered result');
 $page1=searchRender($role,'courses',$prefix);
 searchCheck(str_contains(html_entity_decode($page1,ENT_QUOTES,'UTF-8'),'q='.urlencode($prefix)),'pagination preserves the UTF-8 filter');
 $_GET=['q'=>'  😀Tiếng Việt  '];searchCheck(searchQuery()==='😀Tiếng Việt','trims whitespace and preserves emoji');
 $_GET=['q'=>str_repeat('😀',101)];searchCheck(searchQuery()===str_repeat('😀',100),'four-byte characters are not split');
 echo "$checks $role UTF-8 search checks passed.\n";
}finally{if(db()->inTransaction())db()->rollBack();}
