<?php
function studySoundIcon(string $kind):string {
    $paths=[
        'rain'=>'M7 14H6a4 4 0 1 1 1-7.9A5.5 5.5 0 0 1 17.7 8 3 3 0 0 1 0 6H17M8 17l-1 3m6-3-1 3m6-3-1 3',
        'wind'=>'M3 8h12a3 3 0 1 0-3-3M2 12h17a3 3 0 1 1-3 3M4 16h5a3 3 0 1 1-3 3',
        'pink'=>'M3 12h2l2-6 3 12 3-14 3 16 2-8h3',
        'brown'=>'M2 14c3-9 5-9 8 0s5 9 8 0 4-6 4-6'
    ];
    return '<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="'.$paths[$kind].'"/></svg>';
}
function renderStudySpace(int $userId,int $courseId):void {
    ?><section class="study-space" id="study-space" data-user="<?=$userId?>" aria-labelledby="space-title" hidden>
        <div class="space-title-row"><div><p class="eyebrow"><?=h(ut('DÀNH THỜI GIAN CHO BẠN'))?></p><h2 id="space-title"><?=h(ut('Góc tập trung'))?></h2></div><button type="button" id="space-close" aria-label="<?=h(ut('Đóng không gian học tập'))?>"><?=h(ut('×'))?></button></div>
        <section class="space-timer" aria-labelledby="timer-title"><h3 id="timer-title" class="visually-hidden"><?=h(ut('Đồng hồ tập trung'))?></h3>
            <label for="space-timer-mode" class="visually-hidden"><?=h(ut('Chế độ đồng hồ'))?></label><select id="space-timer-mode" class="form-select"><option value="focus"><?=h(ut('Tập trung · 25 phút'))?></option><option value="break"><?=h(ut('Nghỉ ngắn · 5 phút'))?></option><option value="stopwatch"><?=h(ut('Đồng hồ bấm giờ'))?></option></select>
            <output id="space-clock" role="timer" aria-live="off">25:00</output><p id="space-timer-status" role="status"><?=h(ut('Sẵn sàng cho một phiên học.'))?></p>
            <div class="space-timer-actions"><button type="button" class="btn btn-primary" id="space-timer-start"><?=h(ut('Bắt đầu'))?></button><button type="button" class="btn btn-outline-secondary" id="space-timer-reset"><?=h(ut('Đặt lại'))?></button></div>
        </section>
        <div class="space-tabs" role="tablist" aria-label="<?=h(ut('Công cụ không gian học'))?>"><button type="button" role="tab" id="space-tab-scene" aria-controls="space-scene-panel" aria-selected="true" tabindex="0"><?=h(ut('Không gian'))?></button><button type="button" role="tab" id="space-tab-sound" aria-controls="space-sound-panel" aria-selected="false" tabindex="-1"><?=h(ut('Âm thanh'))?></button><button type="button" role="tab" id="space-tab-music" aria-controls="space-music-panel" aria-selected="false" tabindex="-1"><?=h(ut('Nhạc'))?></button></div>
        <section id="space-scene-panel" class="space-tab-panel" role="tabpanel" aria-labelledby="space-tab-scene">
            <fieldset class="space-scenes"><legend><?=h(ut('Chọn nơi bạn muốn học'))?></legend><?php foreach(['library'=>'Bên cửa sổ','forest'=>'Trong rừng','desk'=>'Bàn học','cafe'=>'Anime Café'] as $scene=>$name): ?><label class="space-scene-option"><input type="radio" name="study_scene" value="<?=$scene?>" <?=$scene==='library'?'checked':''?>><span class="scene-thumb scene-<?=$scene?>" aria-hidden="true"></span><span><?=h(ut($name))?></span></label><?php endforeach; ?></fieldset>
            <label class="space-motion-choice"><input id="space-motion" type="checkbox"><span><?=h(ut('Chuyển động nền'))?><small id="space-motion-status"><?=h(ut('Video HD trong rừng và Anime Café, chuyển động nhẹ ở các cảnh khác.'))?></small></span></label>
            <p class="space-hint" id="space-cafe-hint" hidden><?=h(ut('Quán cà phê Nhật Bản, mưa ngoài cửa kính. Bật tiếng Mưa ở tab Âm thanh; dán link Jazz Lofi bạn thích ở tab Nhạc.'))?></p>
            <p class="space-hint"><?=h(ut('Một góc quen thuộc để đọc, thực hành và tiếp tục bài học của bạn.'))?></p><a class="space-back-to-lesson" href="#lesson-reading"><?=h(ut('Về nội dung bài học ↑'))?></a>
            <details class="space-credits"><summary><?=h(ut('Nguồn hình ảnh và âm thanh'))?></summary><p><?=h(ut('Ảnh: Gül Işık, Alin Serban, RDNE Stock project. Video suối: Anton Atanasov /'))?> <a href="https://www.pexels.com/license/" target="_blank" rel="noopener noreferrer"><?=h(ut('Pexels'))?></a><?=h(ut('. Video Anime Café do nhóm cung cấp; ảnh dự phòng lấy từ video. Mưa: ezwa /'))?> <a href="https://commons.wikimedia.org/wiki/File:Rain_(1).ogg" target="_blank" rel="noopener noreferrer"><?=h(ut('Wikimedia, public domain'))?></a><?=h(ut('. Gió và hai màu tiếng ồn được tổng hợp trên thiết bị.'))?></p></details>
        </section>
        <section id="space-sound-panel" class="space-tab-panel" role="tabpanel" aria-labelledby="space-tab-sound" hidden>
            <label class="space-range-label" for="space-master-volume"><?=h(ut('Âm lượng chung'))?> <output id="space-master-value">60%</output></label><input id="space-master-volume" type="range" min="0" max="100" value="60">
            <div class="ambient-list"><?php foreach(['rain'=>['Mưa','Bản thu tự nhiên'],'wind'=>['Gió','Âm nền tổng hợp'],'pink'=>['Tiếng ồn hồng','Âm nền tổng hợp'],'brown'=>['Tiếng ồn nâu','Âm nền tổng hợp']] as $key=>[$title,$description]): ?><div class="ambient-row" data-sound-row="<?=$key?>"><label class="ambient-choice"><input type="checkbox" data-ambient="<?=$key?>" <?=$key==='rain'?'checked':''?>><span class="ambient-icon ambient-icon-<?=$key?>"><?=studySoundIcon($key)?></span><span class="ambient-name"><?=h(ut($title))?><small><?=h(ut($description))?></small></span><span class="ambient-activity" aria-hidden="true"><i></i><i></i><i></i></span></label><label class="visually-hidden" for="ambient-volume-<?=$key?>"><?=h(ut('Âm lượng'))?> <?=h(ut($title))?></label><div class="ambient-volume-row"><input id="ambient-volume-<?=$key?>" type="range" data-ambient-volume="<?=$key?>" min="0" max="100" value="35"><output id="ambient-value-<?=$key?>">35%</output></div></div><?php endforeach; ?></div>
            <button type="button" class="btn btn-primary w-100" id="space-ambient-play"><?=h(ut('Bật âm nền'))?></button><p id="space-audio-status" class="space-hint" role="status"><?=h(ut('Âm thanh đang tắt.'))?></p><button type="button" class="space-text-button" id="space-mute-all"><?=h(ut('Dừng tất cả âm thanh'))?></button>
        </section>
        <section id="space-music-panel" class="space-tab-panel" role="tabpanel" aria-labelledby="space-tab-music" hidden>
            <form id="space-music-form" method="get" action="/index.php"><?=hidden('page','learn')?><?=hidden('id',$courseId)?>
                <label for="space-youtube-url"><?=h(ut('Link YouTube'))?><input class="form-control" type="url" id="space-youtube-url" name="music_url" maxlength="500" placeholder="<?=h(ut('https://youtu.be/…'))?>" required aria-describedby="space-music-status"></label>
                <label for="space-music-name"><?=h(ut('Tên gợi nhớ (tùy chọn)'))?><input class="form-control" type="text" id="space-music-name" name="music_name" maxlength="60" placeholder="<?=h(ut('Ví dụ: Nhạc đọc sách'))?>"></label>
                <div class="space-music-load"><button type="submit" class="btn btn-primary"><?=h(ut('Mở nhạc'))?></button><button type="button" class="btn btn-outline-secondary" id="space-music-save"><?=h(ut('Lưu yêu thích'))?></button></div>
            </form>
            <p id="space-music-status" class="space-hint" role="status"><?=h(ut('Dán link một video. Nhạc phát sau khi bạn bấm Phát.'))?></p>
            <label class="space-range-label" for="space-music-volume"><?=h(ut('Âm lượng nhạc'))?></label><input id="space-music-volume" type="range" min="0" max="100" value="50">
            <div class="space-favorites"><h3><?=h(ut('Nhạc yêu thích'))?> <small><?=h(ut('(tối đa 5)'))?></small></h3><div id="space-favorite-list"></div></div><p class="space-hint"><?=h(ut('Trình phát nổi giữ nhạc khi bạn cuộn hoặc đổi công cụ. Chuyển sang tab trình duyệt khác sẽ tạm dừng.'))?></p>
        </section>
        <p class="space-storage-note" id="space-storage-status"><?=h(ut('Tùy chỉnh lưu trên trình duyệt này. Qua bài mới, đồng hồ tiếp tục; âm thanh cần bật lại.'))?></p>
    </section>
    <aside id="space-player-shell" class="space-player-dock" aria-label="<?=h(ut('Trình phát nhạc nổi'))?>" hidden><div class="space-dock-heading"><span><?=h(ut('Nhạc của bạn'))?> <small><?=h(ut('YouTube'))?></small></span><button type="button" id="space-player-side" aria-label="<?=h(ut('Chuyển trình phát sang trái'))?>" title="<?=h(ut('Đổi bên trình phát'))?>">↔</button><button type="button" id="space-player-close" aria-label="<?=h(ut('Đóng trình phát YouTube'))?>"><?=h(ut('×'))?></button></div><div id="space-player"></div><div class="space-player-actions"><button type="button" class="btn btn-primary" id="space-music-play" disabled><?=h(ut('Phát'))?></button><button type="button" class="btn btn-outline-secondary" id="space-music-pause" disabled><?=h(ut('Tạm dừng'))?></button></div></aside><?php
}
