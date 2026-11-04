# Checklist nộp frontend PHP tối 12/10/2026

Hạn hệ thống: 00:00 ngày 13/10. Mục tiêu upload trước 23:00 ngày 12/10. Website mới dựng giao diện bằng PHP thật, có PDO và session. PDF cần ảnh bao gồm thanh địa chỉ browser thật, không chỉ caption URL.

## Chạy và kiểm tra

Theo README: vào StudyMate_PHP, tạo config.php, chọn SQLite cho chạy nhanh hoặc MySQL nếu đã có, setup, chạy `php -S localhost:8000 -t public`. Không dùng npm hoặc bản React cũ.

| Màn hình | URL sau localhost:8000 | Vai trò |
|---|---|---|
| Home | /index.php?page=home | Khách |
| Catalog | /index.php?page=courses | Khách |
| Chi tiết HTML | /index.php?page=detail&id=1 | Khách/Student |
| Login/Register | /index.php?page=login hoặc register | Khách |
| Dashboard | /index.php?page=student | Student |
| Learning JavaScript | /index.php?page=learn&id=2 | Student đã tham gia |
| Quiz JavaScript | /index.php?page=quiz&id=2 | Student học đủ bài |
| Result | URL thực được chuyển tới sau nộp | Student owner |
| Profile | /index.php?page=profile | Có phiên |
| Instructor | /index.php?page=instructor | Instructor |
| Editor CSDL | /index.php?page=edit&id=3&tab=info/content/quiz/students | Owner Instructor |
| Admin | /index.php?page=admin&tab=users/courses/categories | Admin |

Tab trong bảng là các giá trị thay thế; không nhập nguyên `info/content/...` vào URL.

## Tạo PDF đúng yêu cầu

1. Mở Chrome/Edge với thanh địa chỉ nhìn thấy, không fullscreen.
2. Mac dùng Command+Shift+4, chọn vùng chứa thanh địa chỉ và nội dung trang. Windows dùng Snipping Tool. Không chụp tab cá nhân hoặc key.
3. Chụp từng màn hình đã chạy thật. Với quiz/result phải hoàn thành bài và nộp thật; không ghép điểm bằng ảnh.
4. Đưa ảnh vào Word: tên màn hình, ảnh, mô tả ngắn. Export PDF, kiểm chữ và URL đọc được.
5. Ghi chế độ DB thực tế (SQLite local hoặc MySQL), AI chưa cấu hình nếu chưa có key. Không coi thông báo 503 là đã nghiệm thu AI.
6. PDF preview đi kèm không có browser toolbar, không thay ảnh tự chụp.
7. Upload trước 23:00 và kiểm trạng thái Submitted. Không tự nhận đạt 70% khi chưa đối chiếu cách chấm của thầy.

## Phân công giữ nguyên

An kiểm public/Student/CSS; Quân kiểm Instructor/Admin và PDF/upload; Tú kiểm PHP/PDO/session/CSDL/runtime. Mỗi người hiểu code của mình và ghi đúng đóng góp thực tế trên GitHub.
