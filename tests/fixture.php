<?php
// Test-only helper. Never execute against data that must be retained.
if(PHP_SAPI!=='cli'||getenv('STUDYMATE_DISPOSABLE_DB')!=='1')throw new RuntimeException('Requires STUDYMATE_DISPOSABLE_DB=1 on a disposable test database');
require dirname(__DIR__).'/app/bootstrap.php';
require dirname(__DIR__).'/database/seed.php';
$mode=$argv[1]??'inspect';
if($mode==='snapshot'){
 $out=[];foreach(['users','categories','courses','chapters','lessons','enrollments','lesson_progress','quizzes','questions','answers','quiz_attempts','quiz_answers'] as $table)$out[$table]=hash('sha256',json_encode(all('SELECT * FROM '.$table.' ORDER BY id')));
 echo json_encode($out);exit;
}
if($mode==='inspect'){
 echo json_encode(['lessons'=>all('SELECT l.id,ch.course_id FROM lessons l JOIN chapters ch ON ch.id=l.chapter_id ORDER BY l.id'),'answers'=>all('SELECT q.id question_id,a.id answer_id,z.course_id FROM answers a JOIN questions q ON q.id=a.question_id JOIN quizzes z ON z.id=q.quiz_id WHERE a.is_correct=1'),'counts'=>['enrollments'=>all('SELECT user_id,course_id,COUNT(*) n FROM enrollments GROUP BY user_id,course_id'),'progress'=>all('SELECT user_id,lesson_id,COUNT(*) n FROM lesson_progress GROUP BY user_id,lesson_id'),'attempts'=>all('SELECT id,user_id,request_id,score_percent FROM quiz_attempts')]]);exit;
}
if($mode==='perf'){
 if((int)val('SELECT COUNT(*) FROM users')!==5||(int)val('SELECT COUNT(*) FROM courses')!==3)throw new RuntimeException('Expected a fresh seeded DB');
 $stamp=['created_at'=>now(),'updated_at'=>now()];$hash=one('SELECT password FROM users WHERE id=1')['password'];
 transaction(function()use($stamp,$hash){
 for($i=6;$i<=100;$i++)insertRow('users',['name'=>'Performance Student '.$i,'email'=>'perf'.$i.'@studymate.test','password'=>$hash,'role'=>'student','is_active'=>1,'auth_version'=>1]+$stamp);
 // Seed contains 18 lessons. Add exactly 482 across 97 new courses.
 for($i=4;$i<=100;$i++){
 $cid=insertRow('courses',['instructor_id'=>3,'category_id'=>1,'title'=>'Performance Course '.str_pad((string)$i,3,'0',STR_PAD_LEFT),'description'=>str_repeat('Nội dung đo hiệu năng. ',6),'cover_key'=>'web','status'=>'published']+$stamp);
 $chapter=insertRow('chapters',['course_id'=>$cid,'title'=>'Performance chapter','position'=>1]+$stamp);
 for($j=1;$j<=($i<=97?5:4);$j++)insertRow('lessons',['chapter_id'=>$chapter,'title'=>'Performance lesson '.$j,'body'=>str_repeat('Nội dung bài học kiểm thử. ',10),'youtube_video_id'=>null,'position'=>$j]+$stamp);
 insertRow('enrollments',['user_id'=>1,'course_id'=>$cid,'last_lesson_id'=>null,'completed_at'=>null]+$stamp);
 }
 for($i=6;$i<=100;$i++)insertRow('enrollments',['user_id'=>$i,'course_id'=>2,'last_lesson_id'=>null,'completed_at'=>null]+$stamp);
 });
 echo json_encode(['users'=>(int)val('SELECT COUNT(*) FROM users'),'courses'=>(int)val('SELECT COUNT(*) FROM courses'),'lessons'=>(int)val('SELECT COUNT(*) FROM lessons')]);exit;
}
throw new RuntimeException('Unknown fixture mode');
