# Báo cáo nghiệm thu kỹ thuật — 27/10/2026

**Cập nhật 30/10/2026:** sửa UTF-8, DB khởi động muộn và cấu hình model; build image và các kiểm tra liên quan đã đạt. Xem [REVIEW_FIXES_2026-10-30.md](REVIEW_FIXES_2026-10-30.md). Kết quả 27/10 dưới đây giữ nguyên nguồn/SHA; không đổi thành kiểm tra của mã mới.

**Bổ sung mới:** 84 ca HTTP tích hợp, 235 kiểm tra Google Chrome 156 và 235 kiểm tra Microsoft Edge 142 đều đạt. Xem `evidence/final/integrated-http/`, `chrome/`, `edge/`. Kết quả Windows của Tú được ghi riêng trong `evidence/final/tu-windows-results.json`. Các số liệu 78 ca/Chromium bên dưới là lượt kiểm trước.


Bản thay đổi cục bộ chuẩn bị cho Tú, chờ review/merge. Xem [FINAL_ACCEPTANCE.md](FINAL_ACCEPTANCE.md) cho ma trận T/A/Q và phần nhóm phải làm. Bằng chứng cũ bên dưới giữ làm lịch sử.

| Kiểm tra mới | Kết quả | Bằng chứng |
|---|---|---|
| PHP lint, SQLite regression | 14/14 service checks, lint đạt | evidence/final/sqlite-and-lint.txt |
| MySQL 8.4 / PHP 8.3 service | 14/14 | evidence/final/mysql-service.txt |
| HTTP trên Docker/MySQL từ source sạch | 78 ca đạt, có concurrent và forced rollback | evidence/final/http-results.json |
| AI hợp đồng offline | 24 kiểm tra đạt; **provider thật chưa chạy** | evidence/final/ai-offline.txt |
| Chromium 390/768/1366 | 235 kiểm tra đạt, ảnh thật từ runner | evidence/final/browser-results.json |
| SQL query budget | Student/Instructor/roster 4; Admin 6; như nhau ở 12 và 50 dòng | evidence/final/query-budget.json |
| Catalog performance | 100 courses / 500 lessons / 100 users; warm-up 5, đo 30; p95 3.14 ms | evidence/final/performance.json |
| Backup/restore | Khớp hash dữ liệu cả 12 bảng, DB đích mới riêng | evidence/final/restore.json |
| Setup lặp/restart và cài sạch | So sánh toàn bộ dữ liệu, health sau restart | evidence/final/restart-and-clean-install.json |

Phép đo hiệu năng là HTTP tuần tự trong cloud, không gồm Internet/video/AI và không thay kiểm tải đồng thời. Chromium không thay lượt kiểm Google Chrome/Microsoft Edge thật. Ảnh không có browser toolbar. AI lỗi 503 hoặc stub offline không thay Gemini thật. Không xác nhận review/merge, rubric, video hoặc nộp bài khi chưa có người thực hiện.

Lỗi đã sửa: quyền đọc Apache trong image; Apache cần reason phrase rõ cho CSRF 419; danh sách chưa phân trang và N+1 progress; model AI không thống nhất; parser AI phòng dữ liệu parts sai kiểu. Mọi cập nhật quyền/CSDL cần Quân review kết quả trước merge.

---

## Báo cáo SQLite lịch sử (trước đợt nghiệm thu mới)

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
