# Nghiệm thu và bàn giao phần Nguyễn Minh Quân

Cập nhật 01/11/2026. Bản local đã ghép phần Quân với source #15 tại `81dc740`, nhận nền Tú `1a27ec1`. Các sửa validation và AI, kiểm thử SQLite/MySQL/Compose và Gemini HTTP/UI đã có bằng chứng tại [QUAN_INTEGRATION_2026-11-01.md](QUAN_INTEGRATION_2026-11-01.md). Đây là source chưa commit; review chéo, merge và nghiệm thu chung chưa được ký thay.

## Q1 Đối chiếu FR và yêu cầu môn

| FR | Chức năng | Bằng chứng kỹ thuật | Cần chốt |
|---|---|---|---|
| FR01 | Tài khoản | Đăng nhập ba role, thu hồi nhiều phiên | Tú xác nhận trên SHA cuối |
| FR02 | Quyền và CSRF | Owner/role/ID chéo, 12 action, DB không đổi | Tú review phần Quân |
| FR03 | Catalog | Phân trang, tìm UTF-8, filter | An kiểm trực quan |
| FR04 | Enrollment | Luồng enroll và unique; bằng chứng race của Tú | Tú chốt hồi quy cần thiết |
| FR05 | Learning | Lưu/giữ tiến độ, archived, restart | An chạy luồng bản cuối |
| FR06 | Quiz | Validation, chấm 100%, UUID và atomic rejection | Tú review/race theo thay đổi |
| FR07 | Instructor | CRUD/publish, khóa cấu trúc, ownership | Tú handler, An HTML |
| FR08 | Admin | Category/state, CSRF, thu hồi phiên | Tú handler, An HTML |
| FR09 | Dashboard | Tổng toàn tập, aggregate, phân trang | An/Tú đối chiếu giao diện |
| FR10 | Gemini | HTTP/MySQL/Chrome thật và mock tách riêng | Tú review chất lượng/mã; nhóm nghiệm thu |
| FR11 | Responsive | Chrome 18 màn hình phần Quân; lịch sử An riêng | An chốt keyboard/Edge bản cuối |
| FR12 | Bàn giao | README, SRS, schema, Docker và gói checksum | Review, SHA, hồ sơ và Submitted |

[COURSE_REQUIREMENTS.md](COURSE_REQUIREMENTS.md) giữ nguồn file tham khảo và yêu cầu lab đã được cung cấp. Giảng viên chỉ nói FinalProject để tham khảo; không áp chức năng note. Khoảng 10/11 hiện là dự kiến, không chuyển thành hạn LMS hoặc tự khẳng định đủ 70% theo rubric chưa chốt.

## Q2 Instructor và Admin

Đã nhận nền phân trang/query của Tú và vùng bảng của An. Thêm validation quiz/correct/array, chapter ID chéo khóa, is_active sai và category không tồn tại. Từ chối không làm thay đổi dữ liệu; khóa/mở vẫn thu hồi cả hai phiên cũ. Helper quản trị chỉ chuẩn hóa tham số rồi dùng `pageQuery()` chung. Tú review handlers/query; An review HTML và khả năng sử dụng.

## Q3 Gemini HTTP MySQL và UI

Model profile đã kiểm là `gemini-3.5-flash-lite`. Hỏi/tóm tắt đã hiển thị trong Chrome qua Apache/MySQL; thêm mẫu thiếu thông tin, injection có kiểm soát và khóa archived. Key giả từ project mới trả 503; quota app bằng fixture trả 429; học/hoàn thành bài/quiz vẫn hoạt động. Spy sai quyền và lỗi provider mô phỏng giữ riêng với các ca live. Provider timeout/quota thật chưa được cố tình gây lỗi, không cần tiêu quota tài khoản để ký nghiệm thu.

Lượt nội dung đầu có năm mẫu hợp lệ nhưng toàn lượt dừng tại ca đổi key; lượt lỗi riêng sau đó đạt. Giữ cả log failed và passed, không gộp thành một lần full-suite đạt. Kết quả mẫu cần Tú review độc lập; chưa tự ghi nhóm đã nghiệm thu FR10.

## Q4 Tài liệu và cấu hình

README/Readme đồng bộ; SRS/Specification có phụ lục tích hợp và phân biệt lịch nội bộ với hạn chính thức. `.env.example` chọn model đã kiểm, config PHP giữ fallback cũ và biến tương thích. File config.php/.env/storage của người dùng không được thay. About GitHub đề xuất: StudyMate - E-learning website built with PHP, PDO and MySQL; chưa cập nhật metadata trên GitHub.

## Q5 Review và phát hành

- [x] Hòa giải source mới của Tú/An với phần Quân và lưu bản trước sửa.
- [x] Chạy kiểm tra quyền/CSRF/validation/DB, Chrome, Compose và Gemini HTTP/UI trên dữ liệu riêng.
- [x] Chuẩn bị báo cáo, ma trận FR, thiết kế CSDL, kịch bản và công cụ đóng gói có checksum.
- [ ] Tú review handler/query/AI; An review HTML và kiểm trực quan.
- [ ] Quân tự commit bằng tài khoản cá nhân sau review; nhóm merge và chốt cùng SHA.
- [ ] Cả ba nghiệm thu luồng Instructor → Student → Admin/DB/Gemini trên SHA đó.
- [ ] Thành viên không làm setup chạy bản cuối chỉ theo README; giữ bằng chứng máy/browser thực tế.
- [ ] Ảnh có thanh địa chỉ thật, PDF giao diện, video và hồ sơ theo yêu cầu môn đã xác nhận.
- [ ] Tạo lại các đầu ra bị ảnh hưởng, nộp đúng kênh và kiểm Submitted.

## Kịch bản trình bày

1. Quân giới thiệu phạm vi/ba role, Instructor tạo khóa/chương/bài/quiz và publish; giải thích ownership và khóa cấu trúc.
2. An tìm/enroll/học/quiz/kết quả bằng Student mới; refresh vẫn giữ tiến độ.
3. Quân minh họa hỏi và tóm tắt thật, nêu nguồn bài và giới hạn; không để key xuất hiện.
4. Quân dùng Admin quản lý category/archive/khóa tài khoản; phiên cũ bị thu hồi.
5. Tú trình bày PDO/MySQL, transaction/unique, health/restart và restore DB riêng.
6. Cả ba nêu phần đóng góp thực tế và giới hạn. Thời lượng/video theo tiêu chí giảng viên xác nhận.

Các kết quả 27/10 và 30/10 giữ nguyên trong QUAN_TEST_REPORT.md và QUAN_LOCAL_REPORT.md. Source review và PDF chuẩn bị không tự xác nhận đã nộp hoặc đủ mọi yêu cầu học phần.
