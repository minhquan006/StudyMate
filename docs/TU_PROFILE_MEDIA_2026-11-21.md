# Hồ sơ và hình ảnh trên giao diện #22

Nền: PR #22 tại `7f374926acdf3b2c6a1fa9e08b204b39e7777299`.
Nhánh triển khai: `tu/ho-so-va-hinh-anh`. Giữ giao diện kính, VI/EN, sáng/tối
và các action/CSRF/ID của form cũ. Không ghép landing/banner hoặc tiện ích #21.

## Chức năng

- Cả ba vai trò sửa họ tên (2–80 ký tự), ngày sinh tùy chọn (1900 đến hôm nay),
  giới thiệu (tối đa 2.000 ký tự) và avatar của chính tài khoản đang đăng nhập.
  Email, role, ID và auth_version không được sửa qua form hồ sơ.
- Instructor thêm bằng cấp/chuyên môn (tối đa 1.000 ký tự) và ảnh của khóa mình
  sở hữu, gồm khóa nháp/đã xuất bản. Giới thiệu và bằng cấp hiển thị dạng văn bản
  trên trang khóa công khai. Ngày sinh chỉ xuất hiện ở hồ sơ riêng.
- Admin sửa ảnh danh mục. Tạo danh mục hoặc khóa mới bằng form hiện có, lưu tên
  trước rồi cập nhật ảnh ở form riêng.
- Có xem trước ảnh, thay ảnh và xóa ảnh. Ảnh tải lên được ưu tiên trên thẻ/trang
  chi tiết khóa và thẻ lĩnh vực. Xóa ảnh hoặc file bị thiếu dùng ảnh mẫu #22;
  avatar trống dùng chữ đầu tên. Các label và lỗi mới có bản dịch tiếng Anh.
- Form vẫn hoạt động khi tắt JavaScript. Đổi ngôn ngữ khi đang sửa dùng cơ chế
  xác nhận của #22; hủy xác nhận giữ bản nháp và file đã chọn.

## Thiết lập và migration

PHP 8.3+ cần PDO với driver tương ứng, **GD, Fileinfo**; **Exif** để xoay JPEG
theo hướng chụp của điện thoại. Dockerfile cài GD/JPEG/PNG và Exif. Máy native
cần cài/bật các extension này trước khi thử tải ảnh. Kiểm bằng `php -m`.

Sau khi backup DB và thư mục ảnh, cập nhật source rồi chạy:

```bash
php tools/setup.php
```

Setup tạo schema mới hoặc thêm sáu cột nullable còn thiếu trên DB đã có dữ liệu.
Cũng có thể chạy riêng migration trên DB đã có đủ bảng:

```bash
php tools/migrate_profile_media.php
```

Migration chạy lại được, không reset seed, không tạo lại ID hoặc cập nhật dòng
cũ. SQLite dùng khóa file, MySQL có thêm advisory lock theo DB để tuần tự hóa
các host chạy migration. Tài khoản triển khai cần quyền ALTER trên đúng DB;
không cần SUPER hay thay cấu hình binary logging.

| Bảng | Cột nullable mới |
|---|---|
| users | birth_date, bio, qualifications, avatar_path |
| courses | image_path |
| categories | image_path |

Schema PHP và `database/schema.mysql.sql` có cùng các cột cho DB mới. Tệp SQL
không thay thế migration cho DB cũ. Trước khi dùng tính năng ghi, hoàn tất setup
hoặc migration. Phiên bản mới đọc được hồ sơ cũ chưa có giá trị ở các cột này.

Với Compose, dựng lại app để có extension và cấu hình upload:

```bash
docker compose up --build -d
```

Entrypoint vẫn chờ DB và chạy setup. Thư mục `storage/uploads` được tạo với owner
www-data và mode 700; file ảnh mode 600. Volume `private_storage` hiện có giữ ảnh
qua restart/rebuild. Không dùng `down -v` khi cập nhật demo.

## Tệp ảnh và phân quyền

- Nhận một JPEG/PNG, tối đa 2 MiB, mỗi cạnh không quá 4096 px và không quá
  8.000.000 điểm ảnh. Giới hạn request là 4 MiB; quá giới hạn trả 413. Ảnh sai
  hoặc vượt giới hạn file trả 422. PHP upload_max_filesize được đặt 3M để app
  có thể báo lỗi cho file vượt giới hạn 2 MiB.
- Kiểm cấu trúc `$_FILES`, lỗi upload, extension, MIME thực tế, kích thước và
  giải mã GD. Không tin MIME/filename do client khai báo. Mã hóa lại pixels và
  thu ảnh dài hơn 1600 px về cạnh dài 1600; bỏ metadata và payload nối đuôi.
- Tên file là 128-bit ngẫu nhiên, đuôi do server chọn; lưu ngoài public. Không
  nhận đường dẫn file hoặc URL ảnh từ người dùng.
- `/media.php` chỉ tra file qua đối tượng DB/ID hợp lệ. Ảnh khóa chưa công khai
  giới hạn owner/admin, hoặc học viên đã đăng ký nếu khóa được lưu trữ.
  Avatar học viên/admin giới hạn chính chủ/admin; avatar giảng viên còn hoạt
  động và ảnh danh mục được dùng công khai. Không phục vụ file theo đường dẫn
  người dùng gửi lên. Có nosniff, Content-Type cố định và cache private/no-store.
- CSRF và role/owner được kiểm trước khi nhận ảnh. Ghi DB trong transaction;
  nếu DB lỗi thì xóa file mới. Chỉ xóa ảnh cũ sau khi DB commit. Xóa đối tượng
  khóa nháp/danh mục cũng dọn ảnh sau khi xóa DB thành công.
- Nếu tiến trình bị tắt đột ngột giữa lúc ghi file và commit, có thể còn file
  mồ côi ngoài public. File đó không được endpoint phục vụ vì không có tham
  chiếu DB; cần đối chiếu DB trước khi dọn thủ công.

Tham khảo: [PHP uploads](https://www.php.net/manual/en/features.file-upload.post-method.php),
[GD decoding](https://www.php.net/manual/en/function.imagecreatefromstring.php).

## Backup và chuyển môi trường

Backup SQL v2 hiện có chỉ chứa DB. **Ảnh cần backup riêng cùng thời điểm với DB**.
Dừng thao tác ghi vào app trong suốt hai bước; giữ DB đang chạy để dump. Lưu
backup ở nơi riêng, không nằm trong source/ZIP hoặc public. Với Compose đã có
thư mục uploads và đã dừng thao tác ghi:

```bash
docker compose stop app
sh tools/db_backup.sh /private/path/studymate.sql
docker compose run --rm --no-deps --entrypoint tar app -C /var/www/html/storage -czf - uploads > /private/path/studymate-uploads.tar.gz
docker compose start app
```

Nếu backup lỗi, giữ nguyên các file/log để xử lý và chủ động khởi động lại app
khi an toàn; không coi cặp backup chưa đủ là bản hoàn chỉnh. Giữ file checksum
của cả SQL lẫn archive và đối chiếu trước khi chuyển máy.

Restore SQL vào **DB mới** theo quy trình v2, rồi khôi phục archive ảnh tin cậy
vào `storage/uploads` của project đích trong khi app chưa nhận thao tác ghi.
Giữ đúng tên file như trong DB. Có thể giải nén qua container chạy user www-data:

```bash
docker compose run --rm --no-deps --user www-data --entrypoint tar app -C /var/www/html/storage -xzf - < /private/path/studymate-uploads.tar.gz
```

Chỉ giải nén archive do nhóm tạo và đã kiểm nội dung/checksum, vào project đích
riêng. Native: copy cả `storage/uploads` cùng DB; cấp quyền đọc/ghi cho user PHP
và không đặt uploads dưới docroot. File thiếu sẽ dùng ảnh dự phòng, nhưng đó
không phải bản restore đầy đủ. Không đưa ảnh cá nhân hoặc runtime DB vào git.

## Phối hợp tích hợp

Các điểm chung với phần An có thể sửa: `app/layout.php`, `app/views.php`,
`app/glass_views.php`. Giữ các hook CSS/JS hồ sơ khi ghép tiện ích học tập; không
thay `forms.js` hoặc cơ chế lựa chọn ngôn ngữ/giao diện của #22. Sau khi #22
merge, cập nhật nhánh theo main mới rồi kiểm lại cùng phần của An.

## Kiểm thực tế

Linux, PHP 8.4.24/GD 2.3.3, Chromium headless-shell 156.0.8078.79 và MySQL
8.4.13. MySQL chạy bằng user QA chỉ được cấp quyền trên các DB QA riêng.

| Bộ kiểm | SQLite | MySQL |
|---|---:|---:|
| Browser/HTTP/ảnh/quyền/VI–EN/sáng–tối | 152 đạt | 152 đạt |
| Service trên fixture cũ có lịch sử, trước migration | 14 đạt | 14 đạt |
| Service trên DB khởi tạo mới bằng schema mới | 14 đạt | 14 đạt |
| Migration DB có dữ liệu: thêm 6 cột, chạy lại và setup lặp | Đạt | Đạt |
| Đối chiếu dữ liệu gốc trong 12 bảng sau migration | Giữ nguyên | Giữ nguyên |
| Ảnh viewport 390/1366 với bốn tổ hợp ngôn ngữ/theme | 8 ảnh | 8 ảnh |

Các suite browser không có JavaScript page error. Có kiểm thêm tại 768 px.
Các số là lượt assertion và có thể kiểm trùng luồng, không phải số chức năng.
Ảnh kiểm dùng hình raster tổng hợp và tài khoản seed, không dùng ảnh cá nhân.
Đã xem ảnh profile sáng/tối để đối chiếu bố cục với #22.

Kiểm gồm JPEG điện thoại có EXIF Orientation=6, re-encode loại payload nối đuôi,
SVG/file PHP/nội dung giả/ảnh cắt cụt, vượt dung lượng/cạnh/số pixels, upload
dạng mảng, thay và xóa đồng thời, sai CSRF/role/owner/ID. Trường hợp DB cố ý lỗi
sau khi xử lý ảnh giữ nguyên 12 bảng và các file ảnh cũ, đồng thời dọn file mới.
Đã kiểm ảnh fallback khi file mất, avatar riêng, ảnh khóa nháp không công khai,
đổi ngôn ngữ hủy một lần xác nhận giữ draft/file, và lưu form khi tắt JS.

Bộ hồi quy SQLite hiện có đạt **487 runner checks** qua adapter PHP native tại
`tests/tu/native_regression.py`; AI dùng transport mô phỏng. Adapter giữ các
assertion của runner, thay container bằng tiến trình PHP/DB dùng một lần.
Log đầy đủ ở `regression-sqlite.txt`. Đây không phải kết quả Docker hoặc một
lượt 487 ca MySQL. PHP lint 46 tệp đạt; schema SQL khớp generator, shell/JS
syntax đạt. Whitespace của mã/tài liệu ngoài docs/evidence/ sạch; log raw giữ nguyên, kể cả khoảng trắng do runner in ra. Tất cả literal label/lỗi mới có bản dịch EN;
mục hướng dẫn mới trong README.md và Readme.txt được đối chiếu khớp nhau.

Kết quả cuối và hash source nằm trong
`docs/evidence/tu-profile-media-2026-11-21/`. Runner
`python3 tests/tu/profile_media_qa.py` tạo app/DB QA riêng, chạy migration trên
DB có tiến độ/lịch sử quiz, DB mới, kiểm HTTP/browser và dọn DB QA sau khi xong.
Runner nhận QA_PHP, QA_CHROMIUM; môi trường native mặc định dùng runtime cạnh
checkout, cần Node/Playwright và MySQL riêng như ghi trong script.

Chạy lại bộ hồi quy SQLite native bằng `python3 tests/tu/native_regression.py`.
Compose chỉ được kiểm render cấu hình volume private_storage, chưa build/run.

Không chạy Docker/Compose runtime, Edge/Safari, điện thoại vật lý hoặc Gemini
thật trong lượt này. Kiểm viewport và Chromium headless không thay cho kiểm
thiết bị thật. Các tiện ích #21 không nằm trong nhánh này.

Metadata định danh tài khoản/host/quyền DB QA gốc được giữ riêng ở local; bản công khai chỉ ghi phiên bản MySQL. Log kết quả kiểm và các ảnh QA được giữ nguyên.

Script browser nhận mật khẩu qua QA_DEMO_PASSWORD do runner lấy từ config QA tạm; không ghi literal mật khẩu vào script. Đã chạy lại 152 lượt/driver sau thay đổi này trong thư mục bằng chứng tạm riêng, giữ nguyên log cũ.
