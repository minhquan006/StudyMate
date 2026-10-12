# StudyMate PHP

Website PHP thuần dựng HTML trên server, truy vấn bằng PDO và lưu vào CSDL. Không cần React, Node/npm, Laravel hoặc Blade. Các chức năng dùng session thật và dữ liệu thật; quiz được chấm trên server. Baseline 2.2 giữ phạm vi, phân công và hạn hoàn tất 01/11/2026.

## Chạy nhanh trên Mac

Giải nén ZIP. Trong Terminal, vào thư mục **StudyMate_PHP** có `config.example.php`, `app`, `public`, `tools`. Không chạy trong `docs`.

Kiểm tra PHP:

```bash
php -v
php -m
```

Cần PHP 8.3+ và extension `PDO`, `pdo_sqlite` cho chạy nhanh. Nếu chưa có PHP và đã cài Homebrew, chạy `brew install php`.

Tạo cấu hình:

```bash
cp config.example.php config.php
```

Mở `config.php` bằng VSCode và đổi:

```php
'db_driver'=>'sqlite',
```

Chạy:

```bash
php tools/setup.php
php -S localhost:8000 -t public
```

Mở **http://localhost:8000/index.php?page=home**. Giữ Terminal mở. Không mở file PHP trực tiếp trong Finder. Nếu cổng 8000 bận, chọn 8001 và mở đúng cổng đó.

SQLite lưu dữ liệu thật trong `storage/studymate.sqlite`, không phải localStorage. Đây là chế độ chạy local và kiểm thử; **MySQL vẫn là CSDL cho bản cuối**. Chạy lại setup không ghi đè DB đã có người dùng.

## Chạy bằng MySQL

Cần extension `pdo_mysql`. Giữ `db_driver=mysql` trong config.php và nhập đúng host, port, database, username, password của MySQL trên máy. Chạy setup và server như trên.

Setup dùng DB có sẵn; nếu chưa có DB thì tài khoản cần quyền tạo database. Không đoán mật khẩu root. Schema nằm trong `database/schema.php`, dùng InnoDB, utf8mb4 và FK/index. MySQL cần kiểm thử riêng; chưa được chạy thử trong môi trường tạo gói này.

## Docker với MySQL

```bash
cp .env.example .env
```

Đổi DB_PASSWORD và MYSQL_ROOT_PASSWORD thành hai mật khẩu local, sau đó:

```bash
docker compose up --build
```

Mở http://localhost:8080/index.php?page=home. DB dùng named volume, không mở cổng ra ngoài. Không chạy `docker compose down -v` khi cần giữ dữ liệu. Docker dùng cấu hình từ env, không dùng config.php local. Dockerfile/Compose chưa được build-test ở đây; Tú phải kiểm tra trước bàn giao cuối kỳ.

## Tài khoản seed

Mật khẩu mặc định **StudyMate2026!**. Đổi `demo_password` hoặc `DEMO_PASSWORD` trước khi seed nếu muốn dùng mật khẩu khác. Đổi cấu hình sau seed không tự đổi password đã lưu.

| Vai trò | Email |
|---|---|
| Student | student1@studymate.test |
| Student thứ hai | student2@studymate.test |
| Instructor sở hữu 3 khóa | instructor1@studymate.test |
| Instructor để test ownership | instructor2@studymate.test |
| Admin | admin@studymate.test |

Student đầu tiên đã tham gia JavaScript và hoàn thành 1/6 bài. HTML và JavaScript published, khóa CSDL là draft. Đăng ký mới tạo Student, dùng đúng mật khẩu người dùng nhập, lưu bcrypt và tự đăng nhập. Không đăng ký Instructor/Admin trên giao diện.

## Kiểm tra và chụp ảnh nộp

1. Student: JavaScript → mở/hoàn thành từng bài → quiz → tick xác nhận → nộp → xem kết quả.
2. Instructor: khóa CSDL draft → các tab thông tin/nội dung/quiz → tick xác nhận và xuất bản.
3. Admin: users/courses/categories; không được khóa Admin hoặc xóa category đang dùng.
4. Chụp browser có thanh địa chỉ thật tại `index.php?page=...` và gom thành PDF theo `docs/SUBMISSION_CHECKLIST.md`.

PDF preview chỉ có ảnh viewport, **không có thanh địa chỉ**, cần chụp lại trên máy. PHP thật đã có nhưng không tự khẳng định đạt 70% rubric hoặc mọi yêu cầu cuối kỳ.

## AI

Trong config.php đặt `ai_enabled=true`, nhập key riêng ở `gemini_api_key`, giữ model `gemini-2.5-flash-lite`. Với Docker dùng AI_ENABLED=1/GEMINI_API_KEY trong .env. PHP cần openssl và allow_url_fopen. Chỉ dùng free tier, không bật billing.

Server lấy body từ DB, gọi HTTPS và kiểm quota bằng file lock. Thiếu key báo 503; học/quiz vẫn hoạt động. Chưa thử provider thật ở đây; Quân phải kiểm tra ask/summarize với key trước nghiệm thu FR10. Không gửi key trong ảnh, PDF hoặc Git.

## Vận hành

Document root chỉ là `public/`. Config, DB, sessions và quota nằm ngoài public. Không host từ project root. Khi production dùng HTTPS, secure_cookie=true, app_env=production. PHP built-in server chỉ dùng local.

Không có nút reset dữ liệu. Backup trước khi sửa. Thay đổi schema sau khi có dữ liệu cần script migration do Tú review. Không commit config.php, .env hoặc storage. Auth thu hồi phiên qua auth_version, có CSRF, bcrypt và PDO prepared statements.

## Kiểm thử và giới hạn

PHP lint, 14 service checks và browser tests trên SQLite đã đạt; xem docs/TEST_REPORT.md. `php tests/run.php` chỉ chạy trên DB dùng một lần đã seed vì script ghi dữ liệu test. Không chạy trên DB thật.

MySQL, request đồng thời, Gemini thật và Compose chưa nghiệm thu. Catalog phân trang 12; các danh sách quản trị/Student cần bổ sung phân trang trước performance gate. Markdown hỗ trợ heading/paragraph/code, chưa full CommonMark. Xác nhận dùng checkbox được kiểm trên server thay modal JavaScript.

Gói này chưa được cập nhật lên repo GitHub. Bản PHP thay bản React; không duy trì hai frontend song song.
