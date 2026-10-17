# QA – Lê Nguyễn Vạn An

## Phạm vi

Kiểm tra giao diện Public, Auth và Student của StudyMate theo phần công việc của An. Kết quả dưới đây dựa trên thao tác thực tế trong Google Chrome và các vùng màn hình đã quan sát.

## Môi trường kiểm thử

- Ngày kiểm tra: 17/10/2026.
- Trình duyệt: Google Chrome, chế độ Responsive trong DevTools.
- Kích thước: 390 × 844, 768 × 1024 và 1366 × 768.
- Ứng dụng chạy tại `http://localhost:8000`.
- Tài khoản sử dụng: Student demo.
- Cơ sở dữ liệu local trong lượt kiểm tra này: PDO SQLite.

## Kết quả kiểm tra giao diện

| Trang / khu vực | 390 × 844 | 768 × 1024 | 1366 × 768 |
| --- | --- | --- | --- |
| Home | Đã xem phần đầu và cuối trang | Đã xem phần đầu và cuối trang | Đã xem phần đầu và cuối trang |
| Danh sách khóa học | Đã xem bộ lọc, thẻ khóa học và chân trang | Đã xem bộ lọc, thẻ khóa học và chân trang | Đã xem bộ lọc, thẻ khóa học và chân trang |
| Chi tiết khóa học | Đã xem phần đầu và cuối trang | Đã xem phần đầu và cuối trang | Đã xem phần đầu và cuối trang |
| Đăng nhập | Đã xem biểu mẫu | Đã xem biểu mẫu và chân trang | Đã xem biểu mẫu và chân trang |
| Đăng ký | Đã xem biểu mẫu; chưa có ảnh xác nhận riêng phần cuối trang | Đã xem biểu mẫu và chân trang | Đã xem biểu mẫu và chân trang |
| Student | Đã xem thống kê, khóa học và chân trang | Đã xem thống kê, khóa học và chân trang | Đã xem thống kê, khóa học và chân trang |
| Bài học | Đã xem phần đầu và cuối trang | Đã xem phần đầu và cuối trang | Đã xem phần đầu và cuối trang |
| Quiz | Đã chọn đáp án, xem phần đầu và nút nộp bài | Đã chọn đáp án, xem phần đầu và nút nộp bài | Đã chọn đáp án, xem phần đầu và nút nộp bài |
| Kết quả | Đã xem điểm và đáp án | Đã xem điểm, đáp án đến câu cuối và chân trang | Đã xem điểm, đáp án đến câu cuối và chân trang |
| Profile | Đã xem thông tin tài khoản | Đã xem thông tin tài khoản và chân trang | Đã xem thông tin tài khoản và chân trang |

**Nhận xét:** Trong các vùng đã xem, chưa quan sát thấy nội dung bị cắt, tràn ngang hoặc chồng lấn. Các trang dài đã được cuộn để xem phần đầu và phần cuối; một số vùng giữa trang không có ảnh riêng nên kết luận chỉ áp dụng cho phạm vi đã quan sát.

## Luồng thao tác đã thực hiện

1. Mở các trang Public: Home, danh sách khóa học và chi tiết khóa học.
2. Kiểm tra biểu mẫu Đăng nhập và Đăng ký.
3. Đăng nhập bằng tài khoản Student demo.
4. Mở Student, bài học, Quiz, kết quả và Profile.
5. Kiểm tra giao diện ở ba kích thước màn hình nêu trên.

## Giới hạn của lượt kiểm thử

- Lượt kiểm tra này dùng PDO SQLite trên máy local; chưa xác nhận hoạt động với MySQL.
- Chưa kiểm tra phần Instructor, Admin và AI trong phạm vi QA của An.
- Những khu vực chưa quan sát đầy đủ được ghi rõ trong bảng, không tính là đã xác nhận đạt.