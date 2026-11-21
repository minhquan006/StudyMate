<?php
// Cosmetic rewards are derived from academic records; no extra XP writes or schema.
function studyBuddyCatalog():array {
    return [
        'outfits'=>[
            'classic'=>['name'=>'Kimono quen thuộc','xp'=>0],
            'crimson'=>['name'=>'Kimono đỏ','xp'=>100],
            'student'=>['name'=>'Áo khoác học viên','xp'=>250],
        ],
        'accessories'=>[
            'none'=>['name'=>'Không phụ kiện','xp'=>0],
            'glasses'=>['name'=>'Kính chăm học','xp'=>50],
            'crown'=>['name'=>'Vương miện nhỏ','xp'=>150],
        ],
    ];
}
function studyBuddyProfile(int $userId,?int $at=null):array {
    $stats=one('SELECT lessons.done,lessons.last_lesson,quizzes.passed,quizzes.last_quiz,activity.last_visit
        FROM (SELECT COUNT(*) done,MAX(completed_at) last_lesson FROM lesson_progress WHERE user_id=?) lessons
        CROSS JOIN (SELECT COUNT(DISTINCT CASE WHEN passed=1 THEN quiz_id END) passed,MAX(submitted_at) last_quiz FROM quiz_attempts WHERE user_id=?) quizzes
        CROSS JOIN (SELECT MAX(updated_at) last_visit FROM enrollments WHERE user_id=?) activity',[$userId,$userId,$userId]);
    $done=(int)$stats['done'];$passed=(int)$stats['passed'];
    $dates=array_filter([$stats['last_lesson'],$stats['last_quiz'],$stats['last_visit']]);
    $last=$dates?max($dates):null;
    $days=$last?max(0,intdiv(($at??time())-(int)strtotime($last.' UTC'),86400)):0;
    return ['xp'=>$done*20+$passed*50,'lessons'=>$done,'quizzes'=>$passed,'lastStudyAt'=>$last,'inactiveDays'=>$days];
}
function renderStudyBuddy(array $user,array $profile,string $page):void {
    $catalog=studyBuddyCatalog();
    ?><aside id="study-buddy" class="study-buddy" data-user="<?=(int)$user['id']?>" data-xp="<?=$profile['xp']?>" data-inactive-days="<?=$profile['inactiveDays']?>" data-page="<?=h($page)?>" data-state="<?=$page==='learn'?'reading':'greeting'?>" hidden>
        <p class="buddy-bubble" id="buddy-bubble" role="status" hidden></p>
        <section id="buddy-panel" class="buddy-panel" aria-labelledby="buddy-title" hidden>
            <div class="buddy-panel-heading"><div><p><?=h(ut('BẠN ĐỒNG HÀNH HỌC TẬP'))?></p><h2 id="buddy-title"><?=h(ut('Sukuna của bạn'))?></h2></div><button type="button" id="buddy-panel-close" aria-label="<?=h(ut('Đóng bảng bạn đồng hành'))?>"><?=h(ut('×'))?></button></div>
            <p id="buddy-state-text"><?=h(ut('Cùng học một chút hôm nay nhé.'))?></p>
            <div class="buddy-xp"><strong><?=$profile['xp']?> <?=h(ut('XP'))?></strong><span><?=$profile['lessons']?> <?=h(ut('bài ·'))?> <?=$profile['quizzes']?> <?=h(ut('quiz đạt'))?></span></div>
            <p class="buddy-explanation"><?=h(ut('Mỗi bài hoàn thành +20 XP. Mỗi quiz đạt lần đầu +50 XP. Học lại không cộng trùng.'))?></p>
            <progress id="buddy-xp-progress" max="250" value="<?=min(250,$profile['xp'])?>" aria-label="<?=h(ut('Tiến độ mở trang phục'))?>"></progress><p id="buddy-next-reward" class="buddy-next-reward"></p>
            <details class="buddy-wardrobe"><summary><?=h(ut('Tủ đồ của Sukuna'))?></summary>
                <?php foreach(['outfits'=>'Trang phục','accessories'=>'Phụ kiện'] as $group=>$label): ?><fieldset><legend><?=h(ut($label))?></legend><div class="buddy-reward-grid"><?php foreach($catalog[$group] as $key=>$item): ?><button type="button" class="buddy-reward" data-buddy-group="<?=$group?>" data-buddy-item="<?=$key?>" data-required-xp="<?=$item['xp']?>" aria-pressed="false" <?=$profile['xp']<$item['xp']?'disabled':''?>><?php if($group==='outfits'): ?><img src="/assets/buddy/<?=$key?>-reading.webp" alt="" width="72" height="72" loading="lazy"><?php else: ?><span class="buddy-accessory-preview" aria-hidden="true"><?=$key==='glasses'?'⌐⌐':($key==='crown'?'♛':'—')?></span><?php endif; ?><span><?=h(ut($item['name']))?></span><small><?=$item['xp']===0?ut('Có sẵn'):($profile['xp']>=$item['xp']?ut('Đã mở khóa'):$item['xp'].' XP')?></small></button><?php endforeach; ?></div></fieldset><?php endforeach; ?>
            </details>
            <a class="buddy-study-link" href="<?=h(url('student'))?>"><?=h(ut('Về việc học của tôi'))?></a>
            <div class="buddy-settings"><label><input type="checkbox" id="buddy-reminders" checked> <?=h(ut('Nhắc nhẹ khi lâu chưa học'))?></label><button type="button" id="buddy-hide"><?=h(ut('Thu gọn nhân vật'))?></button></div>
            <p class="buddy-storage" id="buddy-storage"><?=h(ut('XP theo tài khoản. Trang phục và tùy chỉnh lưu trên trình duyệt này.'))?></p>
        </section>
        <button type="button" class="buddy-launcher" id="buddy-launcher" aria-label="<?=h(ut('Sukuna · Mở bạn đồng hành học tập'))?>" aria-expanded="false" aria-controls="buddy-panel"><span class="buddy-character"><img id="buddy-sprite" src="/assets/buddy/classic-<?=$page==='learn'?'reading':'greeting'?>.webp" alt="" width="112" height="112"><span class="buddy-accessory" aria-hidden="true"><svg class="buddy-glasses" viewBox="0 0 100 35"><g fill="#18355122" stroke="#183551" stroke-width="5"><rect x="5" y="5" width="36" height="25" rx="9"/><rect x="59" y="5" width="36" height="25" rx="9"/><path d="M41 15h18"/></g></svg><svg class="buddy-crown" viewBox="0 0 100 70"><path d="m10 20 20 15L50 8l20 27 20-15-10 40H20Z" fill="#f4c65c" stroke="#ad771d" stroke-width="4"/></svg></span></span><span class="buddy-launcher-label"><?=h(ut('Buddy'))?> <small><?=$profile['xp']?> <?=h(ut('XP'))?></small></span></button>
        <button type="button" class="buddy-restore" id="buddy-restore" aria-label="<?=h(ut('Mở lại bạn đồng hành Sukuna'))?>" hidden><?=h(ut('Buddy'))?> <span aria-hidden="true">✦</span></button>
    </aside><?php
}
