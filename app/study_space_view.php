<?php
function renderStudySpace(int $userId,int $courseId):void {
    ?><section class="study-space" id="study-space" data-user="<?=$userId?>" aria-labelledby="space-title" hidden>
        <div class="space-title-row"><div><p class="eyebrow">DÀNH THỜI GIAN CHO BẠN</p><h2 id="space-title">Góc tập trung</h2></div><button type="button" id="space-close" aria-label="Đóng không gian học tập">×</button></div>
        <section class="space-timer" aria-labelledby="timer-title"><h3 id="timer-title" class="visually-hidden">Đồng hồ tập trung</h3>
            <label for="space-timer-mode" class="visually-hidden">Chế độ đồng hồ</label><select id="space-timer-mode" class="form-select"><option value="focus">Tập trung · 25 phút</option><option value="break">Nghỉ ngắn · 5 phút</option><option value="stopwatch">Đồng hồ bấm giờ</option></select>
            <output id="space-clock" role="timer" aria-live="off">25:00</output><p id="space-timer-status" role="status">Sẵn sàng cho một phiên học.</p>
            <div class="space-timer-actions"><button type="button" class="btn btn-primary" id="space-timer-start">Bắt đầu</button><button type="button" class="btn btn-outline-secondary" id="space-timer-reset">Đặt lại</button></div>
        </section>
        <div class="space-tabs" role="tablist" aria-label="Công cụ không gian học"><button type="button" role="tab" id="space-tab-scene" aria-controls="space-scene-panel" aria-selected="true" tabindex="0">Không gian</button><button type="button" role="tab" id="space-tab-sound" aria-controls="space-sound-panel" aria-selected="false" tabindex="-1">Âm thanh</button><button type="button" role="tab" id="space-tab-music" aria-controls="space-music-panel" aria-selected="false" tabindex="-1">Nhạc</button></div>
        <section id="space-scene-panel" class="space-tab-panel" role="tabpanel" aria-labelledby="space-tab-scene">
            <fieldset class="space-scenes"><legend>Chọn nơi bạn muốn học</legend><?php foreach(['library'=>'Bên cửa sổ','forest'=>'Trong rừng','desk'=>'Bàn học'] as $scene=>$name): ?><label class="space-scene-option"><input type="radio" name="study_scene" value="<?=$scene?>" <?=$scene==='library'?'checked':''?>><span class="scene-thumb scene-<?=$scene?>" aria-hidden="true"></span><span><?=h($name)?></span></label><?php endforeach; ?></fieldset>
            <p class="space-hint">Một góc quen thuộc để đọc, thực hành và tiếp tục bài học của bạn.</p><a class="space-back-to-lesson" href="#lesson-reading">Về nội dung bài học ↑</a>
            <details class="space-credits"><summary>Nguồn hình ảnh và âm thanh</summary><p>Ảnh: Gül Işık, Alin Serban, RF._.studio _ / <a href="https://www.pexels.com/license/" target="_blank" rel="noopener noreferrer">Pexels</a>. Mưa: ezwa / <a href="https://commons.wikimedia.org/wiki/File:Rain_(1).ogg" target="_blank" rel="noopener noreferrer">Wikimedia, public domain</a>. Gió và hai màu tiếng ồn được tổng hợp trên thiết bị.</p></details>
        </section>
        <section id="space-sound-panel" class="space-tab-panel" role="tabpanel" aria-labelledby="space-tab-sound" hidden>
            <label class="space-range-label" for="space-master-volume">Âm lượng chung <output id="space-master-value">60%</output></label><input id="space-master-volume" type="range" min="0" max="100" value="60">
            <div class="ambient-list"><?php foreach(['rain'=>['Mưa','Bản thu tự nhiên'],'wind'=>['Gió','Âm nền tổng hợp'],'pink'=>['Tiếng ồn hồng','Âm nền tổng hợp'],'brown'=>['Tiếng ồn nâu','Âm nền tổng hợp']] as $key=>[$title,$description]): ?><div class="ambient-row"><label class="ambient-choice"><input type="checkbox" data-ambient="<?=$key?>" <?=$key==='rain'?'checked':''?>><span><?=h($title)?><small><?=h($description)?></small></span></label><label class="visually-hidden" for="ambient-volume-<?=$key?>">Âm lượng <?=h($title)?></label><input id="ambient-volume-<?=$key?>" type="range" data-ambient-volume="<?=$key?>" min="0" max="100" value="35"></div><?php endforeach; ?></div>
            <button type="button" class="btn btn-primary w-100" id="space-ambient-play">Bật âm nền</button><p id="space-audio-status" class="space-hint" role="status">Âm thanh đang tắt.</p><button type="button" class="space-text-button" id="space-mute-all">Dừng tất cả âm thanh</button>
        </section>
        <section id="space-music-panel" class="space-tab-panel" role="tabpanel" aria-labelledby="space-tab-music" hidden>
            <form id="space-music-form" method="get" action="/index.php"><?=hidden('page','learn')?><?=hidden('id',$courseId)?>
                <label for="space-youtube-url">Link YouTube<input class="form-control" type="url" id="space-youtube-url" name="music_url" maxlength="500" placeholder="https://youtu.be/…" required aria-describedby="space-music-status"></label>
                <label for="space-music-name">Tên gợi nhớ (tùy chọn)<input class="form-control" type="text" id="space-music-name" name="music_name" maxlength="60" placeholder="Ví dụ: Nhạc đọc sách"></label>
                <div class="space-music-load"><button type="submit" class="btn btn-primary">Mở nhạc</button><button type="button" class="btn btn-outline-secondary" id="space-music-save">Lưu yêu thích</button></div>
            </form>
            <p id="space-music-status" class="space-hint" role="status">Dán link một video. Nhạc phát sau khi bạn bấm Phát.</p>
            <div id="space-player-shell" hidden><div id="space-player"></div><div class="space-player-actions"><button type="button" class="btn btn-primary" id="space-music-play" disabled>Phát</button><button type="button" class="btn btn-outline-secondary" id="space-music-pause" disabled>Tạm dừng</button></div><label class="space-range-label" for="space-music-volume">Âm lượng nhạc</label><input id="space-music-volume" type="range" min="0" max="100" value="50"></div>
            <div class="space-favorites"><h3>Nhạc yêu thích <small>(tối đa 5)</small></h3><div id="space-favorite-list"></div></div><p class="space-hint">Giữ trình phát hiển thị để nghe YouTube. Chuyển sang công cụ khác hoặc cuộn trình phát ra ngoài sẽ tạm dừng.</p>
        </section>
        <p class="space-storage-note" id="space-storage-status">Tùy chỉnh lưu trên trình duyệt này. Qua bài mới, đồng hồ tiếp tục; âm thanh cần bật lại.</p>
    </section><?php
}
