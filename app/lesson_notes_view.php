<?php
function renderLessonNotes(int $userId,array $course,array $lesson):void {
    ?><section class="panel lesson-notes" id="lesson-notes" data-user="<?=$userId?>" data-course="<?=(int)$course['id']?>" data-lesson="<?=(int)$lesson['id']?>" data-course-title="<?=h($course['title'])?>" data-lesson-title="<?=h($lesson['title'])?>" aria-label="Ghi chú cá nhân theo bài" hidden>
        <details id="lesson-notes-details"><summary><span class="notes-summary-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M14 4H5v16h14v-9M13 13l1-4 6-6 3 3-6 6-4 1Z" stroke-linejoin="round"/></svg></span><span><strong>Ghi chú cho bài này</strong><small id="lesson-note-summary">Ghi lại điều bạn muốn nhớ.</small></span><span class="notes-summary-chevron" aria-hidden="true">⌄</span></summary>
            <div class="notes-editor"><p class="notes-local-hint">Lưu riêng theo tài khoản và bài học trên trình duyệt này. Tải xuống để giữ một bản hoặc dùng trên thiết bị khác.</p>
                <label for="lesson-note-input">Ý chính, câu hỏi hoặc đoạn code của bạn</label><textarea id="lesson-note-input" rows="7" maxlength="40000" placeholder="Điều mình đã hiểu…&#10;Ví dụ cần nhớ…&#10;Câu hỏi muốn tìm hiểu thêm…" aria-describedby="lesson-note-status lesson-note-count"></textarea>
                <div class="notes-meta"><p id="lesson-note-status" role="status">Sẵn sàng ghi chú.</p><output id="lesson-note-count" aria-live="off">0 / 20.000</output></div>
                <div class="notes-conflict" id="lesson-note-conflict" role="status" hidden><p>Bản lưu đã thay đổi ở tab khác. Nội dung đang sửa được giữ nguyên; tự lưu đang tạm dừng.</p><div><button type="button" id="lesson-note-use-saved">Đọc bản mới</button><button type="button" id="lesson-note-keep-draft">Lưu bản đang sửa</button></div></div>
                <div class="notes-recovery" id="lesson-note-recovery" hidden><p>Chưa đọc được bản lưu cũ. Hãy tải dữ liệu gốc trước khi tạo ghi chú mới.</p><button type="button" id="lesson-note-raw-download">Tải bản lưu gốc</button><button type="button" id="lesson-note-start-new">Tạo ghi chú mới</button></div>
                <div class="notes-actions"><button type="button" class="btn btn-primary" id="lesson-note-save">Lưu ngay</button><button type="button" class="btn btn-outline-secondary" id="lesson-note-download">Tải ghi chú (.txt)</button><button type="button" class="notes-delete" id="lesson-note-clear">Xóa ghi chú bài này</button></div>
            </div>
        </details>
    </section><?php
}
