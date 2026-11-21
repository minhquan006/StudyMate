<?php
function heading(string $eyebrow,string $title,string $description=''):void{echo '<div class="page-heading"><div><p class="eyebrow">'.h($eyebrow).'</p><h1>'.h($title).'</h1><p class="muted">'.h($description).'</p></div></div>';}
function uiIcon(string $name):string {
    $paths=['book'=>'M12 5v15M12 5C8 2 4 3 2 4v15c3-1 6-1 10 1 4-2 7-2 10-1V4c-2-1-6-2-10 1Z','search'=>'m21 21-5-5M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z','arrow'=>'M4 12h16m-6-6 6 6-6 6','check'=>'m5 12 4 4L19 6'];
    return '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="'.($paths[$name]??$paths['book']).'"/></svg>';
}
function cover(string $key):void {
    $theme=in_array($key,['web','programming','database'],true)?$key:'default';
    ?><div class="cover <?=h($theme)?>" aria-hidden="true">
        <?php if($theme==='web'): ?><span class="cover-caption">HTML / CSS</span><div class="mini-browser"><div class="browser-bar"><i></i><i></i><i></i><span>index.html</span></div><div class="browser-page"><div class="browser-side"></div><div class="browser-content"><b>Build for the web.</b><span></span><span></span><div class="browser-blocks"><i></i><i></i><i></i></div></div></div></div>
        <?php elseif($theme==='programming'): ?><span class="cover-caption">JAVASCRIPT</span><div class="code-preview"><span class="code-line">const learn = () =&gt; {</span><span class="code-line">&nbsp; practice();</span><span class="code-line">&nbsp; return progress;</span><span class="code-line">};</span></div><strong class="language-stamp">JS</strong>
        <?php elseif($theme==='database'): ?><span class="cover-caption">CƠ SỞ DỮ LIỆU</span><div class="table-preview"><div><b>id</b><b>course</b><b>status</b></div><div><span>01</span><span>HTML &amp; CSS</span><span>✓</span></div><div><span>02</span><span>JavaScript</span><span>✓</span></div><div><span>03</span><span>SQL</span><span>✓</span></div></div>
        <?php else: ?><span class="cover-caption">STUDYMATE</span><div class="book-preview"><?=uiIcon('book')?><span>Học. Thực hành.<br>Tiến bộ.</span></div><?php endif; ?>
    </div><?php
}

function statusName(string $s):string{return ['draft'=>'Bản nháp','published'=>'Đã xuất bản','archived'=>'Đã lưu trữ'][$s]??$s;}
function statusBadge(string $s):string{return '<span class="status '.h($s).'">'.h(statusName($s)).'</span>';}
function progressBar(array $p):void{echo '<div class="progress-meta"><span>Tiến độ bài học</span><strong>'.h($p['percent']).'%</strong></div><div class="progress" role="progressbar" aria-valuenow="'.h($p['percent']).'" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar" style="width:'.h($p['percent']).'%"></div></div>';}
function statCard(string $label,$value,string $sub=''):void{echo '<div class="stat"><span>'.h($label).'</span><strong>'.h($value).'</strong><small>'.h($sub).'</small></div>';}
function emptyState(string $title,string $detail=''):void{echo '<div class="empty"><span class="empty-icon">□</span><h3>'.h($title).'</h3><p>'.h($detail).'</p></div>';}
function courseCard(array $c,?array $e=null):void {
    ?><article class="course-card"><a href="<?=h(url($e?'learn':'detail',['id'=>$c['id']]))?>">
        <?php cover($c['cover_key']); ?>
        <div class="card-content">
            <p class="eyebrow"><?=h($c['category_name'])?></p>
            <h3><?=h($c['title'])?></h3>
            <p class="muted clamp"><?=h($c['description'])?></p>
            <?php if($e): $progress=isset($e['total'])?rowProgress($e):progressFor((int)$e['user_id'],(int)$c['id']); ?>
                <div class="card-progress"><?php progressBar($progress); ?></div>
                <p class="small muted"><?=$e['completed_at']?'Đã hoàn thành khóa học':($progress['percent']==100?'Đã học đủ bài · tiếp tục quiz':'Đang học')?><?=$progress['best']!==null?' · Điểm tốt nhất '.h($progress['best']).'%':''?></p>
            <?php endif; ?>
            <div class="card-meta"><span><?=h($c['instructor_name'])?></span><strong>Miễn phí</strong></div>
            <span class="card-action"><?=$e?'Tiếp tục học':'Xem khóa học'?></span>
        </div>
    </a></article><?php
}

function field(string $name,string $label,string $value='',string $type='text',string $attrs=''):void{static $counter=0;$fieldId=$name.'-'.(++$counter);echo '<label for="'.h($fieldId).'">'.h($label).'<input class="form-control" id="'.h($fieldId).'" name="'.h($name).'" type="'.h($type).'" value="'.h($value).'" '.$attrs.'></label>';}
function area(string $name,string $label,string $value='',string $attrs=''):void{echo '<label>'.h($label).'<textarea class="form-control" name="'.h($name).'" '.$attrs.'>'.h($value).'</textarea></label>';}
function markdown(string $body):string{$out='';$code=false;$buf='';foreach(explode("\n",str_replace("\r",'',$body)) as $line){if(str_starts_with($line,'```')){if($code){$out.='<pre tabindex="0" role="region" aria-label="Đoạn mã"><code>'.h($buf).'</code></pre>';$buf='';}$code=!$code;continue;}if($code){$buf.=$line."\n";continue;}if(preg_match('/^(#{1,3})\s+(.+)$/',$line,$m)){$n=strlen($m[1]);$out.="<h$n>".h($m[2])."</h$n>";}elseif(trim($line)!=='')$out.='<p>'.h($line).'</p>'; }if($code)$out.='<pre tabindex="0" role="region" aria-label="Đoạn mã"><code>'.h($buf).'</code></pre>';return $out;}
function lessonViewer(array $l):void{echo '<article class="lesson-text">'.markdown($l['body']);if($l['youtube_video_id'])echo '<iframe title="Video bài học" src="https://www.youtube-nocookie.com/embed/'.h($l['youtube_video_id']).'" allowfullscreen></iframe>';echo '</article>';}
function pageHome():void {
    $subject=filter_var($_GET['subject']??0,FILTER_VALIDATE_INT,['options'=>['min_range'=>0]])?:0;
    $where="c.status='published'";$params=[];
    if($subject){$where.=' AND c.category_id=?';$params[]=$subject;}
    $courses=all('SELECT c.*,g.name category_name,u.name instructor_name FROM courses c JOIN categories g ON g.id=c.category_id JOIN users u ON u.id=c.instructor_id WHERE '.$where.' ORDER BY c.created_at DESC,c.id DESC LIMIT 8',$params);
    $categories=all("SELECT g.id,g.name,COUNT(c.id) total FROM categories g LEFT JOIN courses c ON c.category_id=g.id AND c.status='published' GROUP BY g.id,g.name ORDER BY g.name");
    $user=currentUser();$themes=['Lập trình web'=>'web','Lập trình'=>'programming','Cơ sở dữ liệu'=>'database'];
    ?><main class="home-page">
        <section class="market-banner" aria-labelledby="welcome-title">
            <div class="banner-copy"><p class="banner-eyebrow">HỌC CÔNG NGHỆ · MỞ TƯƠNG LAI</p><h1 id="welcome-title">Bắt đầu học.<br> Mở thêm cơ hội.</h1><p>Học kiến thức nền tảng, thử sức với bài tập và tiến bộ qua từng bài học. Bắt đầu miễn phí, theo thời gian của bạn.</p><a class="btn btn-primary" href="<?=h(url($user?$user['role']:'register'))?>"><?=$user?'Tiếp tục việc học':'Đăng ký miễn phí'?></a></div>
            <section class="hero-gallery" aria-label="Khám phá thế giới công nghệ" aria-roledescription="bộ ảnh" data-hero-gallery>
                <div class="hero-viewport"><div class="hero-track"><?php foreach([['it-code','Lập trình','Từ dòng code đầu tiên đến ý tưởng của bạn.','Minh họa laptop với mã JavaScript và biểu tượng lập trình.'],['it-web','Phát triển web','Tạo nên những trang web theo cách của bạn.','Minh họa giao diện website trên máy tính và điện thoại.'],['it-data','Cơ sở dữ liệu','Kết nối dữ liệu. Hiểu cách mọi thứ hoạt động.','Minh họa máy tính truy vấn SQL kết nối cơ sở dữ liệu.']] as $index=>[$image,$title,$caption,$alt]): ?><figure class="hero-slide" aria-label="<?=$index+1?> / 3 · <?=h($title)?>" <?=$index?'aria-hidden="true" inert':''?>><img src="/assets/images/<?=$image?>.svg" alt="<?=h($alt)?>" width="800" height="520" <?=$index?'loading="lazy"':'fetchpriority="high"'?>><figcaption><strong><?=h($title)?></strong><span><?=h($caption)?></span></figcaption></figure><?php endforeach; ?></div></div>
                <div class="hero-controls" hidden><button type="button" data-hero-prev aria-label="Ảnh IT trước"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m14 6-6 6 6 6"/></svg></button><div class="hero-dots" role="group" aria-label="Chọn ảnh IT"><?php foreach(['Lập trình','Phát triển web','Cơ sở dữ liệu'] as $index=>$label): ?><button type="button" data-hero-index="<?=$index?>" aria-label="Xem ảnh <?=h($label)?>" aria-pressed="<?=$index?'false':'true'?>"><span></span></button><?php endforeach; ?></div><button type="button" data-hero-next aria-label="Ảnh IT tiếp theo"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m10 6 6 6-6 6"/></svg></button><button type="button" class="hero-motion" data-hero-motion aria-label="Tạm dừng chuyển ảnh"><span data-hero-motion-icon aria-hidden="true">Ⅱ</span></button></div>
                <p class="visually-hidden" data-hero-status role="status" aria-live="polite"></p>
            </section>
        </section>
        <section class="subject-section container" aria-labelledby="subject-title"><div class="subject-intro"><h2 id="subject-title">Kiến thức cho công việc và cuộc sống</h2><p class="muted">Chọn một lĩnh vực bạn quan tâm. Xây nền tảng từ những điều cơ bản.</p><a class="text-link" href="<?=h(url('courses'))?>">Khám phá tất cả →</a></div><div class="subject-grid" tabindex="0" role="region" aria-label="Các lĩnh vực học tập"><?php foreach($categories as $category): ?><a class="subject-tile" href="<?=h(url('courses',['category'=>$category['id']]))?>"><?php cover($themes[$category['name']]??'default'); ?><span class="subject-tile-caption"><span><strong><?=h($category['name'])?></strong><small><?=$category['total']?> khóa học miễn phí</small></span><?=uiIcon('arrow')?></span></a><?php endforeach; ?></div></section>
        <section class="container home-courses" id="discover-courses" aria-labelledby="home-courses-title"><div class="section-top"><div><h2 id="home-courses-title">Một lựa chọn phù hợp cho việc học của bạn</h2><p class="muted">Từ HTML, CSS đến JavaScript và dữ liệu. Tìm chủ đề bạn muốn học tiếp.</p></div></div><nav class="course-subjects" aria-label="Lọc khóa học trên trang chủ"><a href="<?=h(url('home'))?>#discover-courses" <?=$subject===0?'aria-current="page"':''?>>Tất cả</a><?php foreach($categories as $category): ?><a href="<?=h(url('home',['subject'=>$category['id']]))?>#discover-courses" <?=$subject===(int)$category['id']?'aria-current="page"':''?>><?=h($category['name'])?></a><?php endforeach; ?></nav><div class="home-course-shelf"><div class="course-grid"><?php foreach($courses as $course)courseCard($course); ?></div><?php if(!$courses): emptyState('Khóa học đang được chuẩn bị','Bạn có thể chọn một lĩnh vực khác hoặc xem tất cả khóa học.'); endif; ?><a class="btn btn-outline-secondary" href="<?=h(url('courses',$subject?['category'=>$subject]:[]))?>">Xem thêm khóa học <?=uiIcon('arrow')?></a></div></section>
        <section class="learning-benefits"><div class="container"><div><?=uiIcon('book')?><p><strong>Học theo thời gian của bạn</strong><span>Nội dung chia thành chương và bài, dễ theo dõi.</span></p></div><div><?=uiIcon('check')?><p><strong>Thực hành để hiểu sâu hơn</strong><span>Ví dụ, bài tập và quiz có giải thích đáp án.</span></p></div><div><?=uiIcon('arrow')?><p><strong>Tiếp tục từ nơi đã dừng</strong><span>Tiến độ bài học được lưu trong tài khoản.</span></p></div></div></section>
        <section class="learning-method container" aria-labelledby="method-title"><div><h2 id="method-title">Một khóa học.<br>Nhiều điều bạn có thể làm.</h2><p class="muted">Dành thời gian cho một kiến thức mới hôm nay.</p><a class="btn btn-primary" href="<?=h(url('courses'))?>">Tìm khóa học của bạn</a></div><ol><li><span>1</span><div><h3>Khám phá điều bạn muốn học</h3><p>Xem giới thiệu, nội dung chương và các bài trước khi tham gia.</p></div></li><li><span>2</span><div><h3>Học và thực hành từng bài</h3><p>Theo dõi tiến độ; Hỏi hoặc Tóm tắt ngay trong màn hình học.</p></div></li><li><span>3</span><div><h3>Ôn tập qua quiz cuối khóa</h3><p>Xem lại câu trả lời, hiểu đáp án và luyện tập lại khi cần.</p></div></li></ol></section>
    </main><?php
}

function pageCourses():void {
    $q=searchQuery();$cat=(int)($_GET['category']??0);$page=max(1,(int)($_GET['p']??1));
    $where="c.status='published'";$params=[];
    if($q!==''){$where.=" AND LOWER(c.title) LIKE LOWER(?) ESCAPE '!'";$params[]=searchPattern($q);}
    if($cat){$where.=' AND c.category_id=?';$params[]=$cat;}
    $count=(int)val('SELECT COUNT(*) FROM courses c WHERE '.$where,$params);
    $last=max(1,(int)ceil($count/12));$page=min($page,$last);
    $rows=all('SELECT c.*,g.name category_name,u.name instructor_name FROM courses c JOIN categories g ON g.id=c.category_id JOIN users u ON u.id=c.instructor_id WHERE '.$where.' ORDER BY c.created_at DESC,c.id DESC LIMIT 12 OFFSET '.(($page-1)*12),$params);
    ?><main class="container catalog-page">
        <nav class="breadcrumb-link" aria-label="Đường dẫn"><a href="<?=h(url('home'))?>">Trang chủ</a><span aria-hidden="true"> / </span><span>Khóa học</span></nav>
        <?php heading('THƯ VIỆN STUDYMATE','Tìm khóa học dành cho bạn','Kiến thức nền tảng, ví dụ cụ thể và bài luyện tập. Tất cả đều miễn phí.'); ?>
        <div class="catalog-layout"><aside class="catalog-sidebar" aria-labelledby="filter-title"><h2 id="filter-title">Bộ lọc khóa học</h2><form class="catalog-filter" method="get" action="/index.php">
            <?=hidden('page','courses')?>
            <label class="search-field">Tìm theo tên khóa học<input class="form-control" name="q" placeholder="Ví dụ: JavaScript" value="<?=h($q)?>" maxlength="100"></label>
            <label>Danh mục<select class="form-select" name="category"><option value="">Tất cả danh mục</option><?php foreach(all('SELECT * FROM categories ORDER BY name') as $category): ?><option value="<?=$category['id']?>" <?=$cat===(int)$category['id']?'selected':''?>><?=h($category['name'])?></option><?php endforeach; ?></select></label>
            <button class="btn btn-primary w-100" type="submit">Tìm khóa học</button>
            <?php if($q!==''||$cat): ?><a class="filter-reset" href="<?=h(url('courses'))?>">Xóa bộ lọc</a><?php endif; ?>
        </form><div class="catalog-help"><h3>Học theo cách của bạn</h3><p>Đọc bài bất cứ lúc nào. Tiến độ được lưu khi bạn đánh dấu hoàn thành.</p><p>Quiz cuối khóa giúp bạn ôn tập và xem lại đáp án.</p></div></aside>
        <section class="catalog-results" aria-label="Kết quả khóa học"><div class="results-toolbar"><p><strong><?=$count?></strong> khóa học<?=$q!==''?' cho “'.h($q).'”':' phù hợp'?></p><span>Mới nhất trước</span></div><div class="course-list"><?php foreach($rows as $course)courseCard($course); ?></div>
        <?php if(!$rows): emptyState('Chưa tìm thấy khóa học phù hợp','Thử một từ khóa ngắn hơn hoặc chọn tất cả danh mục.'); ?><div class="empty-action"><a class="btn btn-primary" href="<?=h(url('courses'))?>">Xem tất cả khóa học</a></div><?php endif; ?>
        <?php if($last>1): ?><nav class="pager" aria-label="Phân trang khóa học"><?php if($page>1): ?><a class="btn btn-outline-secondary" rel="prev" href="<?=h(url('courses',['q'=>$q,'category'=>$cat,'p'=>$page-1]))?>">Trước</a><?php endif; ?><span>Trang <?=$page?> / <?=$last?></span><?php if($page<$last): ?><a class="btn btn-outline-secondary" rel="next" href="<?=h(url('courses',['q'=>$q,'category'=>$cat,'p'=>$page+1]))?>">Sau</a><?php endif; ?></nav><?php endif; ?></section></div>
    </main><?php
}

function pageDetail(int $id):void {
    $course=getCourse($id);
    if($course['status']!=='published')reject('Khóa học không công khai.',404);
    $user=currentUser();
    $chapters=all('SELECT * FROM chapters WHERE course_id=? ORDER BY position,id',[$id]);
    $lessons=courseLessons($id);
    $enrollment=$user&&$user['role']==='student'?one('SELECT id FROM enrollments WHERE user_id=? AND course_id=?',[$user['id'],$id]):null;
    ?><main class="course-detail">
        <header class="detail-masthead"><div class="container"><a class="breadcrumb-link" href="<?=h(url('courses'))?>">Khóa học / <?=h($course['category_name'])?></a>
            <div class="detail-intro"><p class="eyebrow"><?=h($course['category_name'])?></p><h1><?=h($course['title'])?></h1><p class="lead muted course-description"><?=h($course['description'])?></p><p class="instructor-line">Biên soạn bởi <strong><?=h($course['instructor_name'])?></strong></p><div class="course-facts"><span><?=count($chapters)?> chương</span><span><?=count($lessons)?> bài học</span><span>Quiz cuối khóa</span></div></div>
        </div></header>
        <div class="container detail-grid">
            <section class="detail-content"><nav class="detail-nav" aria-label="Nội dung giới thiệu khóa học"><a href="#course-overview">Tổng quan</a><a href="#curriculum">Nội dung khóa học</a><a href="#completion">Hoàn thành khóa</a></nav>
                <?php if($lessons): ?><section class="panel learning-topics" id="course-overview"><h2>Bạn sẽ tìm hiểu</h2><ul><?php foreach(array_slice($lessons,0,6) as $lesson): ?><li><?=h($lesson['title'])?></li><?php endforeach; ?></ul></section><?php endif; ?>
                <section class="panel curriculum" id="curriculum"><div class="section-top"><h2>Nội dung khóa học</h2><span class="muted"><?=count($lessons)?> bài học</span></div>
                    <?php foreach($chapters as $chapter): $chapterLessons=array_values(array_filter($lessons,fn($lesson)=>(int)$lesson['chapter_id']===(int)$chapter['id'])); ?>
                        <details open><summary><?=h($chapter['title'])?><span><?=count($chapterLessons)?> bài</span></summary><ol><?php foreach($chapterLessons as $lesson): ?><li><?=h($lesson['title'])?></li><?php endforeach; ?></ol></details>
                    <?php endforeach; ?>
                </section>
                <section class="completion-note" id="completion"><h2>Học đủ bài. Luyện tập để ghi nhớ.</h2><p>Đánh dấu tất cả bài học, sau đó đạt ít nhất 70% ở quiz cuối khóa để hoàn thành khóa học. Bạn có thể làm lại để luyện tập.</p></section>
            </section>
            <aside class="panel enrollment-panel" aria-label="Tham gia khóa học">
                <?php cover($course['cover_key']); ?>
                <div class="enrollment-content"><p class="eyebrow">BẮT ĐẦU HỌC HÔM NAY</p><h2>Miễn phí</h2><p class="muted">Tự học theo thời gian của bạn.</p>
                    <?php if(!$user): ?><a class="btn btn-primary w-100" href="<?=h(url('login'))?>">Đăng nhập để tham gia</a>
                    <?php elseif($enrollment): ?><a class="btn btn-primary w-100" href="<?=h(url('learn',['id'=>$id]))?>">Tiếp tục học</a>
                    <?php elseif($user['role']==='student'): echo formStart('enroll',['course_id'=>$id,'return_page'=>'detail']); ?><button class="btn btn-primary w-100">Tham gia khóa học</button></form>
                    <?php else: ?><p class="alert alert-light">Học tập dành cho tài khoản Student.</p><?php endif; ?>
                    <ul class="feature-list"><li><?=count($lessons)?> bài học có ví dụ thực hành</li><li>Quiz cuối khóa và giải thích đáp án</li><li>Tiến độ được lưu trên server</li></ul>
                </div>
            </aside>
        </div>
    </main><?php
}

function pageAuth(bool $register):void{?><main class="auth-layout container"><section class="auth-story"><p class="eyebrow">STUDYMATE / HỌC MỖI NGÀY</p><h1>Bước tiếp theo<br>bắt đầu từ đây.</h1><p>Đọc bài, luyện tập và xem lại tiến độ. Mỗi bước nhỏ đều giúp bạn tiến xa hơn.</p><?php cover('web');?></section><section class="panel auth-panel"><h2><?=$register?'Tạo tài khoản':'Chào mừng trở lại'?></h2><p class="muted"><?=$register?'Tạo tài khoản Student để bắt đầu học.':'Đăng nhập để tiếp tục hành trình.'?></p><?=formStart($register?'register':'login',['return_page'=>$register?'register':'login'])?><?php if($register)field('name','Họ và tên','', 'text','required minlength="2" maxlength="80" autocomplete="name"');field('email','Email','','email','required maxlength="254" autocomplete="email"');field('password','Mật khẩu','','password','required autocomplete="'.($register?'new-password':'current-password').'"'.($register?' minlength="10"':''));if($register)field('password_confirmation','Xác nhận mật khẩu','','password','required autocomplete="new-password"');?><button class="btn btn-primary w-100"><?=$register?'Tạo tài khoản':'Đăng nhập'?></button></form><p class="mt-3 small"><a href="<?=h(url($register?'login':'register'))?>"><?=$register?'Đã có tài khoản? Đăng nhập':'Chưa có tài khoản? Đăng ký'?></a></p><div class="demo-accounts"><strong>Tài khoản seed để kiểm tra</strong><p>student1@studymate.test<br>instructor1@studymate.test<br>admin@studymate.test</p><p class="small">Mật khẩu được cấu hình trong config.php khi chạy setup. Không dùng tài khoản cá nhân.</p></div></section></main><?php }
function pageStudent():void{
 $u=requireUser('student');$q=searchQuery();
 $where="e.user_id=? AND c.title LIKE ? ESCAPE '!'";$params=[$u['id'],searchPattern($q)];
 $list=pageQuery('SELECT COUNT(*) FROM enrollments e JOIN courses c ON c.id=e.course_id WHERE '.$where,
 'SELECT c.*,g.name category_name,u.name instructor_name,e.user_id,e.completed_at,COALESCE(totals.total,0) total,COALESCE(done.done,0) done,scores.best FROM enrollments e JOIN courses c ON c.id=e.course_id JOIN categories g ON g.id=c.category_id JOIN users u ON u.id=c.instructor_id '.progressJoins().' WHERE '.$where.' ORDER BY e.created_at DESC,e.id DESC',$params);
 $stats=one('SELECT COUNT(*) total,SUM(CASE WHEN completed_at IS NOT NULL THEN 1 ELSE 0 END) completed FROM enrollments WHERE user_id=?',[$u['id']]);
 ?><main class="container student-workspace"><aside class="workspace-nav" aria-label="Không gian học tập"><p class="workspace-person"><?=h($u['name'])?><small>Không gian học tập</small></p><nav><a class="active" href="<?=h(url('student'))?>" aria-current="page"><?=uiIcon('book')?> Việc học của tôi</a><a href="<?=h(url('courses'))?>"><?=uiIcon('search')?> Khám phá khóa học</a><a href="<?=h(url('profile'))?>">Hồ sơ cá nhân</a></nav><p class="workspace-tip">Tiến độ của bạn được lưu sau mỗi lần đánh dấu hoàn thành bài học.</p></aside><div class="workspace-main">
 <?php heading('VIỆC HỌC CỦA TÔI','Chào mừng trở lại, '.$u['name'],'Tiếp tục từ nơi bạn đã dừng lại.'); ?>
 <div class="study-summary"><span><strong><?=(int)$stats['total']?></strong> khóa đã tham gia</span><span><strong><?=(int)($stats['completed']??0)?></strong> khóa đã hoàn thành</span><a href="<?=h(url('courses'))?>">Tìm khóa học mới →</a></div>
 <section aria-labelledby="my-courses-title"><div class="section-top"><h2 id="my-courses-title">Khóa học của tôi</h2></div><form class="filter-bar" method="get"><?=hidden('page','student')?><?php field('q','Tìm khóa đã tham gia',$q,'text','maxlength="100"');?><button class="btn btn-primary">Tìm</button></form><div class="course-grid"><?php foreach($list['rows'] as $e)courseCard($e,$e);?></div><?php if(!$list['rows'])emptyState('Không có khóa học phù hợp','Thử tên khác hoặc tham gia khóa học từ thư viện.');pagination($list);?></section></div></main><?php }

function pageLearn(int $id):void {
    $enrollment=enrolled($id);$course=getCourse($id);$user=requireUser('student');$lessons=courseLessons($id);
    $done=array_map('intval',array_column(all('SELECT p.lesson_id FROM lesson_progress p JOIN lessons l ON l.id=p.lesson_id JOIN chapters ch ON ch.id=l.chapter_id WHERE p.user_id=? AND ch.course_id=?',[$user['id'],$id]),'lesson_id'));
    $default=null;foreach($lessons as $item)if(!in_array((int)$item['id'],$done,true)){$default=$item;break;}
    $lid=(int)($_GET['lesson']??$enrollment['last_lesson_id']??$default['id']??$lessons[0]['id']??0);
    $lesson=lessonCourse($lid);if((int)$lesson['course_id']!==$id)reject('Bài học không thuộc khóa.',404);
    $progress=progressFor((int)$user['id'],$id);
    $lessonNumber=array_search($lid,array_map('intval',array_column($lessons,'id')),true)+1;
    ?><main class="container wide learning-page">
        <div class="learning-top"><div><a class="breadcrumb-link" href="<?=h(url('student'))?>">Khóa học của tôi</a><p class="learn-course-name"><?=h($course['title'])?></p></div><div class="learning-shortcuts"><a href="#course-outline">Danh sách bài học</a><a href="#study-assistant">Hỏi trợ lý AI</a><button class="btn btn-outline-secondary" id="study-space-toggle" type="button" aria-controls="study-space" aria-expanded="false" hidden>Không gian học tập</button></div></div>
        <div class="learning-grid"><div class="reading-column">
            <section class="panel lesson-panel" id="lesson-reading"><p class="lesson-position">BÀI <?=$lessonNumber?> / <?=count($lessons)?> · <?=h($lessons[$lessonNumber-1]['chapter_title'])?></p><?php lessonViewer($lesson); ?>
                <div class="lesson-actions"><?=formStart('complete',['lesson_id'=>$lid,'course_id'=>$id,'return_page'=>'learn'])?><button class="btn btn-primary" <?=in_array($lid,$done,true)?'disabled':''?>><?=in_array($lid,$done,true)?'✓ Đã hoàn thành bài':'Đánh dấu hoàn thành'?></button></form><?php if(isset($lessons[$lessonNumber])): $next=$lessons[$lessonNumber]; echo formStart('last_lesson',['course_id'=>$id,'lesson_id'=>$next['id'],'return_page'=>'learn']); ?><button class="btn btn-outline-secondary">Bài tiếp theo <?=uiIcon('arrow')?></button></form><?php else: ?><a class="btn btn-outline-secondary" href="<?=h(url('quiz',['id'=>$id]))?>">Đến quiz cuối khóa</a><?php endif; ?></div>
                <p class="small muted mt-3">Tiến độ được ghi khi bạn đánh dấu hoàn thành bài.</p>
            </section>
            <?php renderAiPanel($lesson,$lid,$id); ?>
            </div>
            <div class="learning-rail"><?php require_once __DIR__.'/study_space_view.php'; renderStudySpace((int)$user['id'],$id); ?><details class="lesson-list-wrap" open><summary>Danh sách bài học <span><?=$progress['done']?> / <?=$progress['total']?></span></summary><aside class="panel outline" id="course-outline" aria-labelledby="outline-title"><h2 id="outline-title">Nội dung khóa học</h2><p class="small muted"><?=$progress['done']?> / <?=$progress['total']?> bài đã hoàn thành</p><?php progressBar($progress); ?>
                <?php foreach(all('SELECT * FROM chapters WHERE course_id=? ORDER BY position,id',[$id]) as $chapter): ?><h3><?=h($chapter['title'])?></h3>
                    <?php foreach($lessons as $item)if((int)$item['chapter_id']===(int)$chapter['id']): echo formStart('last_lesson',['course_id'=>$id,'lesson_id'=>$item['id'],'return_page'=>'learn']); ?><button class="lesson-link <?=$lid===(int)$item['id']?'active':''?>" <?=$lid===(int)$item['id']?'aria-current="step"':''?>><span class="lesson-marker" aria-hidden="true"><?=in_array((int)$item['id'],$done,true)?'✓':'○'?></span><span><?=h($item['title'])?><?php if(in_array((int)$item['id'],$done,true)): ?><span class="visually-hidden"> — Đã hoàn thành</span><?php endif; ?></span></button></form><?php endif; ?>
                <?php endforeach; ?>
                <a class="btn btn-outline-secondary outline-quiz" href="<?=h(url('quiz',['id'=>$id]))?>">Quiz cuối khóa</a><?php if($progress['percent']<100): ?><p class="small muted outline-note">Hoàn thành tất cả bài học để mở quiz.</p><?php endif; ?>
            </aside></details></div>

        </div>
    </main><?php
}

function pageQuiz(int $id):void{$e=readyForQuiz($id);$qs=questionsFor($id);if(count($qs)<5)reject('Quiz chưa hợp lệ.',409);$_SESSION['quiz_uuid'][$id]??=uuid();?><main class="container quiz-container"><a href="<?=h(url('learn',['id'=>$id]))?>">Về bài học</a><?php heading('LUYỆN TẬP CUỐI KHÓA','Kiểm tra điều bạn đã học','Đạt từ 70% để hoàn thành khóa; có thể làm lại, không giới hạn thời gian.');?><p class="quiz-note">Câu bỏ trống được tính sai. Refresh/rời trang có thể mất lựa chọn chưa nộp.</p><?=formStart('submit_quiz',['course_id'=>$id,'request_id'=>$_SESSION['quiz_uuid'][$id],'return_page'=>'quiz'])?><?php foreach($qs as $i=>$q):?><fieldset class="panel question"><legend>Câu <?=$i+1?>. <?=h($q['prompt'])?></legend><?php foreach($q['answers'] as $j=>$a):?><label class="answer-option"><input type="radio" name="answers[<?=$q['id']?>]" value="<?=$a['id']?>"><span class="answer-letter"><?=chr(65+$j)?></span><?=h($a['text'])?></label><?php endforeach?></fieldset><?php endforeach;echo confirmField();?><button class="btn btn-primary btn-lg">Nộp bài</button></form><section class="panel mt-4"><h3>Lịch sử luyện tập</h3><?php $history=all('SELECT a.* FROM quiz_attempts a JOIN quizzes q ON q.id=a.quiz_id WHERE a.user_id=? AND q.course_id=? ORDER BY a.submitted_at DESC,a.id DESC',[$e['user_id'],$id]);foreach($history as $a):?><p><a href="<?=h(url('result',['id'=>$a['id']]))?>"><?=h($a['score_percent'])?>% · <?=$a['passed']?'Đạt':'Chưa đạt'?> · <?=h((new DateTimeImmutable($a['submitted_at'],new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Asia/Ho_Chi_Minh'))->format('d/m/Y H:i'))?></a></p><?php endforeach;if(!$history)echo '<p class="muted">Chưa có lần nộp.</p>';?></section></main><?php }
function pageResult(int $id):void{$u=requireUser('student');$a=one('SELECT a.*,q.course_id FROM quiz_attempts a JOIN quizzes q ON q.id=a.quiz_id WHERE a.id=? AND a.user_id=?',[$id,$u['id']]);if(!$a)reject('Không tìm thấy kết quả.',404);?><main class="container quiz-container"><section class="result-hero <?=$a['passed']?'passed':''?>"><p class="eyebrow">KẾT QUẢ LUYỆN TẬP</p><h1><?=$a['passed']?'Bạn đã hoàn thành thử thách!':'Thêm một lần luyện tập nhé.'?></h1><div class="score"><?=h($a['score_percent'])?><small>%</small></div><p>Đúng <?=$a['correct_count']?>/<?=$a['total_questions']?> câu · <?=$a['passed']?'Đạt':'Chưa đạt'?> · Ngưỡng 70%</p><div class="flex gap center"><a class="btn btn-primary" href="<?=h(url('quiz',['id'=>$a['course_id']]))?>">Làm lại quiz</a><a class="btn btn-outline-secondary" href="<?=h(url('student'))?>">Khóa học của tôi</a></div></section><h2 class="section-heading">Xem lại từng câu</h2><?php foreach(all('SELECT qa.*,q.prompt,q.explanation,q.position,selected.text selected_text,correct.text correct_text FROM quiz_answers qa JOIN questions q ON q.id=qa.question_id LEFT JOIN answers selected ON selected.id=qa.answer_id JOIN answers correct ON correct.question_id=q.id AND correct.is_correct=1 WHERE qa.attempt_id=? ORDER BY q.position,q.id',[$id]) as $answer):?><section class="panel review-question"><h3>Câu <?=$answer['position']?>. <?=h($answer['prompt'])?></h3><span class="badge <?=$answer['is_correct']?'text-bg-success':'text-bg-danger'?>"><?=$answer['is_correct']?'Đúng':($answer['answer_id']?'Chưa đúng':'Bỏ trống')?></span><p class="mt-3">Bạn chọn: <?=h($answer['selected_text']??'Chưa chọn')?></p><p>Đáp án đúng: <strong><?=h($answer['correct_text'])?></strong></p><p class="muted"><?=h($answer['explanation'])?></p></section><?php endforeach?></main><?php }
function pageProfile():void{$u=requireUser();?><main class="container"><?php heading('TÀI KHOẢN','Hồ sơ của bạn');?><section class="panel profile-panel"><h2><?=h($u['name'])?></h2><p><?=h($u['email'])?></p><span class="badge text-bg-light"><?=h($u['role'])?></span><p class="muted mt-4">Bản này chỉ hỗ trợ xem hồ sơ; không chỉnh sửa hoặc upload avatar.</p></section></main><?php }

function pagination(array $list):void{
 $params=$_GET;unset($params['p']);$page=(string)($params['page']??'home');unset($params['page']);
 echo '<nav class="pager" aria-label="Phân trang">';
 if($list['page']>1)echo '<a class="btn btn-outline-secondary" rel="prev" href="'.h(url($page,$params+['p'=>$list['page']-1])).'">Trước</a>';
 echo '<span>Trang '.$list['page'].' / '.$list['last'].' · '.$list['total'].' kết quả</span>';
 if($list['page']<$list['last'])echo '<a class="btn btn-outline-secondary" rel="next" href="'.h(url($page,$params+['p'=>$list['page']+1])).'">Sau</a>';
 echo '</nav>';
}
