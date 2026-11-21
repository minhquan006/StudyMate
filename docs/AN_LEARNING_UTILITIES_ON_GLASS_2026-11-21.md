# Tiện ích học tập trên giao diện #22

Bàn giao mới nhất sau khi #22 merge: [tích hợp main #19/#22 và kiểm lại tiện ích](AN_MAIN_INTEGRATION_2026-11-21.md). Các mốc và số kiểm bên dưới được giữ theo lượt lịch sử ban đầu.

Bổ sung sau review `c050b35`: [sửa nút đóng Góc tập trung bị header che trên tablet](AN_STUDY_SPACE_STICKY_FIX_2026-11-21.md). Các số kiểm trong báo cáo dưới đây thuộc đợt tích hợp ban đầu; bộ hồi quy sticky được ghi riêng.

Nền giao diện: PR #22, `7f374926acdf3b2c6a1fa9e08b204b39e7777299`.
Nguồn tách tính năng: PR #21, `79d877ada8da4301c97d9149c1ae6e29b76544e3` (đã gồm #23/#24/#25).
Nhánh bàn giao: `codex/an-tich-hop-tien-ich-hoc-tap-20261121`.

## Phạm vi

- Giữ nguyên trang chủ, logo, ảnh, font, giao diện kính, bảng điều khiển và CSS chính của #22. Không mang landing, banner IT hoặc logo của #21 sang. PR mới dựa trực tiếp vào nhánh #22 để diff chỉ chứa tiện ích; sau khi #22 merge cần cập nhật nền sang main mới.
- Ghi chú riêng theo tài khoản/khóa/bài trên trình duyệt: tự lưu sau 700 ms, lưu ngay, xuất UTF-8 `.txt`, giới hạn 20.000 ký tự Unicode, xử lý hết dung lượng/chặn lưu trữ, khôi phục bản lỗi và đối chiếu khi hai tab cùng sửa. Nhãn nói rõ **chưa đồng bộ giữa thiết bị**.
- Góc tập trung tùy chọn trên màn hình học: Pomodoro/nghỉ/bấm giờ, bốn cảnh, video rừng/Anime Café, bộ trộn mưa/gió/tiếng ồn, link YouTube và tối đa năm yêu thích. Trình phát giữ nguyên khi cuộn/đổi công cụ; chuyển bài cần bật âm thanh lại. Không tải video YouTube về máy chủ.
- Buddy là **thẻ trong khu vực học**, không còn nổi đè lên nút. Có thu gọn/khôi phục, tủ đồ, phụ kiện và nhắc nhẹ tùy chọn. Giữ cách xử lý click của #24; quiz dùng nhân vật thu gọn. XP lấy từ tiến độ đã lưu: 20/bài và 50/quiz đạt lần đầu, không ghi XP riêng hoặc cộng trùng.
- Tiện ích dùng tùy chọn Việt/Anh và sáng/tối của #22, bảng màu kính và font kế thừa. Nội dung khóa do tác giả nhập vẫn giữ nguyên. CSS bổ sung chỉ tác động các tiện ích và vùng học khi bật góc tập trung.
- Không có migration/schema hoặc thay đổi handler học/quiz/AI, hồ sơ, upload ảnh của Tú. Các form nghiệp vụ giữ action, trường và CSRF của #22. Chỉ màn hình học cho phép các đường dẫn script YouTube chính thức và gửi origin cho trình phát.

## Bảo vệ ghi chú khi điều hướng

Chuyển bài/gửi form/rời trang thử lưu đồng bộ trước. Nếu không lưu được, bản nháp vẫn ở trình soạn thảo và có tải `.txt`. Điều hướng POST chỉ khóa form sau khi người dùng đồng ý rời trang. Điều hướng thông thường có cảnh báo trước khi rời nếu vẫn còn bản chưa lưu.

Đổi ngôn ngữ gom trạng thái ghi chú và biểu mẫu vào **một xác nhận**. Bấm hủy giữ bản nháp và trả lựa chọn ngôn ngữ về hiện tại. Đồng ý bỏ phần chưa lưu cho phép tải lại trang mà không hiện thêm cảnh báo `beforeunload`. Đổi sáng/tối áp dụng tại chỗ, không tải lại hoặc bỏ bản nháp. Tranh chấp hai tab tạm dừng tự lưu đến khi người dùng chọn bản mới/bản đang sửa; bản lưu lỗi được giữ cho đến khi chọn tạo mới rõ ràng.

## Kiểm thực tế ngày 21/11/2026

Windows, PHP 8.3.31, SQLite QA riêng; Chrome 157 và Edge 157. Các ca ghi dữ liệu chạy ở bản sao trong thư mục Temp, không dùng DB demo. Đã xem ảnh thực tế ở laptop và viewport điện thoại.

| Bộ kiểm | Số ca đạt | Phạm vi |
| --- | ---: | --- |
| `learning-utilities-integration.cjs` | 143 | Hai trình duyệt; VI/EN, sáng/tối, rộng 390/768/1366; trang chủ khớp pixel #22 gốc; nhãn, XP/trang phục, bố cục không che thao tác; đổi bài/ngôn ngữ/giao diện; lỗi lưu + bản nháp AI chỉ một xác nhận; không JS |
| `lesson-notes.cjs` | 93 | Tự lưu, xuất tệp thật, Unicode, chuyển bài/hoàn thành bài, hai tab, hết dung lượng, bản lỗi, hủy điều hướng không khóa form, phân biệt tài khoản |
| `study-space.cjs` | 158 | Timer, lưu tùy chọn, video HD giải mã/phát thật, Web Audio, phím tab, link/yêu thích, dock di động, không JS; **YouTube mẫu vào trạng thái phát thật trên Chrome và Edge** |
| `study-buddy.cjs` | 99 | Nhân vật đọc/nghỉ/ăn mừng, khóa/mở tủ đồ theo XP, quiz không cộng trùng, nhắc học, thu gọn/khôi phục, giảm chuyển động, lỗi lưu, phân biệt tài khoản/vai trò; bố cục thẻ trong luồng trang |
| `study_buddy.php` | 15 | SQLite trong bộ nhớ; XP, không cộng trùng, thời điểm hoạt động; đọc XP không đổi snapshot 12 bảng |
| `tests/run.php` | 14 | Chấm quiz phía server, UUID, quan hệ đáp án, tiến độ/best score, FK và mật khẩu trên DB QA riêng |

Tổng 493 kiểm trình duyệt và 29 kiểm PHP. PHP/JS syntax và `git diff --check` đạt. Đã kiểm thêm tệp ghi chú tiếng Anh xuất thật sau khi biểu diễn newline trong catalog theo cách không phụ thuộc LF/CRLF.

**Giới hạn:** lượt này không chạy MySQL/Compose, Safari/điện thoại thật hoặc Gemini thật. Web Audio được kiểm decode/nguồn/âm lượng/trạng thái, chưa thay thế việc nghe đánh giá chất lượng âm thanh. Bằng chứng Gemini và kiểm DB của Quân/Tú không được tính là lượt chạy mới của PR này.

## Ảnh và bằng chứng

Kết quả JSON, hash nguồn theo LF và checksum bằng chứng: [thư mục evidence](evidence/an-utilities-glass-2026-11-21/). Các file CSS/layout chính được xác nhận giữ nguyên liệt kê trong `source.json`.

| Màn hình | #22 gốc | Bản tích hợp |
| --- | --- | --- |
| Màn hình học laptop | [Trước](evidence/an-utilities-glass-2026-11-21/learn-before-1366.png) | [Sau](evidence/an-utilities-glass-2026-11-21/learn-after-1366.png) |
| Màn hình học điện thoại | [Trước](evidence/an-utilities-glass-2026-11-21/learn-before-390.png) | [Sau](evidence/an-utilities-glass-2026-11-21/learn-after-390.png) |
| Ghi chú | | [VI sáng](evidence/an-utilities-glass-2026-11-21/notes-vi-light-1366.png), [EN tối](evidence/an-utilities-glass-2026-11-21/notes-en-dark-390.png) |
| Góc tập trung | | [VI sáng](evidence/an-utilities-glass-2026-11-21/tools-vi-light-1366.png), [EN tối](evidence/an-utilities-glass-2026-11-21/tools-en-dark-390.png) |
| Buddy | | [Nghỉ](evidence/an-utilities-glass-2026-11-21/buddy-coffee.png), [Tủ đồ](evidence/an-utilities-glass-2026-11-21/buddy-wardrobe-mobile.png) |

Nguồn media ghi tại `public/assets/spaces/SOURCES.md`, `public/assets/buddy/SOURCES.md` và nguồn ảnh nền chính của #22 tại `public/assets/photos/SOURCES.txt`. Cảnh bàn học dùng ảnh có sẵn của #22.

## Nhờ nhóm review

1. Đăng nhập Student, mở khóa đã đăng ký, chuyển qua các bài. Xem ghi chú ngay dưới nội dung; gõ rồi chuyển bài ngay để kiểm bản nháp được giữ.
2. Mở cùng bài ở hai tab, sửa cả hai; kiểm cảnh báo xung đột và cả hai lựa chọn. Tải tệp trước nếu muốn giữ cả hai bản.
3. Đổi VI/EN, sáng/tối trên desktop và điện thoại; kiểm nhãn, focus bàn phím, đoạn dài và quiz.
4. Bật Góc tập trung, thử timer, bốn cảnh, âm nền và link nhạc. Cuộn trang và đổi tab công cụ để đối chiếu trình phát.
5. Mở Buddy/tủ đồ, thu gọn và khôi phục; kiểm nút hoàn thành/chuyển bài/quiz không bị che hoặc mất click.

Để chạy lại bộ kiểm, dùng các **bản sao và DB SQLite dùng riêng cho QA**. `tests/run.php` ghi tiến độ/quiz; `study_buddy_fixture.php` chỉ dành cho seed mới với tên DB kết thúc `_test.sqlite` và cờ `STUDYMATE_DISPOSABLE_DB=1`. Không chạy chúng trên DB demo đang dùng.

Biến runner: `QA_PLAYWRIGHT_MODULE` trỏ Playwright; `QA_ALLOW_WRITES=1`; `QA_OUTPUT` là thư mục bằng chứng. Các runner notes/Buddy nhận `QA_NOTES_APPS`/`QA_BUDDY_APPS` (mỗi browser một app mới); runner space nhận `QA_BASE_URL`, `QA_BROWSERS` và app có fixture `tests/run.php`; runner tích hợp nhận thêm `QA_BASELINE_URL` chạy bản #22 gốc và app tích hợp có cùng fixture. Không cần đổi schema khi cập nhật bản tích hợp vào môi trường demo.
