<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="theme-color" content="#164ac8">
    <title>StudyMate · Học từng bước</title>
    <link rel="preload" href="/assets/fonts/Inter-Variable.ttf" as="font" type="font/ttf" crossorigin>
    <link rel="stylesheet" href="/assets/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/app.css">
    <script src="/assets/forms.js" defer></script>
</head>
<body class="page-<?=h($page)?>">
<a class="skip-link" href="#main-content">Đến nội dung chính</a>
<header class="topbar">
    <div class="nav-wrap">
        <a class="brand" href="<?=h(url('home'))?>"><span class="brand-icon" aria-hidden="true"><?=uiIcon('book')?></span><span>StudyMate</span></a>
        <?php if($page!=='learn'): ?>
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
                <a href="<?=h(url('login'))?>" <?=$page==='login'?'aria-current="page" class="active"':''?>>Đăng nhập</a>
                <a class="btn btn-primary" href="<?=h(url('register'))?>">Đăng ký miễn phí</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<?php if(in_array($page,['home','courses','detail'],true)): ?><nav class="subject-nav" aria-label="Danh mục khóa học"><div><a href="<?=h(url('courses'))?>">Tất cả khóa học</a><?php foreach(all('SELECT * FROM categories ORDER BY name') as $navCategory): ?><a href="<?=h(url('courses',['category'=>$navCategory['id']]))?>"><?=h($navCategory['name'])?></a><?php endforeach; ?></div></nav><?php endif; ?>
<?php if($error||$f): ?><div class="container flash-container"><div class="alert <?=$error?'alert-danger':'alert-'.h($f['type'])?>" role="alert"><?=h($error??$f['message'])?></div></div><?php endif; ?>
<div id="main-content" tabindex="-1"><?=$body?></div>
<footer class="footer"><div><a class="brand" href="<?=h(url('home'))?>">StudyMate</a><p>Kiến thức mở. Học theo cách của bạn.</p></div><nav aria-label="Liên kết cuối trang"><a href="<?=h(url('courses'))?>">Khám phá khóa học</a><a href="<?=h(url($u?$u['role']:'login'))?>">Không gian học tập</a></nav><span>Đồ án Lập trình Web · TDTU</span></footer>
</body>
</html>
