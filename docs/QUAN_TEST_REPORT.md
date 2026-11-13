# Báo cáo kiểm thử phần Quân — 27/10/2026

Đây là báo cáo lịch sử cloud ngày 27/10. Kết quả phục hồi và kiểm lại trên macOS ngày 30/10 được ghi riêng tại [QUAN_LOCAL_REPORT.md](QUAN_LOCAL_REPORT.md); không sửa số liệu cũ thành kết quả mới.

## Phiên bản và môi trường

- Source: working tree dựa trên main `861e72c`, gồm thay đổi chưa commit trong phiên này; chưa có review/merge của thành viên khác.
- Linux cloud; PHP8.3.36; SQLite PDO và MySQL8.4; image kiểm thử PHP CLI có pdo_mysql/pcntl.
- Python3, Playwright và Chromium; viewport390/768/1366. Không có PHP trên host.
- Mỗi lần chạy tạo bản sao source, cấu hình, DB, network và container riêng. DB phát triển và cấu hình local có sẵn không bị reset/ghi đè.
- Lệnh và cách tái hiện: [tests/quan/README.md](../tests/quan/README.md).

## Kết quả

| Nhóm | SQLite | MySQL | Phạm vi |
|---|---|---|---|
| Runner HTTP/browser/integration | 304 kiểm tra đạt | 304 kiểm tra đạt | Bao gồm assertion không lộ PHP diagnostics, không phải304 test nghiệp vụ độc lập |
| Service suite hiện có | 14/14 đạt | 14/14 đạt | Chấm quiz, UUID/payload, completion, FK, unique, bcrypt |
| AI mô phỏng | 42 kiểm tra đạt | 42 kiểm tra đạt | Ask/summarize, input, quota, status/provider parsing, concurrency; chạy riêng trong runner |
| AI authorization | 2 kiểm tra/role cho Instructor và Student chưa enroll | Tương tự | Không gọi transport khi sai quyền |
| Browser | 6 màn hình ×3 viewport; thêm click phân trang giữ filter | Tương tự | Instructor, roster, quiz editor; ba tab Admin. 18 ảnh/DB trong bộ bàn giao ngoài repo |

Không cộng các dòng thành “tổng test độc lập”: runner còn bao gồm assertion về các suite con và nhiều HTTP diagnostics. Phải xem log theo từng nhóm.

### Các lỗi xử lý đã sửa

- Quiz có correct không phải số nguyên trước đây bị ép kiểu về đáp án0; nay trả422.
- Câu hỏi/lựa chọn dạng array hoặc dữ liệu sai cấu trúc được từ chối422 thay vì warning/lỗi500.
- Xóa chapter bằng ID thuộc course khác được trả404, không báo thành công giả.
- is_active không hợp lệ không bị ép thành0 rồi khóa user; cập nhật category không tồn tại trả404.
- Danh sách Instructor/Admin/roster có giới hạn và phân trang; số đếm Instructor là tổng, không phải số dòng trên trang.
- Roster dùng truy vấn aggregate thay gọi progressFor từng dòng; kết quả progress/best/completion được đối chiếu.
- AI xử lý timeout HTTP408/504, JSON/malformed/safety/empty response; chỉ trả text thông thường, bỏ thought parts. Không theo redirect khi gửi key; TLS giữ mặc định xác minh.
- Default model local/Docker thống nhất theo baseline, có GEMINI_MODEL để dùng model tài khoản hỗ trợ.

### Các ca bảo toàn dữ liệu

Suite băm nội dung12 bảng trước/sau các nhóm yêu cầu bị từ chối: CSRF, khóa cấu trúc, sai owner/role, ID chéo khóa và quiz sai. Kết quả không thay đổi DB. Hai phiên Student cũ cùng bị thu hồi sau khóa rồi mở lại tài khoản.

### Luồng tích hợp

Instructor publish khóa hợp lệ → Student2 enroll → hoàn thành các bài → nộp quiz100% → AI chưa cấu hình trả503 nhưng học vẫn dùng được → Admin archive → Student cũ vẫn học được → restart server → completion còn trong DB. Thao tác này chạy riêng trên SQLite và MySQL, không dùng dữ liệu demo đang giữ.

### Quota song song

12 tiến trình PHP riêng tranh cùng5 lượt app, mỗi tiến trình có key user riêng: đúng5 lượt được nhận, file JSON hợp lệ và count chung bằng5. Đây là kiểm thử cơ chế flock/takeQuota, không phải12 lời gọi Gemini thật hoặc kiểm thử tải HTTP.

## Bằng chứng

- [Log SQLite](evidence/quan-2026-10-27/sqlite.txt)
- [Log MySQL](evidence/quan-2026-10-27/mysql.txt)
- Ảnh browser mới nằm trong thư mục evidence của bộ bàn giao ngoài checkout, tên chứa driver/trang/viewport. Ảnh không có addressbar.
- [Log Docker Compose/Apache chính thức](evidence/quan-2026-10-27/compose.txt): clean build, MySQL healthy, health HTTP, login3 role, 14 service checks MySQL, GEMINI_MODEL binding và giữ quiz attempts sau restart đều đạt. PHP8.3.36. Chạy lại bằng `python3 tests/quan/compose.py`.
- Lần Compose đầu phát hiện Apache403 do source checkout0700/0600. Dockerfile nay chuẩn hóa directory755/file644 trong image, source vẫn root-owned; chỉ storage/sessions được chown www-data và đặt700. Kiểm tra Compose sau sửa đã đạt. Thay đổi nhỏ ở file Tú phụ trách cần Tú review.

## Kiểm tra gói ZIP đã giải nén

Gói ứng viên được giải nén vào thư mục tạm; xác nhận có docker/config.php, không có config.php local hoặc .env; chạy runner MySQL từ chính source giải nén: **264 runner checks đạt**, cùng14 service checks và42 AI checks mô phỏng. Không bật browser trong lượt ZIP nên ít hơn40 assertion so với304 ở lượt có browser. [Log kiểm tra ZIP](evidence/quan-2026-10-27/package-mysql.txt). Sau lượt này chỉ bổ sung tài liệu/bằng chứng, không đổi mã ứng dụng.

Công cụ package xác minh CRC và SHA256 từng tệp so với source khi tạo gói; manifest ghi base commit và trạng thái working tree chưa commit. Gói này không có chữ ký review hoặc xác nhận nộp bài.

## Chưa nghiệm thu

Gemini live/key/model availability/TLS network thực, prompt injection và chất lượng model; lỗi mạng/timeout thật; performance gate; Edge/accessibility toàn diện; reviewer khác, clone độc lập, rubric và trạng thái nộp bài. Xem [KNOWN_ISSUES.md](KNOWN_ISSUES.md) và [QUAN_ACCEPTANCE.md](QUAN_ACCEPTANCE.md).
