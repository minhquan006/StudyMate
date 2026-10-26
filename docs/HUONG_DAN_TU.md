# Tú làm theo hướng dẫn này

Bạn phụ trách T1–T5: Docker/MySQL, dữ liệu và khả năng chạy lại, truy vấn phân trang, kiểm thử đồng thời, hiệu năng, sao lưu/khôi phục và hỗ trợ nghiệm thu. Phần kỹ thuật đã được chuẩn bị trên nhánh `codex/tu-final-acceptance`. Bạn cần tự chạy, hiểu kết quả và nhờ nhóm review trước khi nhận Done.

**Làm nhanh theo thứ tự: mở PR → chạy ứng dụng → kiểm dữ liệu/restart → chạy 14 kiểm tra trên DB riêng → gửi kết quả cho Quân → nghiệm thu chung → quay video/nộp.** Các bước thêm cho Gemini và trình duyệt giao cho Quân/An, không để chặn việc của Tú.

## 1. Mở PR trên GitHub — làm trước

1. Đăng nhập GitHub bằng tài khoản của bạn và mở https://github.com/minhquan006/StudyMate/pull/new/codex/tu-final-acceptance .
2. Kiểm tra dòng chọn nhánh: **base: main**, **compare: codex/tu-final-acceptance**. Không chọn ngược.
3. Nếu GitHub báo đã có PR cho nhánh này, mở PR đó; không tạo thêm PR trùng.
4. Điền tiêu đề: **Hoàn thiện nghiệm thu Docker/MySQL và bàn giao phần Tú**.
5. Mở `docs/PR_DESCRIPTION.md` trên nhánh này, bấm biểu tượng sao chép nội dung thô nếu có, rồi dán vào ô mô tả PR. Không dán lệnh hoặc file `.env` vào mô tả.
6. Bấm mũi tên cạnh **Create pull request** và chọn **Create draft pull request** nếu còn đang kiểm tra. Các nút này là tên giao diện GitHub nên giữ nguyên tiếng Anh.
7. Trong mục **Reviewers**, chọn tài khoản Quân/An nếu bạn có quyền và biết đúng tài khoản. Nếu không chọn được, sao chép link PR để gửi nhóm.
8. Sau khi nhóm chạy đủ và đồng ý, chuyển PR sang **Ready for review**. Quân/An review phần tương ứng; chưa tự merge khi chưa đủ quy tắc nhóm.

**Cần thấy:** PR trỏ vào main, có danh sách file thay đổi và bảng kết quả test; chưa tự nhận Gemini thật hoặc toàn bộ task Done.

Nếu báo không có quyền: dùng đúng tài khoản đã được mời vào repository, hoặc nhờ Quân thêm quyền cộng tác. Không gửi mật khẩu/token cho ai.

## 2. Chuẩn bị máy một lần

Windows: cài Git từ https://git-scm.com/downloads và Docker Desktop từ https://www.docker.com/products/docker-desktop/ . Nếu Docker yêu cầu bật WSL 2/khởi động lại máy, hoàn tất theo hướng dẫn của Docker Desktop.

Mac: cài Git và Docker Desktop đúng chip máy. Các lệnh bên dưới dùng Terminal.

**Windows nên dùng Git Bash** cho toàn bộ lệnh bên dưới: mở Start → tìm Git Bash. Không trộn các lệnh đặt biến môi trường kiểu Bash vào PowerShell. Nếu chỉ muốn chạy ứng dụng/14 kiểm tra, chưa cần cài Python hoặc PHP trên máy.

Mở Docker Desktop, đợi trạng thái Docker Engine đã chạy. Trong Git Bash/Terminal:

```bash
git --version
docker --version
docker compose version
docker info
```

**Cần thấy:** ba lệnh version hiện phiên bản; `docker info` có phần Server, không báo không kết nối được daemon. Nếu lỗi daemon, quay lại Docker Desktop, chưa chạy tiếp.

## 3. Lấy đúng bản code trong thư mục mới

Chọn thư mục bạn dễ tìm, ví dụ Desktop. Nếu Desktop không nằm ở đường dẫn dưới đây, mở Git Bash ngay trong thư mục bạn chọn rồi bỏ lệnh `cd` đầu tiên.

```bash
cd ~/Desktop
git clone --branch codex/tu-final-acceptance https://github.com/minhquan006/StudyMate.git StudyMate-Tu
cd StudyMate-Tu
git branch --show-current
git rev-parse HEAD
```

**Cần thấy:** nhánh `codex/tu-final-acceptance`; lệnh cuối hiện SHA dài. Sao chép SHA vào ghi chú của bạn. SHA thay đổi khi nhánh được cập nhật; đừng lấy SHA cũ trong tin nhắn làm bằng chứng bản mới.

Nếu thư mục StudyMate-Tu đã tồn tại, không xóa/ghi đè: chọn tên khác, ví dụ StudyMate-Tu-2, rồi `cd` đúng tên. Nếu đã có thay đổi riêng, giữ chúng; dùng thư mục mới để tránh mất dữ liệu.

## 4. Tạo cấu hình và chạy web

Trong thư mục vừa clone:

```bash
cp .env.example .env
```

Mở `.env` bằng VS Code hoặc trình soạn thảo. Tự đặt hai mật khẩu khác nhau, đủ dài, ưu tiên chữ/số để tránh lỗi ký tự đặc biệt của Compose. Đây là mật khẩu DB local do bạn tạo, không phải mật khẩu GitHub. Thiết lập các dòng:

```dotenv
DB_PASSWORD=THAY_BANG_MAT_KHAU_DB_CUA_BAN
MYSQL_ROOT_PASSWORD=THAY_BANG_MAT_KHAU_ROOT_KHAC
WEB_PORT=8080
AI_ENABLED=0
```

Hai chuỗi `THAY_BANG_...` chỉ là chỗ hướng dẫn, phải thay trước khi chạy. Giữ các dòng còn lại từ `.env.example`. Chưa cần GEMINI_API_KEY để làm việc của Tú. Không chụp/quay `.env`.

```bash
docker compose up --build -d
docker compose ps
docker compose logs --tail 30 app
```

Lần đầu cần thời gian tải/build image. **Cần thấy:** `db` có trạng thái healthy, `app` đang chạy và thông báo setup hoàn tất hoặc DB đã có dữ liệu.

Mở trình duyệt:

- Kiểm sức khỏe: http://localhost:8080/index.php?page=health → phải thấy `{"status":"ok"}`.
- Trang chủ: http://localhost:8080/index.php?page=home .
- Đăng nhập: http://localhost:8080/index.php?page=login .

| Vai trò | Email | Mật khẩu mẫu mặc định |
|---|---|---|
| Student | student1@studymate.test | StudyMate2026! |
| Instructor | instructor1@studymate.test | StudyMate2026! |
| Admin | admin@studymate.test | StudyMate2026! |

Mật khẩu mẫu trên chỉ đúng nếu bạn không đổi `DEMO_PASSWORD` trước khi seed. Đổi biến sau khi DB đã seed không đổi mật khẩu tài khoản đã lưu. Dùng cửa sổ ẩn danh hoặc các hồ sơ trình duyệt riêng để giữ đồng thời ba phiên đăng nhập.

Nếu cổng 8080 bận: sửa `WEB_PORT=8085` trong `.env`, chạy lại `docker compose up -d`, rồi mở **cổng 8085** thay vì 8080 trong mọi URL.

## 5. Tú tự kiểm dữ liệu và restart — phần T1/T5

1. Đăng nhập Student, vào khóa JavaScript, mở một bài chưa hoàn thành và bấm đánh dấu hoàn thành.
2. Chụp màn hình tiến độ hiện tại, ghi tên khóa/bài và SHA. Không cần làm lại toàn bộ các test tự động ngay ở bước này.
3. Trong Git Bash/Terminal ở thư mục StudyMate-Tu, chạy:

```bash
docker compose exec -T app php tools/setup.php
```

**Cần thấy:** `Database đã có dữ liệu; không ghi đè hoặc reset.`

4. Chạy:

```bash
docker compose restart
docker compose ps
```

5. Đợi DB healthy, mở lại trang health cho đến khi trả `{"status":"ok"}`. Refresh hoặc đăng nhập lại Student nếu cần, kiểm bài vừa đánh dấu vẫn hoàn thành và tiến độ giữ nguyên.
6. Chụp ảnh sau restart. Ghi kết quả: “Chạy setup lần hai và restart không mất dữ liệu”, kèm SHA và ngày giờ thật.

**Không chạy `docker compose down -v`**: tùy chọn `-v` xóa volume dữ liệu. `restart` hoặc `stop` không có mục đích xóa DB.

Muốn nhìn dữ liệu trực tiếp, mở MySQL:

```bash
docker compose exec db mysql -u studymate -p studymate
```

Nhập **DB_PASSWORD bạn tự đặt** khi có lời nhắc; màn hình không hiện ký tự là bình thường. Trong MySQL chạy:

```sql
SELECT id, user_id, course_id, completed_at FROM enrollments ORDER BY id;
SELECT user_id, lesson_id, COUNT(*) AS so_ban_ghi
FROM lesson_progress
GROUP BY user_id, lesson_id
HAVING COUNT(*) > 1;
exit;
```

Truy vấn thứ hai phải không có dòng nào: không có progress trùng. Nếu có dữ liệu bất thường, lưu kết quả và báo nhóm, không xóa dữ liệu cho đẹp báo cáo.

## 6. Chạy 14 kiểm tra trên DB riêng — không dùng DB ở bước 4

Bộ test ghi dữ liệu, vì vậy tạo thư mục và project mới. Không copy `.env`, `config.php` hay DB từ bản đang dùng.

```bash
cd ..
git clone --branch codex/tu-final-acceptance https://github.com/minhquan006/StudyMate.git StudyMate-Tu-QA
cd StudyMate-Tu-QA
cp .env.example .env
```

Mở `.env` **trong StudyMate-Tu-QA**, đặt hai mật khẩu test mới; đặt `WEB_PORT=8084`, `AI_ENABLED=0`. Lưu rồi chạy:

```bash
docker compose -p studymate-tu-qa up --build -d
docker compose -p studymate-tu-qa ps
```

Mở http://localhost:8084/index.php?page=health . Khi thấy status ok, chạy:

```bash
docker compose -p studymate-tu-qa exec -T app php tests/run.php
```

**Cần thấy:** 14 dòng PASS và `14 checks passed. Driver: mysql`. Chụp màn hình gồm lệnh/kết quả; ghi SHA bằng `git rev-parse HEAD` ngay trong thư mục QA.

Chỉ chạy suite này một lần trên DB mới seed. Nếu chạy lại DB đã bị test thay đổi rồi gặp lỗi, không kết luận ngay code hỏng; tạo project test mới với tên và cổng khác. Không reset bản StudyMate-Tu để chạy test.

Có thể chạy thêm bộ AI mô phỏng trên cùng DB QA:

```bash
docker compose -p studymate-tu-qa exec -T -e STUDYMATE_DISPOSABLE_DB=1 app php tests/ai_contract.php
```

**Cần thấy:** `24 offline AI contract checks passed. Real Gemini: NOT RUN.` Đây chỉ là kiểm lỗi/quota theo hợp đồng, không tính FR10 đã đạt.

Bộ 78 ca HTTP và đo hiệu năng đã có bằng chứng trong `docs/evidence/final/`. Nếu cần tự chạy lại, xem README: chúng cần DB/project mới riêng, không dùng DB vừa chạy 14 kiểm tra. Ghi rõ “đã đọc bằng chứng” hoặc “tự chạy lại” cho đúng thực tế.

## 7. Sao lưu/khôi phục — quay về bản phát triển

Chạy `pwd` để kiểm mình đang ở đâu. Từ thư mục QA, quay về bản phát triển:

```bash
cd ../StudyMate-Tu
mkdir -p ../StudyMate-backups
sh tools/db_backup.sh ../StudyMate-backups/studymate-tu-lan1.sql
```

**Cần thấy:** `Backup completed.` File SQL nằm ngoài repo, có dữ liệu riêng nên không đưa vào Git hoặc ZIP nộp.

Khôi phục vào DB **mới**, không đổi DB ứng dụng đang dùng:

```bash
sh tools/db_restore.sh ../StudyMate-backups/studymate-tu-lan1.sql studymate_restore_tu_lan1
```

**Cần thấy:** `Restore completed into a separate database...`. Nếu tên DB đã tồn tại, đổi đuôi `lan1` thành `lan2`; script cố ý không ghi đè.

Kiểm dữ liệu restore bằng tài khoản root chỉ trên máy local:

```bash
docker compose exec db mysql -u root -p
```

Nhập `MYSQL_ROOT_PASSWORD`, rồi chạy:

```sql
SELECT COUNT(*) AS users_goc FROM studymate.users;
SELECT COUNT(*) AS users_khoi_phuc FROM studymate_restore_tu_lan1.users;
SELECT COUNT(*) AS enrollments_goc FROM studymate.enrollments;
SELECT COUNT(*) AS enrollments_khoi_phuc FROM studymate_restore_tu_lan1.enrollments;
SELECT COUNT(*) AS progress_goc FROM studymate.lesson_progress;
SELECT COUNT(*) AS progress_khoi_phuc FROM studymate_restore_tu_lan1.lesson_progress;
exit;
```

Mỗi cặp phải bằng nhau nếu bạn không sửa dữ liệu sau lúc backup. Đây là kiểm nhanh số dòng, **không thay việc so khớp nội dung cả 12 bảng**; bằng chứng kiểm toàn bộ đã được lưu trong `docs/evidence/final/restore.json`. Không chuyển ứng dụng sang DB restore ở bước này.

## 8. Tin nhắn để Tú tự gửi nhóm

Điền đúng phần bạn đã tự làm, xóa mục chưa thực hiện. Đây là mẫu để bạn gửi, công cụ không gửi thay bạn:

> Quân và An xem giúp PR: [dán link PR]. Bản Tú kiểm tại SHA: [dán SHA]. Tú đã chạy Docker/MySQL, kiểm setup/restart giữ dữ liệu, chạy [số kiểm tra thực tế] trên DB test riêng và kiểm backup/restore [ghi cách kiểm thực tế]. Bằng chứng đã có trong docs/evidence/final; ảnh chạy lại trên máy Tú ở [đường dẫn/link]. Nhờ Quân review backend/CSDL/bảo mật, An review phân trang/giao diện và tự cài độc lập theo README. Gemini thật, Chrome/Edge, rubric/video/nộp bài còn chờ nhóm xác nhận. Chưa đánh dấu Done trước khi review/merge và nghiệm thu chung.

## 9. Phần cần Quân và An làm

**Quân — AI và yêu cầu môn học:** nhập khóa Gemini riêng trong `.env`, bật `AI_ENABLED=1`, chọn `AI_MODEL` đúng tài liệu rồi chạy `docker compose up -d --force-recreate app`. Hỏi và tóm tắt thật bằng Student đã enroll, ghi kết quả lỗi/quota và xác nhận học/quiz vẫn hoạt động khi AI lỗi. Không gửi khóa vào chat/ảnh/Git, không bật billing. Đồng thời xác minh đề/rubric, hạn nộp, yêu cầu Git/video và quy định dùng AI từ giảng viên.

**An — trình duyệt và cài độc lập:** clone vào thư mục mới, tự tạo `.env`/DB riêng và chỉ theo README; ghi bước nào thiếu. Kiểm Google Chrome và Microsoft Edge thật, ghi version từ trang Giới thiệu của trình duyệt; rà 390/768/1366 px, Tab/focus, label, nút, empty/error, Instructor/Admin/AI panel. Nếu yêu cầu ảnh có thanh địa chỉ, chụp cửa sổ trình duyệt thật; PNG tự động hiện có không đáp ứng phần toolbar.

## 10. Sau khi được review và merge vào main

Giữ thư mục/DB đang dùng. Nếu có thay đổi cá nhân chưa commit, dừng việc đổi nhánh và dùng clone mới thay vì ghi đè. Với thư mục sạch:

```bash
git status --short
git fetch origin
git switch main
git pull --ff-only origin main
git rev-parse HEAD
docker compose up --build -d
```

Cả ba ghi cùng SHA main này. Chạy lại luồng liên quan sau sửa; nếu schema có thay đổi, cần migration/backup do nhóm review, không tự reset DB cũ. Quân tạo/publish khóa, An đi toàn luồng Student/AI thật, Tú đối chiếu dữ liệu/gửi trùng/restart, Quân khóa tài khoản để kiểm phiên cũ.

Chỉ chốt Done khi có review/merge, SHA, expected/actual, bằng chứng và tài liệu khớp bản cuối. Không quay video cuối trước khi chốt bản nghiệm thu; sửa code sau quay thì kiểm/quay lại phần ảnh hưởng.

## 11. Quay video và đóng gói

Phần Tú nói và làm: giới thiệu SHA/Docker/MySQL → chạy 14 kiểm tra trên DB riêng → chỉ enrollment/progress không trùng → minh họa restart giữ dữ liệu → giải thích backup/restore DB mới. Dùng kết quả thật trên máy, không nhận các commit Codex thành đóng góp tự viết của mình. Kịch bản ba người nằm trong `FINAL_ACCEPTANCE.md`.

Nếu có Python 3, sau khi chốt bản cuối, từ thư mục repo chạy:

```bash
mkdir -p ../StudyMate-nop
python tools/package.py ../StudyMate-nop/StudyMate-source-final.zip
```

Mac/Linux thường dùng `python3` thay `python`; Windows có thể dùng `py -3`. File ZIP không được tồn tại trước đó; chọn tên mới nếu đã có. Script yêu cầu repo Git, nên chạy từ bản clone ở trên. Nó chỉ đóng gói source và tài liệu có trong repo, không tự thêm video nằm ở thư mục khác hoặc bằng chứng Git mới.

Mở ZIP, kiểm có README, app, public, database/schema.php, database/schema.mysql.sql, docs và tests; không có `.env`, config.php local, DB/session/backup. `docker/config.php` là file cấu hình mẫu phục vụ Docker, vẫn phải có. Bổ sung video/rubric/ảnh/Git evidence đúng yêu cầu chính thức của môn. Quân kiểm gói nộp và trạng thái Submitted sau upload; không coi upload xong là đã chắc chắn nộp thành công.

## 12. Xử lý lỗi thường gặp

| Hiện tượng | Làm ngay |
|---|---|
| `docker` không tìm thấy | Cài Docker Desktop, mở lại Git Bash/Terminal |
| Không kết nối được Docker daemon | Mở Docker Desktop, đợi Engine chạy; xem yêu cầu WSL/khởi động lại |
| Cổng đã được dùng | Đổi WEB_PORT trong đúng `.env`, chạy lại Compose, mở URL cổng mới |
| Trang chưa mở sau build | `docker compose ps`, `docker compose logs --tail 50 app`, `docker compose logs --tail 50 db` |
| `Access denied` khi kết nối DB sau đổi mật khẩu | Volume cũ giữ tài khoản cũ. Dùng lại mật khẩu đúng trước đó; không xóa volume bản đang giữ dữ liệu |
| Thiếu DB_PASSWORD/MYSQL_ROOT_PASSWORD | Kiểm file tên chính xác `.env`, không phải `.env.txt`, đặt đủ hai dòng |
| 14 kiểm tra lỗi khi chạy lần hai | Suite cần DB mới; tạo project QA mới thay vì chạy lại trên DB đã thay đổi |
| AI trả 503 khi AI_ENABLED=0 | Đúng trạng thái chưa bật AI; học/quiz vẫn dùng được; chưa tính FR10 đạt |
| Restore báo DB đã tồn tại | Dùng tên DB mới, không xóa DB cũ để làm lệnh chạy qua |
| Có conflict hoặc `git pull` không chạy | Giữ nguyên file, lưu log/trạng thái và xử lý với nhóm; không dùng reset --hard |

Khi cần báo lỗi, gửi tên bước, lệnh đã chạy, SHA và phần lỗi liên quan; che mật khẩu/key. Không gửi toàn bộ `.env`.
