# StudyMate PHP

**Tú cần làm nhanh:** mở [hướng dẫn thao tác chi tiết](docs/HUONG_DAN_TU.md). Mô tả PR bằng tiếng Việt nằm ở [PR_DESCRIPTION.md](docs/PR_DESCRIPTION.md).

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

Mở http://localhost:8080/index.php?page=home. DB dùng named volume, không mở cổng ra ngoài. Không chạy `docker compose down -v` khi cần giữ dữ liệu. Docker dùng cấu hình từ env, không dùng config.php local. Dockerfile/Compose đã được build và kiểm thử trên MySQL trong đợt nghiệm thu; xem docs/FINAL_ACCEPTANCE.md và docs/evidence/final/.

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

MySQL/Compose, request đồng thời và phân trang đã có kiểm tra mới trong docs/evidence/final/. Gemini thật vẫn chưa nghiệm thu. Các danh sách Student/Instructor/Admin/roster phân trang mặc định 12, tối đa 50; thống kê không tính riêng trang hiện tại. Markdown hỗ trợ heading/paragraph/code, chưa full CommonMark. Xác nhận dùng checkbox được kiểm trên server thay modal JavaScript.

Bản PHP thay bản React; không duy trì hai frontend song song. Trạng thái review/merge của đợt nghiệm thu mới xem docs/FINAL_ACCEPTANCE.md.


## Nghiệm thu cuối — Tú và nhóm (27/10/2026)

Bảng T1–T5/A1–A5/Q1–Q5, FR01–FR12, việc cần người thật và kịch bản demo: [FINAL_ACCEPTANCE.md](docs/FINAL_ACCEPTANCE.md). Kết quả kỹ thuật không đồng nghĩa đã review/merge main hay đã nộp bài.

### Máy/checkout sạch với Docker + MySQL

Yêu cầu Git, Docker Engine đang chạy và Docker Compose v2. Không cần PHP/npm trên host. Cổng 8080 phải trống. Clone repository và chuyển đúng nhánh đã được nhóm bàn giao. Trước khi merge dùng `codex/tu-final-acceptance`; sau khi review/merge dùng `main`:

```bash
git clone https://github.com/minhquan006/StudyMate.git
cd StudyMate
git switch codex/tu-final-acceptance
git rev-parse HEAD
cp .env.example .env
```

Mở `.env`, tự đặt `DB_PASSWORD` và `MYSQL_ROOT_PASSWORD` khác nhau. Không chia sẻ/commit file này. `WEB_PORT` mặc định 8080; `AI_ENABLED=0` không cần khóa để học/quiz. Sau đó:

```bash
docker compose up --build -d
curl --fail --silent --retry 10 --retry-all-errors --retry-connrefused --retry-delay 2 'http://127.0.0.1:8080/index.php?page=health'
docker compose logs --tail 30 app
```

Health phải trả `{"status":"ok"}`; trình duyệt mở cổng WEB_PORT với `index.php?page=home`. Kiểm login Student/Instructor/Admin theo seed ở trên. Nếu host không cho ghi `$HOME/.docker/buildx`, dùng thư mục ghi được: `DOCKER_CONFIG=/tmp/studymate-docker docker compose up --build -d`. Không tắt xác minh TLS/chữ ký.

Kiểm setup lặp và restart (không reset DB):

```bash
docker compose exec -T app php tools/setup.php
docker compose restart
curl --fail --silent --retry 10 --retry-all-errors --retry-connrefused --retry-delay 2 'http://127.0.0.1:8080/index.php?page=health'
```

Setup lần hai phải báo DB đã có dữ liệu, không seed lại. Volume giữ DB/session/quota qua restart. Không dùng `down -v` với dữ liệu cần giữ. Thay `.env` cần `docker compose up -d --force-recreate app`, restart đơn thuần không thay môi trường.

### Backup và restore an toàn

Dừng thao tác ghi hoặc chọn thời điểm nhất quán theo quy trình nhóm; `mysqldump --single-transaction` backup InnoDB. Chạy từ thư mục repo; thư mục chứa backup phải tồn tại và nằm ngoài public/source nộp bài. Backup chứa dữ liệu riêng, không commit:

```bash
sh tools/db_backup.sh /private/path/studymate.sql
sh tools/db_restore.sh /private/path/studymate.sql studymate_restore_review
```

Restore cố ý **chỉ tạo DB mới**; nếu tên đã tồn tại thì từ chối. Script không thay DB chạy chính. Dùng tài khoản root trong container để kiểm số dòng/nội dung/quan hệ cả 12 bảng trong DB restore trước bất kỳ chuyển đổi nào. Bằng chứng kiểm khớp hash toàn bộ 12 bảng trong đợt này nằm ở `docs/evidence/final/restore.json`. Đặt `COMPOSE_PROJECT_NAME` đúng project nếu không dùng mặc định.

### Chạy test trên project dùng một lần

Tạo một thư mục source sạch/clone riêng và `.env` riêng với `WEB_PORT=8083`, đặt hai mật khẩu test. **Không chạy tests/run.php, fixture.php hoặc http_acceptance.py trên DB phát triển/sản xuất.** Fixture chỉ chấp nhận khi có `STUDYMATE_DISPOSABLE_DB=1`; cờ này không tự biến DB thật thành DB test.

```bash
docker compose -p studymate-qa up --build -d
# Chạy 14 checks trên DB test được seed. Chỉ chạy một lần trên DB mới.
docker compose -p studymate-qa exec -T app php tests/run.php
```

Bộ HTTP cần DB seed mới **chưa chạy tests/run.php** (service test hoàn thành sẵn các bài, làm thay đổi điều kiện quiz gate). Dùng project/thư mục test thứ hai sạch, hoặc DB mới được cấp quyền riêng. Python 3 trên host:

```bash
STUDYMATE_DISPOSABLE_DB=1 COMPOSE_PROJECT_NAME=studymate-qa STUDYMATE_TEST_URL=http://127.0.0.1:8083 python3 tests/http_acceptance.py
# Offline AI contract; không dùng làm bằng chứng Gemini thật:
docker compose -p studymate-qa exec -T -e STUDYMATE_DISPOSABLE_DB=1 app php tests/ai_contract.php
```

`tests/fixture.php perf` yêu cầu DB seed mới 5 users/3 courses rồi tạo đúng 100 users/100 courses/500 lessons. Dùng DB/project riêng cho phép đo. Browser runner cần Python Playwright và Chromium tại `/usr/bin/chromium` (hoặc sửa đường dẫn theo máy):

```bash
docker compose -p studymate-perf exec -T -e STUDYMATE_DISPOSABLE_DB=1 app php tests/fixture.php perf
# Chuẩn bị trạng thái quiz/result cho browser bằng suite trên đúng DB perf riêng:
docker compose -p studymate-perf exec -T app php tests/run.php
STUDYMATE_BROWSER_URL=http://127.0.0.1:8081 python3 tests/browser_acceptance.py
```

`tests/query_budget.php` đo số query MySQL cho các trang Student/Instructor/roster/Admin ở 12 và 50 dòng; chạy với `STUDYMATE_DISPOSABLE_DB=1` trên DB perf, số query không được tăng theo số dòng.

Runner lưu kết quả/ảnh tại `docs/evidence/final/`. Browser runner dùng Chromium, không thay lượt kiểm Google Chrome/Microsoft Edge thủ công. Ảnh chỉ là viewport/nội dung trang, không có toolbar thật. Phép đo p95 gồm warm-up 5 lần, 30 HTTP catalog tuần tự, nearest-rank; không phải kiểm tải đồng thời. Cần đổi WEB_PORT=8081 cho project perf nếu dùng ví dụ trên.

### Đóng gói source

Sau khi review/merge, chốt SHA, chạy lại kiểm tra và thêm video/rubric/bằng chứng Git thật:

```bash
python3 tools/package.py /private/path/StudyMate-source.zip
```

Script không ghi đè ZIP đã có, loại config.php/.env/storage DB/session/backup, kiểm integrity và tạo manifest SHA256 cạnh ZIP. ZIP này là source, chưa tự bao gồm video ba người hoặc xác nhận nộp bài. Giữ đúng cấu trúc thư mục được ghi trong Specification.
