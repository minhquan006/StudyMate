# Ba khóa thực hành cho demo StudyMate

Nhánh `tu/demo-courses-20261121` được tạo từ main `9cb5dea77d23684dc258198ffa2c4e3c9dee47dc`. Nội dung phục vụ phần trình bày ngày 22/11/2026. Tài liệu này mô tả phần bổ sung của Tú và cách kiểm trên DB demo.

## Nội dung bàn giao

| Khóa học | Danh mục | Chương 1 | Chương 2 | Bài / quiz |
| --- | --- | --- | --- | --- |
| Thực hành HTML và CSS: trang giới thiệu khóa học | Lập trình web | Tạo cấu trúc và biểu mẫu | Trình bày và hoàn thiện trang | 4 bài / 6 câu |
| Thực hành JavaScript: bộ theo dõi việc học | Lập trình | Tính toán và tổ chức dữ liệu | Tạo tương tác và lưu thử | 4 bài / 6 câu |
| Thực hành SQL: quản lý đăng ký khóa học | Cơ sở dữ liệu | Tạo dữ liệu và truy vấn cơ bản | Liên kết và tổng hợp báo cáo | 4 bài / 6 câu |

Mỗi mô tả khóa có đối tượng, điều kiện đầu vào, mục tiêu và sản phẩm sau khi học. Mỗi bài có mục tiêu, giải thích kiến thức, ví dụ chạy được, kết quả để đối chiếu và bài tập nhỏ. Quiz có bốn lựa chọn mỗi câu, một đáp án đúng và giải thích bám vào bài học.

Danh sách bài được thêm theo đúng thứ tự sau.

| Khóa | Chương | Bài 1 | Bài 2 |
| --- | --- | --- | --- |
| HTML/CSS | 1 | Từ tệp HTML đến trang có ngữ nghĩa | Form đăng ký có nhãn và kiểm tra đầu vào |
| HTML/CSS | 2 | Box model và thẻ khóa học dễ đọc | Grid responsive và kiểm trang hoàn chỉnh |
| JavaScript | 1 | Hàm tính phần trăm tiến độ | Mảng bài học và trạng thái hoàn thành |
| JavaScript | 2 | DOM, nút bấm và cập nhật tiến độ | Lưu trạng thái và xử lý dữ liệu không hợp lệ |
| SQL | 1 | Thiết kế bảng và khóa cho dữ liệu mẫu | SELECT, WHERE và thứ tự kết quả |
| SQL | 2 | INNER JOIN để xem người học đăng ký khóa nào | LEFT JOIN và COUNT để báo cáo cả khóa chưa có đăng ký |

Thông tin An có thể dùng trực tiếp trên thẻ và trang chi tiết gồm `title`, `description`, `category_name`, `cover_key`, tên giảng viên, số bài và danh sách chương/bài. Đối tượng và mục tiêu nằm trong mô tả hiện có. Các đoạn bài dùng heading, paragraph và code fence phù hợp bộ render Markdown của StudyMate. Ảnh bìa dùng các theme `web`, `programming`, `database` có sẵn.

## Cơ chế bổ sung dữ liệu

`database/demo_courses.php` chứa nội dung gốc. `database/seed_demo_courses.php` dùng `insertRow()` và transaction của dự án. Lệnh CLI là `tools/seed_demo_courses.php`.

Importer tìm giảng viên theo email `instructor1@studymate.test` và kiểm role instructor. Nó dùng ID thực tế của giảng viên và danh mục, rồi thêm ID tự tăng cho ba khóa published. Khóa cũ, người dùng, tiến độ, enrollment và lịch sử quiz được bảo toàn. Seed gốc và `tools/setup.php` tiếp tục cung cấp fixture ba khóa hiện có cho bộ kiểm.

Khóa mẫu đã có được nhận diện theo cặp giảng viên và tên khóa. Chạy lại sẽ bỏ qua khóa đó, bao gồm các chỉnh sửa bài học do giảng viên thực hiện sau import. Nếu đổi tên một khóa đã nhập rồi chạy importer lần nữa, khóa mang tên mẫu ban đầu sẽ được tạo thêm. Lệnh này phục vụ thêm khóa mẫu, không đồng bộ lại nội dung đã được sửa tay.

Toàn bộ lượt import nằm trong một transaction. Khi lỗi giữa chừng, các dòng mới trong lượt đó được rollback. Khóa tệp trong private storage tuần tự hóa các lượt chạy cùng checkout. Trên MySQL, khóa dòng giảng viên còn tuần tự hóa các lượt chạy từ các host khác nhau dùng cùng DB. Các lượt import đều dùng cùng tài khoản giảng viên mẫu.

## Đưa vào môi trường demo

Trước khi cập nhật DB demo hiện có, tạo backup theo mục backup v2 trong README và kiểm đúng project/config đang sử dụng. Thực hiện lệnh import sau khi đã cập nhật source của PR này.

Với SQLite hoặc MySQL chạy trực tiếp, tạo `config.php` từ mẫu theo hướng dẫn README, chọn driver và thông tin DB riêng. Sau đó chạy từ thư mục gốc repository.

```bash
php tools/setup.php
php tools/seed_demo_courses.php
```

DB mới nhận seed gốc rồi thêm ba khóa. DB đã có người dùng sẽ được setup giữ nguyên trước khi chạy importer. Lệnh import in ID thực tế, tên khóa và trạng thái đã thêm hoặc đã có. Tổng trên DB mẫu ban đầu sau import là 6 khóa, 12 chương, 30 bài, 6 quiz, 33 câu hỏi và 132 lựa chọn. Các số này áp dụng khi DB ban đầu có đúng fixture gốc và chưa có nội dung do người dùng tự thêm.

Với Compose, dựng lại app từ source mới rồi chạy importer trong container app của đúng project demo.

```bash
docker compose up --build -d
docker compose exec -T app php tools/seed_demo_courses.php
```

Importer dùng cấu hình DB hiện có của app. Giữ nguyên volume demo khi cập nhật. Trong giao diện, đăng nhập bằng Student, tìm `Thực hành`, mở chi tiết khóa và đăng ký. Giảng viên mẫu quản lý được các khóa này ở khu vực Instructor. Quiz xuất hiện sau khi hoàn thành đủ bốn bài.

Các bài HTML/CSS dùng một thư mục thực hành riêng. Các bài JavaScript có thể chạy Console hoặc tệp HTML qua HTTP server cục bộ. Bài localStorage chỉ lưu khóa `studymate-practice-done`. Các bài SQL chạy trên DB thực hành riêng, sử dụng tên bảng `lab_courses` và `lab_enrollments`. Với SQLite, bật `PRAGMA foreign_keys = ON` trên kết nối thực hành trước khi chạy ví dụ. Với MySQL, dùng InnoDB.

## Kiểm lại nội dung

Đáp án được ghi theo thứ tự lựa chọn A–D để reviewer đối chiếu với câu hỏi trong `database/demo_courses.php`.

| Khóa | Câu 1 | Câu 2 | Câu 3 | Câu 4 | Câu 5 | Câu 6 |
| --- | --- | --- | --- | --- | --- | --- |
| HTML/CSS | B | C | A | D | A | C |
| JavaScript | B | C | A | D | C | B |
| SQL | B | C | A | D | B | C |

Các kết quả mẫu cần đối chiếu gồm tiến độ JavaScript 25, 0, 67; mảng mới có hai bài hoàn thành và mảng gốc giữ `false`; nút DOM tăng 25% mỗi lần đến 100%; localStorage giữ 50% sau reload và loại abc, 9, 1.5. Hộp CSS border-box rộng 300px, content-box rộng 342px. Grid có hai cột ở 900px và một cột ở 390px.

SQL lọc published trả HTML, JavaScript. INNER JOIN trả ba đăng ký. LEFT JOIN với COUNT(e.id) trả HTML 2, JavaScript 1, SQL 0. Hai thao tác thêm đăng ký có course_id 99 và đăng ký trùng email/khóa bị ràng buộc từ chối.

Các lệnh tái kiểm sau dùng một DB QA riêng mới có seed gốc. Tạo DB và user QA bằng tài khoản quản trị, chỉ cấp quyền trên DB QA đó (quyền DDL cần cho setup và fixture, gồm CREATE/ALTER/DROP, cùng quyền đọc/ghi dữ liệu). Cấu hình `config.php` của checkout QA dùng **user app này**, không dùng root hoặc DB demo. `tests/run.php` và `tests/demo_courses.php` ghi dữ liệu kiểm thử, vì vậy kiểm đúng cấu hình trước khi chạy. Mỗi lượt chạy đầy đủ cần DB QA mới; bộ kiểm yêu cầu ban đầu chỉ có ba khóa seed gốc.

```bash
php tools/setup.php
php tests/run.php
STUDYMATE_DISPOSABLE_DB=1 php tests/demo_courses.php
```

Fixture MySQL dùng CHECK tạm trên `lessons` để từ chối bài mới đầu tiên, sau khi importer đã tạo khóa/chương. Fixture được gỡ trong `finally`, rồi bộ kiểm thử import lại. Không tạo trigger MySQL, không cần SUPER hoặc thay `log_bin_trust_function_creators`; kiểm được với binary logging bật và biến trust bằng 0. SQLite dùng trigger cục bộ để tạo lỗi tương đương. Chỉ dùng fixture trên DB QA dùng một lần. Nếu dừng tiến trình cưỡng bức, bỏ DB QA và tạo DB mới thay vì dùng DB đó cho demo.

Trên Compose, dùng `.env` theo README, chọn tên project QA **mới chưa có volume** và dùng cả hai tệp cấu hình. App kết nối bằng `DB_USER=studymate`, không phải root.

```bash
QA_PROJECT=tu-content-qa-20261121-new
docker compose -p "$QA_PROJECT" -f compose.yaml -f compose.qa.yaml up --build -d --wait
docker compose -p "$QA_PROJECT" -f compose.yaml -f compose.qa.yaml exec -T app php tools/setup.php
docker compose -p "$QA_PROJECT" -f compose.yaml -f compose.qa.yaml exec -T app php tests/run.php
docker compose -p "$QA_PROJECT" -f compose.yaml -f compose.qa.yaml exec -T -e STUDYMATE_DISPOSABLE_DB=1 app php tests/demo_courses.php
```

Đổi `QA_PROJECT` cho mỗi lượt kiểm đầy đủ. Khi xong, có thể dọn **đúng project QA vừa tạo** bằng `docker compose -p "$QA_PROJECT" -f compose.yaml -f compose.qa.yaml down -v`; thao tác này xóa volume QA. Các lệnh Compose là hướng dẫn cho máy có Docker; lượt sửa review này kiểm trực tiếp PHP/MySQL qua TCP, chưa chạy Compose.

Bộ kiểm xác nhận rollback giữa lượt import, bảo toàn từng dòng cũ ở 12 bảng, cấu trúc nội dung/quiz, chấm 6/6 và bảo toàn cả chỉnh sửa tay lẫn lịch sử học khi import lại. Điều kiện MySQL CHECK xem [MySQL 8.4 CHECK Constraints](https://dev.mysql.com/doc/refman/8.4/en/create-table-check-constraints.html); điều kiện quyền của fixture cũ xem [CREATE TRIGGER](https://dev.mysql.com/doc/refman/8.4/en/create-trigger.html).

## Nguồn biên soạn

Bài học, ví dụ và câu hỏi được viết riêng cho StudyMate. Các tài liệu dưới đây dùng để đối chiếu cú pháp và khái niệm. Giao diện sử dụng ảnh/theme sẵn có của dự án.

- Cấu trúc HTML được đối chiếu với MDN. [Basic HTML syntax](https://developer.mozilla.org/en-US/docs/Learn_web_development/Core/Structuring_content/Basic_HTML_syntax)
- Sự kiện JavaScript được đối chiếu với MDN. [Introduction to events](https://developer.mozilla.org/en-US/docs/Learn_web_development/Core/Scripting/Events)
- Các truy vấn SELECT/JOIN/GROUP BY được đối chiếu với tài liệu MySQL. [MySQL 8.4 SELECT](https://dev.mysql.com/doc/refman/8.4/en/select.html)

## Kết quả kiểm trên candidate

Kiểm độc lập trên Linux ngày 21/11/2026, PHP 8.4.24, SQLite 3 và MySQL Community Server 8.4.13 riêng. Trình duyệt là Chromium headless-shell 156.0.8078.79 với Playwright, kiểm ở 1366px và 390px. MySQL chạy trực tiếp qua TCP loopback trong môi trường QA, với Unix socket tắt do giới hạn môi trường. Lượt này không chạy Docker Compose hay Windows/Edge.

| Bộ kiểm | SQLite | MySQL |
| --- | --- | --- |
| Service tests hiện có qua tests/run.php | 14 đạt | 14 đạt |
| Nội dung, rollback, bảo toàn 12 bảng, quiz và import lặp qua tests/demo_courses.php | 358 đạt | 358 đạt |
| Hai lệnh CLI import lặp chạy đồng thời | Cả hai giữ nguyên 12 bảng | Cả hai giữ nguyên 12 bảng |
| Browser, luồng học và ví dụ HTML/CSS/JavaScript | 136 kiểm tra đạt | 136 kiểm tra đạt |
| Chạy bốn khối SQL và đối chiếu kết quả | Đạt | Đạt |
| Kiểm FK và UNIQUE của bài SQL | Hai thao tác sai bị từ chối | Hai thao tác sai bị từ chối |

Browser kiểm Student đăng nhập, tìm đủ ba khóa mới, xem chi tiết và 12 bài thật, đăng ký và hoàn thành cả ba khóa, nộp mỗi quiz đạt 6/6 = 100%, xem đủ sáu giải thích. Nội dung code hiển thị trong khối pre/code. Trang danh sách và trang học vừa viewport 390px, không có JavaScript page error. Các ví dụ form, box model, Grid, DOM và localStorage đã chạy trong trình duyệt. Các đoạn JavaScript tính toán được chạy riêng và đối chiếu output.

Bộ hồi quy tests/quan/run.py trên SQLite chạy thêm 487 runner checks đạt bằng adapter PHP native cho môi trường thiếu Docker. Adapter giữ các assertion của runner, thay thao tác container bằng tiến trình PHP và DB dùng một lần. Đây là kiểm native, tách khỏi kết quả Compose do nhóm cung cấp. Các số trong bảng là phạm vi từng suite, có thể bao gồm kiểm trùng luồng.

Lint bốn tệp PHP mới đạt. Các thay đổi trong candidate chỉ gồm năm tệp về nội dung, importer, CLI, kiểm thử và tài liệu. Khi bàn giao PR, reviewer có thể chạy lại các lệnh QA ở trên để đối chiếu trên cùng commit.

AI được tắt trong DB QA của lượt này. Nghiệm thu Gemini thật trên bài mới tiếp tục dùng cấu hình và kịch bản nhóm đã thống nhất.

## Sửa góp ý review về fixture MySQL

Đã tái hiện fixture cũ từ `ae8a6b1` bằng user app trên MySQL 8.4.13 với
`log_bin=1`, `log_bin_trust_function_creators=0`: lỗi 1419 ở CREATE TRIGGER,
snapshot dữ liệu 12 bảng không đổi. Log lỗi cũ được giữ riêng với log đạt.

Bản sửa thay trigger MySQL bằng CHECK tạm có tên `demo_content_failure`.
Importer và lệnh CLI không đổi. Bản sửa đồng thời chỉnh viết hoa đầu câu trong
nội dung và giải thích, giữ nguyên code fence, tiêu đề khóa, cấu trúc và đáp án.

Đã chạy đúng ba lệnh PHP native ở mục tái kiểm, bằng user app chỉ được cấp
quyền trên DB QA, không có SUPER hay quyền quản trị toàn server. MySQL giữ
binary logging bật và trust bằng 0. Mỗi driver đạt **14 service checks và
358 content/importer checks**. Hai lệnh CLI import lại đồng thời trên mỗi driver
giữ nguyên dữ liệu 12 bảng; fixture được dọn sau lượt kiểm.

Bằng chứng và fingerprint mã được kiểm nằm tại
`docs/evidence/tu-pr19-mysql-fixture-2026-11-21/`. Runner tái hiện là
`tests/tu/verify_demo_mysql.py`. Đây là kiểm PHP/MySQL native, không phải kết quả
chạy container Compose. Lượt sửa review không chạy lại browser, Edge hoặc Gemini.
