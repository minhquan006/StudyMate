<?php
// Offline contract tests, not evidence of a real Gemini provider call.
if(getenv('STUDYMATE_DISPOSABLE_DB')!=='1')throw new RuntimeException('Disposable DB required');
require dirname(__DIR__).'/app/bootstrap.php';require dirname(__DIR__).'/app/services.php';require dirname(__DIR__).'/app/ai_service.php';
$_SESSION=['user_id'=>1,'auth_version'=>(int)val('SELECT auth_version FROM users WHERE id=1')];
$storage=sys_get_temp_dir().'/studymate-ai-'.bin2hex(random_bytes(6));mkdir($storage,0700);
$n=0;function testAi(bool $ok,string $name):void{global $n;if(!$ok)throw new RuntimeException($name);echo "PASS $name\n";$n++;}
function expectAi(int $status,callable $call,string $name):void{try{$call();}catch(AppError $e){testAi($e->status===$status,$name);return;}throw new RuntimeException('Expected failure: '.$name);}
$lesson=lessonCourse(7);$config['ai_enabled']=true;$config['gemini_api_key']='offline-test-placeholder';$calls=0;
$success=function($payload)use(&$calls){$calls++;testAi(!str_contains(json_encode($payload),'studymate.test'),'no user email in provider payload');return [200,json_encode(['candidates'=>[['content'=>['parts'=>[['text'=>'Nội dung kiểm thử offline.']]]]]]),.01];};
try{
 foreach(['ask','summarize'] as $mode)testAi(aiHelp($lesson,$mode,'Giải thích bài học',$success)==='Nội dung kiểm thử offline.','offline '.$mode.' transport');
 expectAi(422,fn()=>aiHelp($lesson,'ask',str_repeat('á',1001),$success),'1001 Unicode characters rejected');
 expectAi(422,fn()=>aiHelp($lesson,'invalid','test',$success),'invalid mode rejected');
 testAi($calls===2,'invalid input never calls provider');
 $config['gemini_api_key']='';expectAi(503,fn()=>aiHelp($lesson,'ask','test',$success),'missing key 503');$config['gemini_api_key']='offline-test-placeholder';
 foreach([[403,'denied',.01,503,'provider rejects key'],[429,'quota',.01,429,'provider quota'],[0,false,.01,503,'network failure'],[0,false,25,504,'network timeout'],[200,'{}',.01,503,'empty response'],[200,'invalid json',.01,503,'malformed provider JSON'],[200,'{"candidates":[{"content":{"parts":"invalid"}}]}',.01,503,'malformed parts']] as [$status,$body,$duration,$expected,$name]){
  @unlink($storage.'/limits.json');expectAi($expected,fn()=>aiHelp($lesson,'ask','test',fn($p)=>[$status,$body,$duration]),$name);
 }
 $min=intdiv(time(),60);$day=(new DateTimeImmutable('now',new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d');
 foreach(['ai:user:1:m:'.$min=>5,'ai:app:m:'.$min=>5,'ai:user:1:d:'.$day=>20,'ai:app:d:'.$day=>100] as $key=>$count){
  $state=[$key=>['count'=>$count,'until'=>time()+3600]];file_put_contents($storage.'/limits.json',json_encode($state));$before=$calls;
  expectAi(429,fn()=>aiHelp($lesson,'ask','test',$success),'quota '.$key);testAi($calls===$before&&json_decode(file_get_contents($storage.'/limits.json'),true)===$state,'quota rejection does not reserve other counters');
 }
 // Global cap counts multiple users together, independent of their per-user counters.
 file_put_contents($storage.'/limits.json',json_encode(['ai:user:2:m:'.$min=>['count'=>5,'until'=>time()+3600],'ai:app:m:'.$min=>['count'=>5,'until'=>time()+3600]]));
 expectAi(429,fn()=>aiHelp($lesson,'ask','test',$success),'global quota blocks a different user too');
 echo "$n offline AI contract checks passed. Real Gemini: NOT RUN.\n";
}finally{@unlink($storage.'/limits.json');rmdir($storage);}
