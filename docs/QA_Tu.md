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
## Bổ sung kiểm thử HTTP và phân quyền — 17/10/2026

Người kiểm tra: Phạm Văn Tú.
Commit mã nguồn kiểm thử: ab65abe.
Nhánh làm việc: test/tu-auth-http-concurrency.

### Môi trường và phương pháp

- Windows PowerShell, curl.exe, Docker Desktop.
- Docker Compose project: studymate-tu-http.
- Địa chỉ: http://localhost:8082.
- PHP 8.3.35, MySQL 8.4; setup và seed thành công.
- Dùng DB và storage riêng của project kiểm thử, không dùng dữ liệu demo đang giữ trong studymate-tuan3.
- Các request POST gửi tới /index.php, kèm cookie đăng nhập và CSRF hợp lệ.
- Không tự động đi theo redirect khi ghi nhận mã HTTP.

### Kết quả

| Ca kiểm thử | Request / dữ liệu | HTTP thực tế | Đối chiếu DB | Kết luận |
|---|---|---|---|---|
| Giả mạo quyền Admin khi đăng ký | action=register, role=admin; các trường đăng ký hợp lệ | 303 | Tài khoản mới ID 6 có role=student | Không nâng được quyền |
| Giả mạo quyền Instructor khi đăng ký | action=register, role=instructor; các trường đăng ký hợp lệ | 303 | Tài khoản mới ID 7 có role=student | Không nâng được quyền |
| Instructor ghi danh | instructor1, action=enroll, course_id=1 | Đăng nhập 303; ghi danh 403 | Sau request: 0 enrollment của tài khoản | Bị chặn |
| Admin ghi danh | admin, action=enroll, course_id=1 | Đăng nhập 303; ghi danh 403 | Sau request: 0 enrollment của tài khoản | Bị chặn |
| Student ghi danh khóa draft | student2, action=enroll, course_id=3 | 409 | Enrollment khóa 3 trước/sau: 0/0 | Bị chặn |
| Student ghi danh khóa archived | student2, action=enroll, course_id=3 | 409 | Enrollment khóa 3 trước/sau: 0/0 | Bị chặn |
| Gửi lặp quiz tuần tự qua HTTP | student2, course_id=2, cùng UUID và cùng payload bỏ trống, confirm=1 | Hai lần 303, cùng Location kết quả id=1 | 1 attempt, điểm 0% | Không tạo attempt trùng |
| Gửi quiz song song qua HTTP | Hai tiến trình PowerShell, hai session riêng của student2, cùng UUID và payload bỏ trống, course_id=2, confirm=1 | Hai lần 303, cùng Location kết quả id=2; curl exit code đều 0 | 1 attempt, 5 quiz_answers, điểm 0% | Không tạo attempt trùng trong lần kiểm thử |

Khóa 3 được đổi trạng thái trực tiếp bằng SQL trong DB kiểm thử để chuẩn bị dữ liệu draft/archived. Ca này kiểm tra chặn ghi danh phía server, không kiểm tra quy trình chuyển trạng thái qua giao diện Instructor.

### Chi tiết gửi lặp và song song

UUID gửi lặp tuần tự:
82e75f72-ca02-4318-86e5-02e06f3eb3c9

UUID gửi song song:
ef0708d1-ed5c-4dac-b926-9523328f056d

Hai phiên đăng nhập đã được kiểm tra có session ID khác nhau.
Hai tiến trình chờ cùng một tín hiệu trước khi gọi curl.exe.

Thời gian đo phía máy gửi, theo UTC:

- A: 14:27:54.8880578 đến 14:27:55.1615039.
- B: 14:27:54.8880578 đến 14:27:55.1587757.
- Hai khoảng thực thi chồng lấn khoảng 271 ms.

Minh chứng:

- [Kết quả HTTP song song](evidence/tu-http/concurrent-http.json)
- [Đối chiếu DB](evidence/tu-http/concurrent-db.txt)

### Giới hạn và việc còn lại

- Thời gian trên đo phía client, chưa có trace chứng minh hai transaction đồng thời chờ khóa trong MySQL.
- Ca song song mới thực hiện một cặp request cùng UUID và cùng payload bỏ trống; chưa phải kiểm thử tải.
- Chưa kiểm tra cùng UUID nhưng khác payload qua HTTP trong đợt này; kết quả service trước đó được ghi ở phần báo cáo cũ.
- Các kết quả mới bổ sung cho những mục trước đây ghi chưa kiểm thử; giữ phần cũ để thể hiện lịch sử.
- Chưa hoàn thành lượt tích hợp chung sau khi PR của An được merge.
- Chưa đánh dấu toàn bộ tuần 3 hoàn thành.

