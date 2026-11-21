# Ghi chú riêng theo bài học — 21/11/2026

Runtime `bb6b37febd9f865a6c9e615c63a769a6ba5bee9b`, nhánh `codex/an-lesson-notes-20261121`, dựa trên bản thử đã có Buddy và bản sửa click tại `6508885`.

## Người học dùng như thế nào

Dưới nội dung bài học, mở **Ghi chú cho bài này**, ghi ý chính, câu hỏi hoặc code. Nội dung tự lưu sau khoảng 700 ms dừng gõ; có Lưu ngay, bộ đếm, Tải ghi chú (.txt) và Xóa ghi chú bài này. Khi quay lại bài có ghi chú, phần ghi chú tự mở.

Ghi chú lưu trên trình duyệt theo user ID, course ID và lesson ID. Các tài khoản/bài khác không dùng chung nội dung. Chưa đồng bộ giữa thiết bị; file tải xuống là bản sao để giữ hoặc chia sẻ. File UTF-8 có BOM, tên khóa/bài, thời điểm xuất và nguyên văn nội dung. Nội dung luôn là plain text, không chạy HTML/JavaScript.

Giới hạn 20.000 Unicode code point, có hỗ trợ tiếng Việt/emoji; nội dung vượt giới hạn vẫn được giữ trong ô nhập và có thể tải xuống, không âm thầm cắt hoặc thay bản đã lưu.

## Giữ dữ liệu khi có lỗi

- Chuyển bài/hoàn thành bài flush bản nháp trước khi form gửi. Textarea nằm ngoài các POST nghiệp vụ và không có trường note gửi lên server.
- Storage đầy/bị chặn: giữ nội dung hiện tại, báo lỗi rõ, tải file vẫn dùng được. Nếu chưa lưu mà rời trang, có cảnh báo. Với POST, xác nhận xảy ra trước khi forms.js khóa form; bấm Hủy không làm kẹt nút học/AI/đăng xuất. Đồng ý rời chỉ có một xác nhận.
- Hai tab cùng sửa: nếu bản đã lưu thay đổi khi còn draft, giữ draft và dừng tự lưu, cho chọn Đọc bản mới hoặc Lưu bản đang sửa sau xác nhận. Tab sạch đọc cập nhật mới; đây vẫn là ghi chú cá nhân trên một trình duyệt.
- Bản lưu sai định dạng: giữ nguyên dữ liệu gốc, cho tải bản gốc; chỉ thay khi người dùng chọn Tạo ghi chú mới và xác nhận.
- Xóa cần xác nhận và chỉ xóa khóa của bài hiện tại. Không JavaScript: ẩn công cụ ghi chú, bài học/form bình thường vẫn hoạt động.

Không đổi schema, seed, ID, handler, CSRF, điểm quiz, AI provider hoặc XP. Không thêm request/API ghi dữ liệu. Chỉ tải view/CSS/JS notes ở trang học. forms.js giữ nguyên.

## Kiểm mới

**93 kiểm trên Chrome/Edge đạt**, 0 JavaScript page error, source runtime ở trên. Có autosave/reload, Unicode đúng giới hạn, markup không chạy, download thật đọc lại tệp, responsive 390/768/1366, POST chuyển/hoàn thành bài, trường CSRF/action giữ đúng, tài khoản/bài khác, hai tab, quota, hủy/đồng ý rời, không khóa form, recovery và no-JS.

**158 kiểm hồi quy tích hợp đạt** là cùng lượt đã ghi trong PR sửa click Buddy #24, không phải một lần chạy mới sau commit notes. Source server liên quan được đối chiếu và trùng với commit runtime này; raw log/timestamps/fingerprints được giữ nguyên. Có YouTube thật mẫu phát trên cả hai browser, đồng hồ/âm nền/favorites/native lesson/quiz. 38 PHP syntax, JS syntax và diff checks đạt.

Lượt hồi quy đầu phát hiện Buddy bung trong focusin làm mouseup trượt khỏi nút lưu nhạc; log 49 kiểm trước khi dừng giữ ở diagnostics, bản sửa độc lập đã merge qua #24 trước khi tích hợp ghi chú. Notes không đổi code XP hoặc cơ chế player.

Giới hạn: SQLite QA riêng trên Windows, Chrome 157.0.8103.80/Edge 157.0.4341.60; chưa chạy MySQL/Docker hoặc Gemini thật, Safari/điện thoại vật lý. Notes không lưu ở server và không tự nhập tệp tải xuống trở lại ứng dụng.

## Chạy lại

Tạo hai checkout QA riêng, mỗi checkout có config SQLite và DB seed mới bằng `php tools/setup.php`. Chưa chạy tests/run.php trên hai DB này. Chạy server loopback khác cổng.

Cấu hình `QA_ALLOW_WRITES=1`, `QA_PLAYWRIGHT_MODULE`, `QA_OUTPUT` và `QA_NOTES_APPS` là JSON danh sách `name`, `path` executable, `base` URL của từng app. Chạy `node tests/lesson-notes.cjs`. Runner gửi POST thật và tạo tiến độ/enrollment trên DB QA, cần DB mới cho mỗi lần chạy. Bộ hồi quy `tests/study-space.cjs` dùng app/DB thứ ba theo báo cáo không gian học tập.

## Bàn giao

[Desktop](evidence/an-lesson-notes-2026-11-21/notes-desktop.png) · [Điện thoại](evidence/an-lesson-notes-2026-11-21/notes-390.png) · [93 kiểm](evidence/an-lesson-notes-2026-11-21/results.json) · [158 hồi quy tích hợp](evidence/an-lesson-notes-2026-11-21/regression/results.json).

[File tải thật Chrome](evidence/an-lesson-notes-2026-11-21/note-chrome.txt) · [File Edge](evidence/an-lesson-notes-2026-11-21/note-edge.txt) · [Source hashes](evidence/an-lesson-notes-2026-11-21/summary.json) · [Checksums](evidence/an-lesson-notes-2026-11-21/checksums.json).

PR đặt base nhánh bản thử #21 để chỉ review phần mới; việc ghép với #19/#22 và đưa lên main vẫn theo review của nhóm.
