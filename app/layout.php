<?php $withUtilities=$u&&$u['role']==='student'&&in_array($page,['learn','student','quiz','result'],true);if($withUtilities){require_once __DIR__.'/learning_utilities.php';require_once __DIR__.'/study_buddy.php';$buddyProfile=studyBuddyProfile((int)$u['id']);} ?>
<!doctype html>
<html lang="<?=h(uiLanguage())?>" class="no-js" data-theme-preference="<?=h(uiTheme())?>"<?php if(uiTheme()!=='system'):?> data-theme="<?=h(uiTheme())?>" data-bs-theme="<?=h(uiTheme())?>"<?php endif;?>>
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="theme-color" content="#113c36"><meta name="description" content="<?=h(t('StudyMate — học kiến thức nền tảng, thực hành qua từng bài và theo dõi hành trình của bạn.'))?>">
    <title><?=h(t('StudyMate · Học để hiểu, thực hành để làm được'))?></title>
    <script src="/assets/preferences.js"></script>
    <link rel="preload" href="/assets/fonts/Inter-Variable.ttf" as="font" type="font/ttf" crossorigin>
    <link rel="stylesheet" href="/assets/bootstrap.min.css"><link rel="stylesheet" href="/assets/app.css"><link rel="stylesheet" href="/assets/glass.css"><link rel="stylesheet" href="/assets/glass-workspace.css"><link rel="stylesheet" href="/assets/preferences.css">
    <script src="/assets/glass.js"></script><script src="/assets/forms.js" defer></script>
    <?php if($withUtilities): ?><link rel="stylesheet" href="/assets/study-buddy.css"><script src="/assets/learning-utilities-i18n.js" defer></script><?php endif; ?>
    <?php if($withUtilities&&$page==='learn'): ?><link rel="stylesheet" href="/assets/study-space.css"><link rel="stylesheet" href="/assets/lesson-notes.css"><script src="/assets/study-space.js" defer></script><script src="/assets/lesson-notes.js" defer></script><?php endif; ?>
    <?php if($withUtilities): ?><link rel="stylesheet" href="/assets/learning-utilities.css"><script src="/assets/study-buddy.js" defer></script><?php endif; ?>
    <script src="/assets/profile-media.js" defer></script><link rel="stylesheet" href="/assets/profile-media.css">
</head>
<body <?php if($withUtilities): ?>data-unsaved-notes-message="<?=h(ut('Bạn có ghi chú chưa lưu được và có thể có thay đổi biểu mẫu chưa gửi. Tải ghi chú xuống để giữ lại. Đổi ngôn ngữ và bỏ phần chưa lưu?'))?>"<?php endif; ?> data-unsaved-message="<?=h(t('Bạn có thay đổi chưa lưu. Đổi ngôn ngữ sẽ tải lại trang. Bạn có muốn tiếp tục?'))?>" data-pending-message="<?=h(t('Đang xử lý, vui lòng chờ…'))?>" data-quiz-exit-message="<?=h(t('Bạn có đáp án quiz chưa nộp. Rời trang sẽ mất lựa chọn. Bạn có muốn tiếp tục?'))?>" class="page-<?=h($page)?> <?=$page!=='home'?'liquid-workspace':''?>">
<?php if($page!=='home'):?><div class="workspace-backdrop" aria-hidden="true"><div class="workspace-backdrop-photo"></div></div><?php endif;?>
<a class="skip-link" href="#main-content"><?=h(t('Đến nội dung chính'))?></a>
<header class="glass-header glass-surface" id="site-header"><div class="glass-nav-wrap">
    <a class="brand" href="<?=h(url('home'))?>" aria-label="<?=h(t('StudyMate — Trang chủ'))?>"><span class="brand-mark" aria-hidden="true"><?=glassIcon('book')?></span><span>StudyMate<span class="brand-period">.</span></span></a>
    <button class="mobile-menu-toggle" type="button" aria-controls="site-navigation" aria-expanded="false" aria-label="<?=h(t('Mở menu điều hướng'))?>"><?=glassIcon('menu')?></button>
    <nav class="primary-navigation" id="site-navigation" aria-label="<?=h(t('Điều hướng chính'))?>"><div class="primary-links"><a href="<?=h(url('courses'))?>" <?=in_array($page,['courses','detail'],true)?'aria-current="page"':''?>><?=h(t('Khóa học'))?></a><a href="<?=h(url('home'))?>#fields"><?=h(t('Lĩnh vực'))?></a><a href="<?=h(url('home'))?>#how-it-works"><?=h(t('Cách học'))?></a></div><form class="nav-search" method="get" action="/index.php" role="search" aria-label="<?=h(t('Tìm khóa học toàn trang'))?>"><?=hidden('page','courses')?><label class="visually-hidden" for="global-query"><?=h(t('Bạn muốn học gì?'))?></label><input id="global-query" name="q" type="search" placeholder="<?=h(t('Bạn muốn học gì?'))?>" maxlength="100"><button aria-label="<?=h(t('Tìm kiếm'))?>"><?=glassIcon('search')?></button></form><div class="nav-account"><?php if($u):?><a class="dashboard-link" href="<?=h(url($u['role']))?>" <?=$page===$u['role']?'aria-current="page"':''?>><?=$u['role']==='student'?t('Việc học của tôi'):t('Khu vực của tôi')?></a><a class="account-link" href="<?=h(url('profile'))?>" title="<?=h($u['name'])?>"><?php profileAvatar($u);?><span class="visually-hidden"><?=h($u['name'])?></span></a><?=formStart('logout',['return_page'=>'home'])?><button class="btn nav-logout"><?=h(t('Đăng xuất'))?></button></form><?php else:?><a class="login-link" href="<?=h(url('login'))?>"><?=h(t('Đăng nhập'))?></a><a class="btn btn-primary" href="<?=h(url('register'))?>"><?=h(t('Bắt đầu học'))?></a><?php endif;?></div></nav>

    <details class="display-settings" id="display-settings">
      <summary aria-label="<?=h(t('Tùy chỉnh hiển thị'))?>" title="<?=h(t('Tùy chỉnh hiển thị'))?>"><?=glassIcon('globe')?><span><?=strtoupper(uiLanguage())?></span><span class="theme-symbol sun-symbol"><?=glassIcon('sun')?></span><span class="theme-symbol moon-symbol"><?=glassIcon('moon')?></span></summary>
      <div class="preferences-panel">
        <p class="eyebrow"><?=h(t('Hiển thị'))?></p><h2><?=h(t('Điều chỉnh không gian học tập'))?></h2>
        <form class="display-preferences" method="get" action="/index.php" data-display-preferences aria-label="<?=h(t('Tùy chỉnh hiển thị'))?>">
          <?=preferenceFields($page,$id)?>
          <label for="display-language"><?=h(t('Ngôn ngữ'))?><select class="form-select" id="display-language" name="ui_language"><option value="vi" <?=uiLanguage()==='vi'?'selected':''?> lang="vi">Tiếng Việt</option><option value="en" <?=uiLanguage()==='en'?'selected':''?> lang="en">English</option></select></label>
          <label for="display-theme"><?=h(t('Giao diện'))?><select class="form-select" id="display-theme" name="ui_theme"><?php foreach(['system'=>'Theo thiết bị','light'=>'Sáng','dark'=>'Tối'] as $value=>$label):?><option value="<?=$value?>" <?=uiTheme()===$value?'selected':''?>><?=h(t($label))?></option><?php endforeach;?></select></label>
          <button class="btn btn-primary preferences-apply" type="submit"><?=h(t('Áp dụng lựa chọn'))?></button>
        </form><p class="preferences-note"><?=h(t('Lựa chọn được lưu trên trình duyệt này.'))?></p>
      </div>
    </details>
</div></header>
<?php if($error||$f):?><div class="container flash-container"><div class="alert <?=$error?'alert-danger':'alert-'.h($f['type'])?>" role="alert"><?=h(displayMessage($error??$f['message']))?></div></div><?php endif;?>
<div id="main-content" tabindex="-1"><?=$body?></div>
<footer class="glass-footer"><div class="container footer-layout"><div class="footer-brand"><a class="brand" href="<?=h(url('home'))?>"><span class="brand-mark" aria-hidden="true"><?=glassIcon('book')?></span>StudyMate.</a><p><?=h(t('Học để hiểu. Thực hành để làm được.'))?><br><?=h(t('Một không gian cho những bước tiến của bạn.'))?></p><span><?=h(t('Đồ án Lập trình Web · TDTU'))?></span></div><div><h2><?=h(t('Khám phá'))?></h2><a href="<?=h(url('courses'))?>"><?=h(t('Thư viện khóa học'))?></a><a href="<?=h(url('home'))?>#fields"><?=h(t('Lĩnh vực học tập'))?></a><a href="<?=h(url('home'))?>#how-it-works"><?=h(t('Cách StudyMate hoạt động'))?></a></div><div><h2><?=h(t('Không gian của bạn'))?></h2><a href="<?=h(url($u?$u['role']:'login'))?>"><?=h(t('Tổng quan học tập'))?></a><a href="<?=h(url('profile'))?>"><?=h(t('Tài khoản cá nhân'))?></a><?php if(!$u):?><a href="<?=h(url('register'))?>"><?=h(t('Tạo tài khoản miễn phí'))?></a><?php endif;?></div></div><div class="container footer-bottom"><span>© <?=date('Y')?> StudyMate</span><span><?=h(t('Kiến thức nền tảng. Giá trị dài lâu.'))?></span></div></footer>
<?php if($withUtilities): ?><div id="learning-utilities-i18n" hidden data-messages="<?=h(json_encode(uiLanguage()==='en'?require __DIR__.'/lang/learning.en.php':[],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES))?>"></div><div class="container learning-buddy-slot"><?php renderStudyBuddy($u,$buddyProfile,$page); ?></div><?php endif; ?>
</body></html>
