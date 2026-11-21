<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="theme-color" content="#5040cf">
    <title>StudyMate · Học từng bước</title>
    <link rel="preload" href="/assets/fonts/Inter-Variable.ttf" as="font" type="font/ttf" crossorigin>
    <link rel="stylesheet" href="/assets/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/app.css">
    <script src="/assets/forms.js" defer></script>
</head>
<body>
<a class="skip-link" href="#main-content">Đến nội dung chính</a>
<header class="topbar">
    <div class="nav-wrap">
        <a class="brand" href="<?=h(url('home'))?>"><span class="brand-icon" aria-hidden="true">S</span><span>StudyMate<span class="brand-dot">.</span></span></a>
        <nav class="nav-links" aria-label="Điều hướng chính">
            <a href="<?=h(url('home'))?>" <?=$page==='home'?'aria-current="page" class="active"':''?>>Trang chủ</a>
            <a href="<?=h(url('courses'))?>" <?=in_array($page,['courses','detail'],true)?'aria-current="page" class="active"':''?>>Khóa học</a>
            <?php if($u): ?>
                <a href="<?=h(url($u['role']))?>" <?=$page===$u['role']?'aria-current="page" class="active"':''?>>Khu vực của tôi</a>
                <a class="account-link" href="<?=h(url('profile'))?>" title="<?=h($u['name'])?>" <?=$page==='profile'?'aria-current="page"':''?>><?=h($u['name'])?></a>
                <?=formStart('logout',['return_page'=>'home'])?><button class="btn btn-outline-secondary">Đăng xuất</button></form>
            <?php else: ?>
                <a href="<?=h(url('login'))?>" <?=$page==='login'?'aria-current="page" class="active"':''?>>Đăng nhập</a>
                <a class="btn btn-primary" href="<?=h(url('register'))?>">Bắt đầu học</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<?php if($config['app_env']==='local'): ?><div class="demo-strip">Môi trường demo · Dữ liệu học được lưu trong <?=h(strtoupper($config['db_driver']))?></div><?php endif; ?>
<?php if($error||$f): ?><div class="container flash-container"><div class="alert <?=$error?'alert-danger':'alert-'.h($f['type'])?>" role="alert"><?=h($error??$f['message'])?></div></div><?php endif; ?>
<div id="main-content" tabindex="-1"><?=$body?></div>
<footer class="footer"><div><a class="brand" href="<?=h(url('home'))?>">StudyMate<span class="brand-dot">.</span></a><p>Học từng bước. Hiểu từng bài.</p></div><span>Đồ án Lập trình Web · TDTU</span></footer>
</body>
</html>
