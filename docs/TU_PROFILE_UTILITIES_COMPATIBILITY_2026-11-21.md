# Tú — tương thích hồ sơ/ảnh với tiện ích học tập

Đối chiếu ngày 21/11/2026: main `6b79ef34d632aebc07884fa981cc75b162cd4bce`, PR #26 `eb08da64de92b3a6efa22b1d48deee81f2561038`, PR #27 `12b819a5676b7ecf0ba439dd3a32884ab59d2380`.

PR #27 đã chuyển base sang main; head giữ nguyên. Không tạo commit hoặc push mới. PR #26 đã merge vào main tại `49db2d07341967b94c0e16528e0dd2b37865f3bc` trong lúc kiểm. Tree main mới bằng tree #26 đã kiểm; candidate #27 + main sau khi giải quyết hai conflict cũng bằng toàn bộ tree mã nguồn đã kiểm. Candidate chờ duyệt commit và push, chưa phải SHA tích hợp của main.

## Kết quả thực tế

Linux PHP 8.4.24/GD 2.3.3; Chromium headless-shell 156.0.8078.79; SQLite và MySQL 8.4.13. Bản sao app/DB dùng một lần, AI tắt. MySQL dùng tài khoản QA riêng có quyền trong DB test. Dọn đúng DB/user tạo trong lượt chạy.

| Bộ kiểm trên bản ghép #26 + #27 | SQLite | MySQL |
|---|---:|---:|
| Hồ sơ/ảnh/browser/HTTP | 152 | 152 |
| Tiện ích học tập hiện có của An, một Chromium | 78 | 78 |
| Giao thoa và sidebar | 360 | 360 |

360 assertions gồm 52 kiểm tra giao thoa và 308 kiểm tra lesson outline của Quân. Các số có thể trùng luồng; không tính là số tính năng độc lập. Hai app baseline/combined trong lượt giao thoa đã nhập đủ sáu khóa, có lịch sử học/quiz fixture.

Đã kiểm upload avatar, preview blob, lưu tên/giới thiệu; hủy đổi ngôn ngữ chỉ một hộp thoại và giữ file/text; quay lại bài học giữ ghi chú, XP và snapshot enrollment/progress/quiz. Header hiển thị avatar với tiện ích. Hồ sơ không nạp Buddy/notes/study-space. Không lỗi JavaScript. VI/EN × sáng/tối tại 390/768/834/991/1366 px. Outline kiểm thêm 767/992 px, chiều cao 500/600/900 và click tọa độ thật.

Bộ tiện ích hiện có kiểm autosave khi đổi bài/ngôn ngữ, lỗi storage và xác nhận gộp với AI draft, Buddy trong sidebar/không dịch khi bật music dock, theme/i18n/no-JS; không kiểm phát YouTube thật trong lượt này.

Trước đó cùng lượt đã chạy #27 + main riêng: 152 hồ sơ/ảnh mỗi driver, migration thêm sáu cột rồi chạy lại không thêm, giữ giá trị cũ của 12 bảng. Bản ghép #26 + #27 cũng đạt migration và 14 service checks trên fixture cũ, 14 trên schema mới mỗi driver. PHP lint: 50 tệp bản main + #27; 57 tệp bản ghép.

## Hai điểm cần giữ khi giải quyết conflict

- `app/bootstrap.php`: giữ `$studySpaceRequest`, `$playerScripts` và Referrer-Policy của #26; thêm `blob:` trong img-src cho preview của #27; giữ currentUser/profile-media requires của #27.
- `app/layout.php`: giữ toàn bộ điều kiện `$withUtilities`, script/CSS tiện ích và data-unsaved-notes-message của #26; đồng thời giữ script/CSS profile-media và avatar header của #27.

`merge-reference.patch` ghi nội dung hai tệp sau khi giải quyết trên nền #26; chỉ dùng tham chiếu khi merge, không áp dụng mù vào branch đang có #27. PR #27 không chứa toàn bộ tiện ích #26.

## Giới hạn và bằng chứng

Chưa chạy Edge/Safari, Docker/Compose runtime, điện thoại vật lý, Gemini thật hoặc YouTube playback. Không thay kết quả của An/Quân cho các môi trường đó. Quân cần kiểm SHA sau merge thực tế.

Bằng chứng mới nằm tại `docs/evidence/tu-profile-utilities-2026-11-21/`: JSON assertions, migration, checksum và patch tham chiếu. Runner giao thoa nằm tại `tests/tu/profile_utilities_cross.py` và `tests/tu/profile_utilities_cross.cjs`; runner dùng nguồn/baseline/runtime/datadir QA tường minh. Các script tiện ích và outline gốc nằm trong tests của An/Quân. Ảnh/log đầy đủ còn ở thư mục QA cục bộ ngoài checkout. Báo cáo, runner và bằng chứng mới chưa commit/push; chưa có URL GitHub cho các tệp này.

Lệnh chạy runner (các đường dẫn phải trỏ tới tài nguyên QA riêng):

```sh
python3 tests/tu/profile_utilities_cross.py --baseline /path/to/profile-before26 --runtime /path/to/qa-runtime --mysql-datadir /path/to/disposable-mysql-data --artifacts /path/to/results
```

Runner không gọi provider AI. MySQL phải đang dừng trước khi runner sử dụng datadir QA đã khởi tạo. Bản baseline là #27 ghép main `6b79ef3`; source là candidate #27 ghép main `49db2d0`.

Runner với các tham số tường minh ở trên đã chạy lại trên candidate sau #26: SQLite và MySQL đều đạt lại 78 tiện ích + 360 giao thoa/sidebar, không JavaScript page error.
