# Báo cáo kiểm thử nền tảng — Phạm Văn Tú

Ngày kiểm tra: 12/10/2026.

## Môi trường

- Windows, Docker Desktop dùng Linux/WSL2.

- Docker 29.8.0; Docker Compose v5.5.1.

- Image PHP: php:8.3-apache; MySQL: mysql:8.4.

- Địa chỉ: http://localhost:8080

- Bản kiểm tra: StudyMate_PHP_Integrated.zip, chạy riêng ngoài repo.

- Compose project: studymate-test.

## Kết quả kiểm tra thủ công

| Nội dung | Kết quả |
|---|---|
| Build và khởi động Docker Compose | Đạt; app Up, DB healthy |
| Đăng nhập Student, Instructor, Admin | Đạt |
| Đánh dấu hoàn thành bài học | Tiến độ cập nhật |
| Quiz lần đầu | 0/5 câu, 0%, chưa đạt |
| Làm lại quiz | 5/5 câu, 100%, đạt |
| Dashboard sau hoàn thành | Hiển thị 1 khóa hoàn thành |
| Khởi động lại container | App và DB chạy lại |
| Dữ liệu sau restart | Giữ trạng thái hoàn thành và kết quả 100% |
| Trang Instructor | Mở được danh sách khóa học |
| Trang Admin | Mở được người dùng, khóa học và danh mục |

## Giới hạn

- Đây là kiểm tra thủ công bản Integrated, chưa phải nghiệm thu bản main.

- Chưa chạy tests/run.php trên MySQL.

- Chưa kiểm thử đầy đủ phân quyền, CSRF, request đồng thời và thao tác quản trị.

- AI đang tắt; chưa kiểm thử Gemini thật.

- Cần kiểm tra lại từ bản clone sạch sau khi merge đủ ba phần. 

---

## Cập nhật tuần 3 — ngày 15–16/10/2026

Người kiểm tra: Phạm Văn Tú.

Nguồn: clone mới từ GitHub, nhánh main, commit f142c53.

Không sử dụng ZIP Integrated cho đợt kiểm tra này.

### Môi trường

- Windows, Docker Desktop dùng Linux/WSL2.

- PHP 8.3.35; image MySQL mysql:8.4.

- Kiểm tra thủ công: project studymate-tuan3, localhost:8081.

- Kiểm thử tự động: project studymate-tuan3-check, DB và volume riêng.

### Kiểm tra thủ công

| Nội dung | Mong đợi | Thực tế |
|---|---|---|
| Khởi tạo từ clone sạch | App chạy, MySQL sẵn sàng | App Up, DB healthy; setup và seed thành công |
| Bảng và seed | 12 bảng, 2 Student, 2 Instructor, 1 Admin, 3 khóa học | Đúng số lượng trước khi tạo tài khoản thử |
| Chạy lại setup | Không reset hoặc seed trùng | Thông báo không ghi đè/reset; số lượng giữ nguyên |
| Giữ dữ liệu sau setup lại và restart | Tài khoản mới vẫn dùng được | Tài khoản tu.qa.tuan3@example.com đăng nhập lại được |
| Đăng ký thông thường | Tạo Student | Hồ sơ hiển thị student |
| Email trùng | Từ chối đăng ký | Báo Email đã được sử dụng |
| Mật khẩu sai | Từ chối đăng nhập | Báo Email hoặc mật khẩu không đúng |
| Mật khẩu đúng | Đăng nhập thành công | Vào được dashboard |
| Truy cập Student sau đăng xuất | Không hiện dữ liệu riêng | Báo phiên đăng nhập không hợp lệ |
| Tham gia khóa HTML | Khóa xuất hiện trong dashboard | Số khóa của tài khoản thử tăng từ 1 lên 2 |
| Tham gia lặp tuần tự ở hai tab | Một enrollment cho mỗi user/course | student2, course_id=1 có COUNT(*)=1 |
| Hoàn thành bài rồi refresh | Giữ trạng thái và tiến độ | Đã xác nhận giữ sau F5 |
| Mở quiz khi chưa học đủ | Bị chặn | Báo cần hoàn thành tất cả bài học |
| Quiz đúng 3/5 | 60%, chưa đạt | Hiển thị đúng 60%, chưa đạt |
| Làm lại thấp điểm | Không mất hoàn thành và điểm tốt nhất | JavaScript vẫn hoàn thành, điểm tốt nhất 100% |
| Câu bỏ trống | Không được cộng điểm | Hai câu hiện Bỏ trống; bài đúng 1/5 được 20% |

### Kiểm thử tự động trên MySQL

Lệnh đã chạy:

    docker compose -p studymate-tuan3-check build app

    docker compose -p studymate-tuan3-check run --rm app sh -c 'php tools/setup.php && php tests/run.php'

Kết quả: 14 checks passed. Driver: mysql

Các kiểm tra đạt:

- Chấm 5/5 = 100% và ghi completed_at.

- Cùng UUID, cùng payload trả về cùng attempt; DB chỉ có một bản ghi.

- Cùng UUID, khác payload bị từ chối với lỗi 409 ở mức service.

- Chấm 3/5 = 60%, chưa đạt.

- Hai câu bỏ trống được lưu answer_id NULL.

- Làm lại thấp không ghi đè completed_at; điểm tốt nhất vẫn 100%.

- Chặn đáp án không thuộc câu hỏi.

- Ràng buộc unique enrollment và khóa ngoại category có hiệu lực.

- Mật khẩu seed kiểm tra bcrypt thành công, không lưu plaintext.

### Giới hạn và việc còn lại

- Kiểm tra UUID là gọi service tuần tự, chưa kiểm tra gửi lặp qua HTTP hoặc request đồng thời.

- Chưa kiểm tra yêu cầu đăng ký giả mạo role.

- Chưa kiểm tra đầy đủ chặn Instructor/Admin tham gia khóa và tham gia khóa draft/archived.

- Chưa nghiệm thu AI thật, hiệu năng hoặc toàn bộ phân quyền quản trị.

- T5 review chéo và chạy tích hợp trên main mới nhất vẫn còn thực hiện.

- Kết quả trên chỉ áp dụng cho commit f142c53; chưa đánh dấu toàn bộ tuần 3 hoàn thành.

