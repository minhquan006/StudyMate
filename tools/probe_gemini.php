<?php
// Explicit live probe: two real requests using public seed lesson text, no identity payload.
if(PHP_SAPI!=='cli')exit;
require dirname(__DIR__).'/app/bootstrap.php';require dirname(__DIR__).'/app/services.php';require dirname(__DIR__).'/app/ai_service.php';
function probeResult(array $result,int $code):never{echo json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).PHP_EOL;exit($code);}
if(!$config['ai_enabled']||!$config['gemini_api_key'])probeResult(['status'=>'blocked','reason'=>'AI_ENABLED/GEMINI_API_KEY chưa được cấu hình; không có request provider nào được gửi.'],2);
if(getenv('STUDYMATE_ALLOW_LIVE_AI')!=='1')probeResult(['status'=>'blocked','reason'=>'Cần STUDYMATE_ALLOW_LIVE_AI=1 để xác nhận gửi hai request Gemini thật bằng bài học mẫu công khai; chỉ dùng free tier.'],2);
$enrollment=one("SELECT e.user_id,u.auth_version FROM enrollments e JOIN users u ON u.id=e.user_id WHERE e.course_id=2 AND u.role='student' AND u.is_active=1 ORDER BY e.id LIMIT 1");
if(!$enrollment)probeResult(['status'=>'blocked','reason'=>'Cần Student active đã enroll khóa mẫu JavaScript id=2.'],2);
$_SESSION=['user_id'=>(int)$enrollment['user_id'],'auth_version'=>(int)$enrollment['auth_version']];
$lesson=one('SELECT l.*,ch.course_id FROM lessons l JOIN chapters ch ON ch.id=l.chapter_id WHERE ch.course_id=2 ORDER BY ch.position,l.position,l.id LIMIT 1');
if(!$lesson)probeResult(['status'=>'blocked','reason'=>'Không tìm thấy bài mẫu công khai trong khóa JavaScript.'],2);
$results=[];
foreach(['ask','summarize'] as $mode){$start=microtime(true);try{$answer=aiHelp($lesson,$mode,'Giải thích ngắn gọn kiến thức chính trong bài học này.');$results[]=['mode'=>$mode,'status'=>'passed','answer_characters'=>length($answer),'elapsed_ms'=>round((microtime(true)-$start)*1000)];}catch(AppError $e){$results[]=['mode'=>$mode,'status'=>'failed','http_status'=>$e->status,'message'=>$e->getMessage(),'elapsed_ms'=>round((microtime(true)-$start)*1000)];}}
$passed=count(array_filter($results,fn($r)=>$r['status']==='passed'))===2;
probeResult(['status'=>$passed?'passed':'failed','model'=>$config['ai_model'],'provider'=>'Gemini thật','results'=>$results,'limitations'=>'Không tự xác nhận chất lượng câu trả lời, free tier/billing, provider quota thật hoặc nghiệm thu của Quân.'], $passed?0:1);
