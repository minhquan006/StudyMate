<?php
// Run only through tests/quan/run.py: it creates an isolated disposable copy/DB.
require dirname(__DIR__,2).'/app/bootstrap.php';
require dirname(__DIR__,2).'/app/services.php';
require dirname(__DIR__,2).'/app/ai_service.php';
if(getenv('STUDYMATE_QUAN_TEST')!=='1')exit("Use python3 tests/quan/run.py\n");
$checks=0;
function check(bool $ok,string $name):void{global $checks;if(!$ok)throw new RuntimeException('FAIL: '.$name);echo "PASS: $name\n";$checks++;}
function fails(callable $fn,int $status,string $name):void{try{$fn();}catch(AppError $e){check($e->status===$status,$name);return;}throw new RuntimeException('FAIL: '.$name.' did not reject');}
$_SESSION=['user_id'=>(int)($argv[1]??1),'auth_version'=>1];
$lesson=lessonCourse(7);
$called=0;
$request=function(array $payload)use(&$called,$lesson):array{$called++;check(str_contains($payload['contents'][0]['parts'][0]['text'],$lesson['body']),'server lesson context included');check(!str_contains(json_encode($payload),'student1@'),'no user email sent');return [200,json_encode(['candidates'=>[['finishReason'=>'STOP','content'=>['parts'=>[['text'=>'Tóm tắt thử nghiệm.']]]]]]),.01];};
$config['ai_enabled']=true;$config['gemini_api_key']='test-only-placeholder';
if(($argv[1]??'1')!=='1'){
    fails(fn()=>aiHelp($lesson,'ask','Hỏi bài',$request),(int)$argv[2],'AI rejects wrong role or missing enrollment');
    check($called===0,'unauthorized request never reaches provider');exit;
}
function resetQuota():void{global $storage;file_put_contents($storage.'/limits.json','{}');}
resetQuota();
fails(fn()=>aiHelp($lesson,'ask',str_repeat('a',1001),$request),422,'question over 1000 characters');
fails(fn()=>aiHelp($lesson,'ask','   ',$request),422,'blank question');
fails(fn()=>aiHelp($lesson,'invalid','question',$request),422,'invalid mode');
check($called===0,'invalid requests do not reach provider');
check(json_decode(file_get_contents($storage.'/limits.json'),true)===[],'invalid requests do not consume quota');
check(aiHelp($lesson,'ask','Nội dung bài là gì?',$request)==='Tóm tắt thử nghiệm.','ask response');
check(aiHelp($lesson,'summarize','',$request)==='Tóm tắt thử nghiệm.','summarize response');
for($i=0;$i<3;$i++)aiHelp($lesson,'ask','Nội dung bài là gì?',$request);
fails(fn()=>aiHelp($lesson,'ask','Nội dung bài là gì?',$request),429,'sixth request in minute denied');
check($called===5,'quota stops provider call');
$day=(new DateTimeImmutable('now',new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d');
foreach(['ai:user:1:d:'.$day=>20,'ai:app:d:'.$day=>100,'ai:app:m:'.intdiv(time(),60)=>5] as $key=>$count){
 file_put_contents($storage.'/limits.json',json_encode([$key=>['count'=>$count,'until'=>time()+3600]]));
 fails(fn()=>aiHelp($lesson,'ask','Câu hỏi',$request),429,'quota '.$key);
}
resetQuota();
$config['ai_enabled']=false;fails(fn()=>aiHelp($lesson,'ask','Câu hỏi',$request),503,'disabled AI');$config['ai_enabled']=true;
$config['gemini_api_key']='';fails(fn()=>aiHelp($lesson,'ask','Câu hỏi',$request),503,'missing key');$config['gemini_api_key']='test-only-placeholder';
$config['ai_model']='bad/model';fails(fn()=>aiHelp($lesson,'ask','Câu hỏi',$request),503,'invalid model');$config['ai_model']='gemini-2.5-flash-lite';
foreach([[false,0,.01,503],[false,0,25.0,504],['',504,.1,504],['',429,.1,429],['',400,.1,503],['',403,.1,503],['',500,.1,503],['not json',200,.1,503],['null',200,.1,503],['{}',200,.1,503],['{"candidates":[{"finishReason":"SAFETY","content":{"parts":[{"text":"blocked"}]}}]}',200,.1,503],['{"candidates":[{"finishReason":"STOP","content":{"parts":[{"text":[]}]}}]}',200,.1,503]] as [$body,$status,$elapsed,$expected])fails(fn()=>aiProviderAnswer($body,$status,$elapsed),$expected,'provider error '.$status.' / '.$elapsed);
$answer=aiProviderAnswer('{"candidates":[{"finishReason":"MAX_TOKENS","content":{"parts":[{"thought":true,"text":"internal"},{"text":"visible"}]}}]}',200,.1);
check($answer==='visible','thought parts excluded; truncated output supported');
resetQuota();$before=$called;
fails(fn()=>aiHelp($lesson,'ask','Câu hỏi',fn($p)=>[429,'',.1]),429,'provider failure still consumes quota');
$quota=json_decode(file_get_contents($storage.'/limits.json'),true);
check($quota['ai:user:1:d:'.$day]['count']===1,'failed provider call counted');
// Forked reservations test shared application limit across distinct users.
resetQuota();$children=[];
for($i=0;$i<12;$i++){
 $pid=pcntl_fork();if($pid===-1)throw new RuntimeException('fork failed');
 if($pid===0){$ok=takeQuota(['parallel:app'=>['max'=>5,'until'=>time()+60],'parallel:user:'.$i=>['max'=>5,'until'=>time()+60]]);exit($ok?0:10);}
 $children[]=$pid;
}
$accepted=0;foreach($children as $pid){pcntl_waitpid($pid,$status);$code=pcntl_wexitstatus($status);if($code===0)$accepted++;elseif($code!==10)throw new RuntimeException('child failed');}
check($accepted===5,'12 parallel users admit exactly 5 shared reservations');
$quota=json_decode(file_get_contents($storage.'/limits.json'),true);check($quota['parallel:app']['count']===5,'shared quota file remains valid and bounded');
resetQuota();
echo "$checks AI checks passed (simulated transport; not live Gemini).\n";
