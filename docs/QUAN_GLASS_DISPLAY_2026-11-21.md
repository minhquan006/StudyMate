# Giao diện kính và tùy chọn hiển thị

Bản này đưa landing page và các màn bên trong về cùng thiết kế dùng ảnh thật, thẻ kính và phân cấp nội dung rõ hơn. Lựa chọn Việt/Anh và Sáng/Tối/Theo thiết bị nằm trong menu quả địa cầu ở góc phải thanh điều hướng.

## Phạm vi

- Giữ nền mã của An từ PR #20, commit `56e58f8`; thêm giao diện kính, ảnh khóa và dashboard đọc số liệu thật.
- Ba khóa thực hành mới và importer của Tú vẫn thuộc PR #19. Website chạy độc lập với seed mặc định; sau khi tích hợp #19, thẻ khóa mới dùng các ảnh đã khai báo.
- PR #21 không nằm trong nhánh này. Không tự merge hoặc đóng PR của thành viên khác.
- Nội dung bài học và dữ liệu thành viên giữ ngôn ngữ đã biên soạn. Chi tiết lựa chọn, cookie và trường hợp không có JavaScript ở [DISPLAY_PREFERENCES.txt](DISPLAY_PREFERENCES.txt).

## Ảnh khóa và số liệu

Ảnh được lưu local, nguồn/giấy phép ở [SOURCES.txt](../public/assets/photos/SOURCES.txt). `app/course_media.php` ghép ảnh cho sáu tên khóa mẫu, kết hợp mẫu bìa hiện có. Khóa được đổi tên hoặc khóa mới dùng ảnh dự phòng theo lĩnh vực; lựa chọn đổi mẫu bìa của Instructor vẫn có hiệu lực. Đây chưa phải chức năng upload ảnh khóa.

Student xem số bài hoàn thành và lần nộp theo 7 ngày giờ Việt Nam, tối đa sáu điểm quiz gần nhất và tiến độ khóa. Instructor xem tối đa 5 khóa nhiều đăng ký nhất cùng tỷ lệ lần nộp đạt; Admin xem phân bố vai trò và tỷ lệ lần nộp đạt. Tỷ lệ tính theo lần nộp, không phải số người duy nhất. Không tạo lịch sử, nhận xét hoặc số học viên giả để làm đẹp biểu đồ.

## Kiểm thử

- Bộ hiển thị chạy trên bản sao SQLite tạm từ source của nhánh này: **744 lượt Chrome**, **441 lượt PHP**; fixture dịch vụ gốc đạt 14 lượt kiểm.
- Hồi quy MySQL/Chrome: **487 lượt kiểm** trong container/DB riêng; AI mô phỏng, không gọi Gemini thật.
- Hai runner có fingerprint riêng, các tệp runtime chung khớp nhau và khớp source trước commit. MySQL kiểm thêm `tools/wait_for_db.php`.
- Các số là lượt kiểm của runner, không cộng thành số chức năng độc lập. Log, fingerprint, báo cáo JSON và ảnh chọn lọc nằm tại [evidence/quan-glass-2026-11-21](evidence/quan-glass-2026-11-21/).

Ảnh bằng chứng PR dùng seed gốc của `main` (hai khóa công khai, một khóa nháp) và fixture hoàn thành bài/quiz. Preview local của Quân có thêm dữ liệu #19 nên số khóa có thể khác. Số hiển thị luôn đọc từ DB đang chạy.

Lệnh tự tái kiểm tra và các tùy chọn đường dẫn ở [tests/quan/README.md](../tests/quan/README.md). Các runner tạo bản sao và DB tạm, không yêu cầu sửa `.env` hoặc DB demo.

## Điểm cần review

- Landing, màn học, bảng quản lý và bốn tổ hợp hiển thị ở màn hình nhỏ; focus, chọn mục và thao tác không có JavaScript.
- Truy vấn biểu đồ theo đúng tài khoản/vai trò và cách diễn giải số liệu.
- Giữ đáp án/nội dung đang nhập khi đổi giao diện; xác nhận trước khi đổi ngôn ngữ làm tải lại trang.
- Chốt quan hệ #20/#21 trước khi hợp nhất hướng giao diện cuối; kiểm lại cùng dữ liệu #19 khi tích hợp vào `main`.
