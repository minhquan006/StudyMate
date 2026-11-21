<?php
function heading(string $eyebrow,string $title,string $description=''):void{echo '<div class="page-heading"><div><p class="eyebrow">'.h($eyebrow).'</p><h1>'.h($title).'</h1><p class="muted">'.h($description).'</p></div></div>';}
function uiIcon(string $name):string {
    $paths=['book'=>'M12 5v15M12 5C8 2 4 3 2 4v15c3-1 6-1 10 1 4-2 7-2 10-1V4c-2-1-6-2-10 1Z','search'=>'m21 21-5-5M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z','arrow'=>'M4 12h16m-6-6 6 6-6 6','check'=>'m5 12 4 4L19 6'];
    return '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="'.($paths[$name]??$paths['book']).'"/></svg>';
}

function statusName(string $s):string{return ['draft'=>t('Bản nháp'),'published'=>t('Đã xuất bản'),'archived'=>t('Đã lưu trữ')][$s]??$s;}
function statusBadge(string $s):string{return '<span class="status '.h($s).'">'.h(statusName($s)).'</span>';}
function progressBar(array $p):void{echo '<div class="progress-meta"><span>'.h(t('Tiến độ bài học')).'</span><strong>'.h($p['percent']).'%</strong></div><div class="progress" role="progressbar" aria-valuenow="'.h($p['percent']).'" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar" style="width:'.h($p['percent']).'%"></div></div>';}
function statCard(string $label,$value,string $sub=''):void{echo '<div class="stat"><span>'.h($label).'</span><strong>'.h($value).'</strong><small>'.h($sub).'</small></div>';}
function emptyState(string $title,string $detail=''):void{echo '<div class="empty"><span class="empty-icon">□</span><h3>'.h($title).'</h3><p>'.h($detail).'</p></div>';}

function field(string $name,string $label,string $value='',string $type='text',string $attrs=''):void{static $counter=0;$fieldId=$name.'-'.(++$counter);echo '<label for="'.h($fieldId).'">'.h($label).'<input class="form-control" id="'.h($fieldId).'" name="'.h($name).'" type="'.h($type).'" value="'.h($value).'" '.$attrs.'></label>';}
function area(string $name,string $label,string $value='',string $attrs=''):void{echo '<label>'.h($label).'<textarea class="form-control" name="'.h($name).'" '.$attrs.'>'.h($value).'</textarea></label>';}
function markdown(string $body):string{$out='';$code=false;$buf='';foreach(explode("\n",str_replace("\r",'',$body)) as $line){if(str_starts_with($line,'```')){if($code){$out.='<pre tabindex="0" role="region" aria-label="'.h(t('Đoạn mã')).'"><code>'.h($buf).'</code></pre>';$buf='';}$code=!$code;continue;}if($code){$buf.=$line."\n";continue;}if(preg_match('/^(#{1,3})\s+(.+)$/',$line,$m)){$n=strlen($m[1]);$out.="<h$n>".h($m[2])."</h$n>";}elseif(trim($line)!=='')$out.='<p>'.h($line).'</p>'; }if($code)$out.='<pre tabindex="0" role="region" aria-label="'.h(t('Đoạn mã')).'"><code>'.h($buf).'</code></pre>';return $out;}
function lessonViewer(array $l):void{echo '<article class="lesson-text">'.markdown($l['body']);if($l['youtube_video_id'])echo '<iframe title="'.h(t('Video bài học')).'" src="https://www.youtube-nocookie.com/embed/'.h($l['youtube_video_id']).'" allowfullscreen></iframe>';echo '</article>';}

function pageCourses():void {
    $q=searchQuery();$cat=(int)($_GET['category']??0);$page=max(1,(int)($_GET['p']??1));
    $where="c.status='published'";$params=[];
    if($q!==''){$where.=" AND LOWER(c.title) LIKE LOWER(?) ESCAPE '!'";$params[]=searchPattern($q);}
    if($cat){$where.=' AND c.category_id=?';$params[]=$cat;}
    $count=(int)val('SELECT COUNT(*) FROM courses c WHERE '.$where,$params);
    $last=max(1,(int)ceil($count/12));$page=min($page,$last);
    $rows=all('SELECT c.*,g.name category_name,u.name instructor_name FROM courses c JOIN categories g ON g.id=c.category_id JOIN users u ON u.id=c.instructor_id WHERE '.$where.' ORDER BY c.created_at DESC,c.id DESC LIMIT 12 OFFSET '.(($page-1)*12),$params);
    ?><main class="container catalog-page">
        <nav class="breadcrumb-link" aria-label="<?=h(t('Đường dẫn'))?>"><a href="<?=h(url('home'))?>"><?=h(t('Trang chủ'))?></a><span aria-hidden="true"> / </span><span><?=h(t('Khóa học'))?></span></nav>
        <?php heading(t('THƯ VIỆN STUDYMATE'),t('Tìm khóa học dành cho bạn'),t('Kiến thức nền tảng, ví dụ cụ thể và bài luyện tập. Tất cả đều miễn phí.')); ?>
        <div class="catalog-layout"><aside class="catalog-sidebar" aria-labelledby="filter-title"><h2 id="filter-title"><?=h(t('Bộ lọc khóa học'))?></h2><form class="catalog-filter" method="get" action="/index.php">
            <?=hidden('page','courses')?>
            <label class="search-field"><?=h(t('Tìm theo tên khóa học'))?><input class="form-control" name="q" placeholder="<?=h(t('Ví dụ: JavaScript'))?>" value="<?=h($q)?>" maxlength="100"></label>
            <label><?=h(t('Danh mục'))?><select class="form-select" name="category"><option value=""><?=h(t('Tất cả danh mục'))?></option><?php foreach(all('SELECT * FROM categories ORDER BY name') as $category): ?><option value="<?=$category['id']?>" <?=$cat===(int)$category['id']?'selected':''?>><?=h($category['name'])?></option><?php endforeach; ?></select></label>
            <button class="btn btn-primary w-100" type="submit"><?=h(t('Tìm khóa học'))?></button>
            <?php if($q!==''||$cat): ?><a class="filter-reset" href="<?=h(url('courses'))?>"><?=h(t('Xóa bộ lọc'))?></a><?php endif; ?>
        </form><div class="catalog-help"><h3><?=h(t('Học theo cách của bạn'))?></h3><p><?=h(t('Đọc bài bất cứ lúc nào. Tiến độ được lưu khi bạn đánh dấu hoàn thành.'))?></p><p><?=h(t('Quiz cuối khóa giúp bạn ôn tập và xem lại đáp án.'))?></p></div></aside>
        <section class="catalog-results" aria-label="<?=h(t('Kết quả khóa học'))?>"><div class="results-toolbar"><p><strong><?=$count?></strong> <?=h(countedLabel('khóa học',$count))?><?=$q!==''?' '.h(t('cho “{query}”',['query'=>$q])):t(' phù hợp')?></p><span><?=h(t('Mới nhất trước'))?></span></div><div class="course-list"><?php foreach($rows as $course)courseCard($course); ?></div>
        <?php if(!$rows): emptyState(t('Chưa tìm thấy khóa học phù hợp'),t('Thử một từ khóa ngắn hơn hoặc chọn tất cả danh mục.')); ?><div class="empty-action"><a class="btn btn-primary" href="<?=h(url('courses'))?>"><?=h(t('Xem tất cả khóa học'))?></a></div><?php endif; ?>
        <?php if($last>1): ?><nav class="pager" aria-label="<?=h(t('Phân trang khóa học'))?>"><?php if($page>1): ?><a class="btn btn-outline-secondary" rel="prev" href="<?=h(url('courses',['q'=>$q,'category'=>$cat,'p'=>$page-1]))?>"><?=h(t('Trước'))?></a><?php endif; ?><span><?=h(t('Trang {page} / {last}',['page'=>$page,'last'=>$last]))?></span><?php if($page<$last): ?><a class="btn btn-outline-secondary" rel="next" href="<?=h(url('courses',['q'=>$q,'category'=>$cat,'p'=>$page+1]))?>"><?=h(t('Sau'))?></a><?php endif; ?></nav><?php endif; ?></section></div>
    </main><?php
}

function pageDetail(int $id):void {
    $course=getCourse($id);
    if($course['status']!=='published')reject(t('Khóa học không công khai.'),404);
    $user=currentUser();
    $chapters=all('SELECT * FROM chapters WHERE course_id=? ORDER BY position,id',[$id]);
    $lessons=courseLessons($id);
    $enrollment=$user&&$user['role']==='student'?one('SELECT id FROM enrollments WHERE user_id=? AND course_id=?',[$user['id'],$id]):null;
    ?><main class="course-detail">
        <header class="detail-masthead"><div class="container"><a class="breadcrumb-link" href="<?=h(url('courses'))?>"><?=h(t('Khóa học /'))?> <?=h($course['category_name'])?></a>
            <div class="detail-intro"><p class="eyebrow"><?=h($course['category_name'])?></p><h1><?=h($course['title'])?></h1><p class="lead muted course-description"><?=h($course['description'])?></p><p class="instructor-line"><?=h(t('Biên soạn bởi'))?> <strong><?=h($course['instructor_name'])?></strong></p><div class="course-facts"><span><?=count($chapters)?> <?=h(t('chương'))?></span><span><?=count($lessons)?> <?=h(t('bài học'))?></span><span><?=h(t('Quiz cuối khóa'))?></span></div></div>
        </div></header>
        <div class="container detail-grid">
            <section class="detail-content"><nav class="detail-nav" aria-label="<?=h(t('Nội dung giới thiệu khóa học'))?>"><a href="#course-overview"><?=h(t('Tổng quan'))?></a><a href="#curriculum"><?=h(t('Nội dung khóa học'))?></a><a href="#completion"><?=h(t('Hoàn thành khóa'))?></a></nav>
                <?php if($lessons): ?><section class="panel learning-topics" id="course-overview"><h2><?=h(t('Bạn sẽ tìm hiểu'))?></h2><ul><?php foreach(array_slice($lessons,0,6) as $lesson): ?><li><?=h($lesson['title'])?></li><?php endforeach; ?></ul></section><?php endif; ?>
                <section class="panel curriculum" id="curriculum"><div class="section-top"><h2><?=h(t('Nội dung khóa học'))?></h2><span class="muted"><?=count($lessons)?> <?=h(t('bài học'))?></span></div>
                    <?php foreach($chapters as $chapter): $chapterLessons=array_values(array_filter($lessons,fn($lesson)=>(int)$lesson['chapter_id']===(int)$chapter['id'])); ?>
                        <details open><summary><?=h($chapter['title'])?><span><?=count($chapterLessons)?> <?=h(t('bài'))?></span></summary><ol><?php foreach($chapterLessons as $lesson): ?><li><?=h($lesson['title'])?></li><?php endforeach; ?></ol></details>
                    <?php endforeach; ?>
                </section>
                <?php renderInstructorProfile((int)$course['instructor_id']);?><section class="completion-note" id="completion"><h2><?=h(t('Học đủ bài. Luyện tập để ghi nhớ.'))?></h2><p><?=h(t('Đánh dấu tất cả bài học, sau đó đạt ít nhất 70% ở quiz cuối khóa để hoàn thành khóa học. Bạn có thể làm lại để luyện tập.'))?></p></section>
            </section>
            <aside class="panel enrollment-panel" aria-label="<?=h(t('Tham gia khóa học'))?>">
                <?php cover($course['cover_key'],$course); ?>
                <div class="enrollment-content"><p class="eyebrow"><?=h(t('BẮT ĐẦU HỌC HÔM NAY'))?></p><h2><?=h(t('Miễn phí'))?></h2><p class="muted"><?=h(t('Tự học theo thời gian của bạn.'))?></p>
                    <?php if(!$user): ?><a class="btn btn-primary w-100" href="<?=h(url('login'))?>"><?=h(t('Đăng nhập để tham gia'))?></a>
                    <?php elseif($enrollment): ?><a class="btn btn-primary w-100" href="<?=h(url('learn',['id'=>$id]))?>"><?=h(t('Tiếp tục học'))?></a>
                    <?php elseif($user['role']==='student'): echo formStart('enroll',['course_id'=>$id,'return_page'=>'detail']); ?><button class="btn btn-primary w-100"><?=h(t('Tham gia khóa học'))?></button></form>
                    <?php else: ?><p class="alert alert-light"><?=h(t('Học tập dành cho tài khoản Student.'))?></p><?php endif; ?>
                    <ul class="feature-list"><li><?=count($lessons)?> <?=h(t('bài học có ví dụ thực hành'))?></li><li><?=h(t('Quiz cuối khóa và giải thích đáp án'))?></li><li><?=h(t('Tiến độ được lưu trên server'))?></li></ul>
                </div>
            </aside>
        </div>
    </main><?php
}

function pageAuth(bool $register):void{?><main class="auth-layout container"><section class="auth-story"><p class="eyebrow"><?=h(t('STUDYMATE / HỌC MỖI NGÀY'))?></p><h1><?=h(t('Bước tiếp theo'))?><br><?=h(t('bắt đầu từ đây.'))?></h1><p><?=h(t('Đọc bài, luyện tập và xem lại tiến độ. Mỗi bước nhỏ đều giúp bạn tiến xa hơn.'))?></p><?php cover('web');?></section><section class="panel auth-panel"><h2><?=$register?t('Tạo tài khoản'):t('Chào mừng trở lại')?></h2><p class="muted"><?=$register?t('Tạo tài khoản Student để bắt đầu học.'):t('Đăng nhập để tiếp tục hành trình.')?></p><?=formStart($register?'register':'login',['return_page'=>$register?'register':'login'])?><?php if($register)field('name',t('Họ và tên'),'', 'text','required minlength="2" maxlength="80" autocomplete="name"');field('email',t('Email'),'','email','required maxlength="254" autocomplete="email"');field('password',t('Mật khẩu'),'','password','required autocomplete="'.($register?'new-password':'current-password').'"'.($register?' minlength="10"':''));if($register)field('password_confirmation',t('Xác nhận mật khẩu'),'','password','required autocomplete="new-password"');?><button class="btn btn-primary w-100"><?=$register?t('Tạo tài khoản'):t('Đăng nhập')?></button></form><p class="mt-3 small"><a href="<?=h(url($register?'login':'register'))?>"><?=$register?t('Đã có tài khoản? Đăng nhập'):t('Chưa có tài khoản? Đăng ký')?></a></p><div class="demo-accounts"><strong><?=h(t('Tài khoản seed để kiểm tra'))?></strong><p>student1@studymate.test<br>instructor1@studymate.test<br>admin@studymate.test</p><p class="small"><?=h(t('Mật khẩu được cấu hình trong config.php khi chạy setup. Không dùng tài khoản cá nhân.'))?></p></div></section></main><?php }

function pageLearn(int $id):void {
    $enrollment=enrolled($id);$course=getCourse($id);$user=requireUser('student');$lessons=courseLessons($id);
    $done=array_map('intval',array_column(all('SELECT p.lesson_id FROM lesson_progress p JOIN lessons l ON l.id=p.lesson_id JOIN chapters ch ON ch.id=l.chapter_id WHERE p.user_id=? AND ch.course_id=?',[$user['id'],$id]),'lesson_id'));
    $default=null;foreach($lessons as $item)if(!in_array((int)$item['id'],$done,true)){$default=$item;break;}
    $lid=(int)($_GET['lesson']??$enrollment['last_lesson_id']??$default['id']??$lessons[0]['id']??0);
    $lesson=lessonCourse($lid);if((int)$lesson['course_id']!==$id)reject(t('Bài học không thuộc khóa.'),404);
    $progress=progressFor((int)$user['id'],$id);
    $lessonNumber=array_search($lid,array_map('intval',array_column($lessons,'id')),true)+1;
    ?><main class="container wide learning-page">
        <div class="learning-top"><div><a class="breadcrumb-link" href="<?=h(url('student'))?>"><?=h(t('Khóa học của tôi'))?></a><p class="learn-course-name"><?=h($course['title'])?></p></div><div class="learning-shortcuts"><a href="#course-outline"><?=h(t('Danh sách bài học'))?></a><a href="#study-assistant"><?=h(t('Hỏi trợ lý AI'))?></a><button class="btn btn-outline-secondary" id="study-space-toggle" type="button" aria-controls="study-space" aria-expanded="false" hidden><?=h(ut('Không gian học tập'))?></button></div></div>
        <div class="learning-grid"><div class="reading-column">
            <section class="panel lesson-panel" id="lesson-reading"><p class="lesson-position"><?=h(t('BÀI'))?> <?=$lessonNumber?> / <?=count($lessons)?> · <?=h($lessons[$lessonNumber-1]['chapter_title'])?></p><?php lessonViewer($lesson); ?>
                <div class="lesson-actions"><?=formStart('complete',['lesson_id'=>$lid,'course_id'=>$id,'return_page'=>'learn'])?><button class="btn btn-primary" <?=in_array($lid,$done,true)?'disabled':''?>><?=in_array($lid,$done,true)?t('✓ Đã hoàn thành bài'):t('Đánh dấu hoàn thành')?></button></form><?php if(isset($lessons[$lessonNumber])): $next=$lessons[$lessonNumber]; echo formStart('last_lesson',['course_id'=>$id,'lesson_id'=>$next['id'],'return_page'=>'learn']); ?><button class="btn btn-outline-secondary"><?=h(t('Bài tiếp theo'))?> <?=uiIcon('arrow')?></button></form><?php else: ?><a class="btn btn-outline-secondary" href="<?=h(url('quiz',['id'=>$id]))?>"><?=h(t('Đến quiz cuối khóa'))?></a><?php endif; ?></div>
                <p class="small muted mt-3"><?=h(t('Tiến độ được ghi khi bạn đánh dấu hoàn thành bài.'))?></p>
            </section>
            <?php require_once __DIR__.'/lesson_notes_view.php';renderLessonNotes((int)$user['id'],$course,$lesson); ?>
            <?php renderAiPanel($lesson,$lid,$id); ?>
            </div>
            <div class="learning-rail"><?php require_once __DIR__.'/study_space_view.php';renderStudySpace((int)$user['id'],$id); ?><details class="lesson-list-wrap" open><summary><?=h(t('Danh sách bài học'))?> <span><?=$progress['done']?> / <?=$progress['total']?></span></summary><aside class="panel outline" id="course-outline" aria-labelledby="outline-title"><h2 id="outline-title"><?=h(t('Nội dung khóa học'))?></h2><p class="small muted"><?=$progress['done']?> / <?=$progress['total']?> <?=h(t('bài đã hoàn thành'))?></p><?php progressBar($progress); ?>
                <?php foreach(all('SELECT * FROM chapters WHERE course_id=? ORDER BY position,id',[$id]) as $chapter): ?><h3><?=h($chapter['title'])?></h3>
                    <?php foreach($lessons as $item)if((int)$item['chapter_id']===(int)$chapter['id']): echo formStart('last_lesson',['course_id'=>$id,'lesson_id'=>$item['id'],'return_page'=>'learn']); ?><button class="lesson-link <?=$lid===(int)$item['id']?'active':''?>" <?=$lid===(int)$item['id']?'aria-current="step"':''?>><span class="lesson-marker" aria-hidden="true"><?=in_array((int)$item['id'],$done,true)?'✓':'○'?></span><span><?=h($item['title'])?><?php if(in_array((int)$item['id'],$done,true)): ?><span class="visually-hidden"> <?=h(t('— Đã hoàn thành'))?></span><?php endif; ?></span></button></form><?php endif; ?>
                <?php endforeach; ?>
                <a class="btn btn-outline-secondary outline-quiz" href="<?=h(url('quiz',['id'=>$id]))?>"><?=h(t('Quiz cuối khóa'))?></a><?php if($progress['percent']<100): ?><p class="small muted outline-note"><?=h(t('Hoàn thành tất cả bài học để mở quiz.'))?></p><?php endif; ?>
            </aside></details></div>

        </div>
    </main><?php
}

function pageQuiz(int $id):void{$e=readyForQuiz($id);$qs=questionsFor($id);if(count($qs)<5)reject(t('Quiz chưa hợp lệ.'),409);$_SESSION['quiz_uuid'][$id]??=uuid();?><main class="container quiz-container"><a href="<?=h(url('learn',['id'=>$id]))?>"><?=h(t('Về bài học'))?></a><?php heading(t('LUYỆN TẬP CUỐI KHÓA'),t('Kiểm tra điều bạn đã học'),t('Đạt từ 70% để hoàn thành khóa; có thể làm lại, không giới hạn thời gian.'));?><p class="quiz-note"><?=h(t('Câu bỏ trống được tính sai. Refresh/rời trang có thể mất lựa chọn chưa nộp.'))?></p><?=formStart('submit_quiz',['course_id'=>$id,'request_id'=>$_SESSION['quiz_uuid'][$id],'return_page'=>'quiz'])?><?php foreach($qs as $i=>$q):?><fieldset class="panel question"><legend><?=h(t('Câu'))?> <?=$i+1?>. <?=h($q['prompt'])?></legend><?php foreach($q['answers'] as $j=>$a):?><label class="answer-option"><input type="radio" name="answers[<?=$q['id']?>]" value="<?=$a['id']?>"><span class="answer-letter"><?=chr(65+$j)?></span><?=h($a['text'])?></label><?php endforeach?></fieldset><?php endforeach;echo confirmField();?><button class="btn btn-primary btn-lg"><?=h(t('Nộp bài'))?></button></form><section class="panel mt-4"><h3><?=h(t('Lịch sử luyện tập'))?></h3><?php $history=all('SELECT a.* FROM quiz_attempts a JOIN quizzes q ON q.id=a.quiz_id WHERE a.user_id=? AND q.course_id=? ORDER BY a.submitted_at DESC,a.id DESC',[$e['user_id'],$id]);foreach($history as $a):?><p><a href="<?=h(url('result',['id'=>$a['id']]))?>"><?=h($a['score_percent'])?>% · <?=$a['passed']?t('Đạt'):t('Chưa đạt')?> · <?=h((new DateTimeImmutable($a['submitted_at'],new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Asia/Ho_Chi_Minh'))->format('d/m/Y H:i'))?></a></p><?php endforeach;if(!$history)echo '<p class="muted">'.h(t('Chưa có lần nộp.')).'</p>';?></section></main><?php }
function pageResult(int $id):void{$u=requireUser('student');$a=one('SELECT a.*,q.course_id FROM quiz_attempts a JOIN quizzes q ON q.id=a.quiz_id WHERE a.id=? AND a.user_id=?',[$id,$u['id']]);if(!$a)reject(t('Không tìm thấy kết quả.'),404);?><main class="container quiz-container"><section class="result-hero <?=$a['passed']?'passed':''?>"><p class="eyebrow"><?=h(t('KẾT QUẢ LUYỆN TẬP'))?></p><h1><?=$a['passed']?t('Bạn đã hoàn thành thử thách!'):t('Thêm một lần luyện tập nhé.')?></h1><div class="score"><?=h($a['score_percent'])?><small>%</small></div><p><?=h(t('Đúng'))?> <?=$a['correct_count']?>/<?=$a['total_questions']?> <?=h(t('câu ·'))?> <?=$a['passed']?t('Đạt'):t('Chưa đạt')?> <?=h(t('· Ngưỡng 70%'))?></p><div class="flex gap center"><a class="btn btn-primary" href="<?=h(url('quiz',['id'=>$a['course_id']]))?>"><?=h(t('Làm lại quiz'))?></a><a class="btn btn-outline-secondary" href="<?=h(url('student'))?>"><?=h(t('Khóa học của tôi'))?></a></div></section><h2 class="section-heading"><?=h(t('Xem lại từng câu'))?></h2><?php foreach(all('SELECT qa.*,q.prompt,q.explanation,q.position,selected.text selected_text,correct.text correct_text FROM quiz_answers qa JOIN questions q ON q.id=qa.question_id LEFT JOIN answers selected ON selected.id=qa.answer_id JOIN answers correct ON correct.question_id=q.id AND correct.is_correct=1 WHERE qa.attempt_id=? ORDER BY q.position,q.id',[$id]) as $answer):?><section class="panel review-question"><h3><?=h(t('Câu'))?> <?=$answer['position']?>. <?=h($answer['prompt'])?></h3><span class="badge <?=$answer['is_correct']?'text-bg-success':'text-bg-danger'?>"><?=$answer['is_correct']?t('Đúng'):($answer['answer_id']?t('Chưa đúng'):t('Bỏ trống'))?></span><p class="mt-3"><?=h(t('Bạn chọn:'))?> <?=h($answer['selected_text']??t('Chưa chọn'))?></p><p><?=h(t('Đáp án đúng:'))?> <strong><?=h($answer['correct_text'])?></strong></p><p class="muted"><?=h($answer['explanation'])?></p></section><?php endforeach?></main><?php }
function pageProfile():void{renderEditableProfile();}

function pagination(array $list):void{
 $params=$_GET;unset($params['p']);$page=(string)($params['page']??'home');unset($params['page']);
 echo '<nav class="pager" aria-label="'.h(t('Phân trang')).'">';
 if($list['page']>1)echo '<a class="btn btn-outline-secondary" rel="prev" href="'.h(url($page,$params+['p'=>$list['page']-1])).'">'.h(t('Trước')).'</a>';
 echo '<span>'.h(countedLabel(t('Trang {page} / {last} · {total} kết quả',['page'=>$list['page'],'last'=>$list['last'],'total'=>$list['total']]),$list['total'])).'</span>';
 if($list['page']<$list['last'])echo '<a class="btn btn-outline-secondary" rel="next" href="'.h(url($page,$params+['p'=>$list['page']+1])).'">'.h(t('Sau')).'</a>';
 echo '</nav>';
}

require_once __DIR__.'/glass_views.php';
