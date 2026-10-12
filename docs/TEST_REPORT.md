# Kiểm thử bản PHP

Môi trường đã chạy: PHP 8.3.6, PDO SQLite, Chromium headless và Playwright. PHP render HTML thật, không Node hoặc JavaScript runtime ở ứng dụng.

## Server

Tất cả file PHP lint thành công. `tests/run.php` chạy đạt 14 kiểm tra:

1. Server chấm 5/5 = 100% và đạt.
2. Ghi completed_at khi học đủ và đạt quiz.
3. Cùng UUID/payload trả attempt cũ.
4. DB chỉ có một attempt cho UUID.
5. Cùng UUID khác payload trả 409.
6. 3/5 = 60%, chưa đạt.
7. Hai câu bỏ trống được lưu answer_id null.
8. Làm lại thấp không ghi đè completed_at.
9. Điểm tốt nhất vẫn 100%.
10. Đáp án thuộc câu khác bị chặn.
11. Unique enrollment tại DB có hiệu lực.
12. FK chặn xóa category đang được dùng.
13. Password seed xác minh bcrypt đúng.
14. Không lưu password plaintext.

## Browser

Login Student/Instructor/Admin, học bài và lưu progress, gate quiz 403, nộp quiz, hoàn thành course, lưu quiz builder/publish, category restrict409, attempt owner404, thiếu CSRF419, AI chưa cấu hình503 đều đạt. Thu hồi phiên ở cửa sổ khác sau khi Admin khóa user, Instructor sai owner trả 404 và đăng ký bỏ qua role giả đều đạt. Không lỗi PHP/JavaScript trong luồng đã thử. Home/Catalog/Login ở 390/768/1366 không tràn ngang toàn trang.

## Chưa xác nhận

- Chưa chạy MySQL 8.4: không dùng kết quả SQLite thay nghiệm thu MySQL.
- Chưa build/run Docker Compose.
- Chưa thử hai request đồng thời hoặc transaction MySQL race.
- Chưa gọi Gemini bằng key thật hoặc chứng minh FR10 đã hoàn thành.
- Chưa đo p95 với bộ 100 courses/500 lessons/100 users.
- Các danh sách quản trị/roster cần phân trang và aggregate query trước performance gate.

Chỉ chạy tests/run.php trên DB test dùng một lần đã seed. Không chạy script lên dữ liệu cần giữ.
