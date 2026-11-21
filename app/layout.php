<?php $withStudyBuddy=$u&&$u['role']==='student';if($withStudyBuddy){require_once __DIR__.'/study_buddy.php';$studyBuddyProfile=studyBuddyProfile((int)$u['id']);} ?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="theme-color" content="#6d28d2">
    <link rel="icon" href="/assets/images/studymate-icon.svg" type="image/svg+xml">
    <title>StudyMate · Học từng bước</title>
    <link rel="preload" href="/assets/fonts/Inter-Variable.ttf" as="font" type="font/ttf" crossorigin>
    <link rel="stylesheet" href="/assets/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/app.css">
    <script src="/assets/forms.js" defer></script>
    <script src="/assets/discovery.js" defer></script>
    <?php if($page==='learn'): ?><link rel="stylesheet" href="/assets/study-space.css"><script src="/assets/study-space.js" defer></script><?php endif; ?>
    <?php if($withStudyBuddy): ?><link rel="stylesheet" href="/assets/study-buddy.css"><script src="/assets/study-buddy.js" defer></script><?php endif; ?>
</head>
<body class="page-<?=h($page)?>">
<a class="skip-link" href="#main-content">Đến nội dung chính</a>
<?php if($page==='home'): ?><aside class="learning-announcement" aria-label="Thông tin học tập"><p><strong>Một khởi đầu mới cho việc học.</strong> Khám phá các khóa học miễn phí trên StudyMate. <a href="<?=h(url('courses'))?>">Khám phá ngay</a></p><button type="button" data-dismiss-announcement hidden aria-label="Đóng thông báo">×</button></aside><?php endif; ?>
<header class="topbar">
    <div class="nav-wrap">
        <a class="brand brand-artwork" href="<?=h(url('home'))?>" aria-label="StudyMate · Trang chủ"><img src="/assets/images/studymate-logo.webp" alt="StudyMate" width="480" height="292"></a>
        <?php if($page!=='learn'): ?>
        <details class="explore-menu"><summary>Khám phá <span aria-hidden="true">⌄</span></summary><nav class="explore-content" aria-label="Khám phá lĩnh vực"><a href="<?=h(url('courses'))?>">Tất cả khóa học <?=uiIcon('arrow')?></a><?php foreach(all('SELECT * FROM categories ORDER BY name') as $menuCategory): ?><a href="<?=h(url('courses',['category'=>$menuCategory['id']]))?>"><?=h($menuCategory['name'])?> <?=uiIcon('arrow')?></a><?php endforeach; ?></nav></details>
        <form class="global-search" method="get" action="/index.php" role="search" aria-label="Tìm khóa học toàn trang">
            <?=hidden('page','courses')?>
            <label class="visually-hidden" for="global-query">Bạn muốn học gì?</label><input id="global-query" name="q" type="search" placeholder="Bạn muốn học gì?" maxlength="100">
            <button type="submit" aria-label="Tìm kiếm"><?=uiIcon('search')?></button>
        </form>
        <?php endif; ?>
        <nav class="nav-links" aria-label="Điều hướng chính">
            <a href="<?=h(url('courses'))?>" <?=in_array($page,['courses','detail'],true)?'aria-current="page" class="active"':''?>>Khóa học</a>
            <?php if($u): ?>
                <a href="<?=h(url($u['role']))?>" <?=$page===$u['role']?'aria-current="page" class="active"':''?>><?=$u['role']==='student'?'Việc học của tôi':'Khu vực của tôi'?></a>
                <a class="account-link" href="<?=h(url('profile'))?>" title="<?=h($u['name'])?>" <?=$page==='profile'?'aria-current="page"':''?>><?=h($u['name'])?></a>
                <?=formStart('logout',['return_page'=>'home'])?><button class="btn btn-outline-secondary">Đăng xuất</button></form>
            <?php else: ?>
                <a class="btn btn-outline-secondary" href="<?=h(url('login'))?>" <?=$page==='login'?'aria-current="page"':''?>>Đăng nhập</a>
                <a class="btn btn-primary" href="<?=h(url('register'))?>">Đăng ký miễn phí</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<?php if(in_array($page,['courses','detail'],true)): ?><nav class="subject-nav" aria-label="Danh mục khóa học"><div><a href="<?=h(url('courses'))?>">Tất cả khóa học</a><?php foreach(all('SELECT * FROM categories ORDER BY name') as $navCategory): ?><a href="<?=h(url('courses',['category'=>$navCategory['id']]))?>"><?=h($navCategory['name'])?></a><?php endforeach; ?></div></nav><?php endif; ?>
<?php if($error||$f): ?><div class="container flash-container"><div class="alert <?=$error?'alert-danger':'alert-'.h($f['type'])?>" role="alert"><?=h($error??$f['message'])?></div></div><?php endif; ?>
<div id="main-content" tabindex="-1"><?=$body?></div>
<footer class="footer"><div><a class="brand brand-artwork" href="<?=h(url('home'))?>" aria-label="StudyMate · Trang chủ"><img src="/assets/images/studymate-logo.webp" alt="StudyMate" width="480" height="292" loading="lazy"></a><p>Kiến thức mở. Học theo cách của bạn.</p></div><nav aria-label="Liên kết cuối trang"><a href="<?=h(url('courses'))?>">Khám phá khóa học</a><a href="<?=h(url($u?$u['role']:'login'))?>">Không gian học tập</a></nav><span>Đồ án Lập trình Web · TDTU</span></footer>
<?php if($withStudyBuddy)renderStudyBuddy($u,$studyBuddyProfile,$page); ?>
</body>
</html>
