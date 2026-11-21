# Virtual Study Buddy — 21/11/2026

Runtime `16ae7aa744c35e09ca2b57e6362f0038663559fa`. Nhánh `codex/an-virtual-study-buddy-20261121`, dựa trên bản không gian học tập PR #21 tại `2a8da10`. Đây là tính năng riêng để review và tích hợp vào nhánh bản thử.

## Hành vi

- Sukuna chibi nhỏ ở góc màn hình dành cho Student. Khi học có tư thế đọc sách; khi chọn Nghỉ ngắn trong không gian học tập chuyển sang uống cà phê; XP mới từ tiến độ đã lưu kích hoạt ăn mừng một lần khoảng 6,5 giây.
- Khi không có hoạt động học được ghi nhận trong ít nhất ba ngày, hiển thị lời nhắc nhẹ tối đa một lần/ngày trên trình duyệt đó. Có tắt nhắc, thu gọn nhân vật và nút Buddy để mở lại. Không phát âm thanh, không gửi push/email và không gọi dịch vụ AI.
- XP tính bằng **20 × số bài đã hoàn thành + 50 × số quiz khác nhau đã đạt**. Toàn bộ số liệu lấy từ DB bằng một truy vấn tổng hợp có tham số user ID từ session, không nhận XP từ client. Bài cũ được tính ngay, học lại hoặc thi lại cùng quiz không cộng trùng.
- Đạt mốc XP tự mở đồ, không trừ XP: kính 50; kimono đỏ 100; vương miện 150; áo khoác học viên 250. Kimono trắng và không phụ kiện có sẵn.
- XP theo dữ liệu tài khoản, dùng được khi đổi thiết bị. Trang phục đang chọn, thu gọn và lựa chọn nhắc lưu bằng localStorage theo user ID, chưa đồng bộ thiết bị. Món đồ lưu trong trình duyệt nhưng chưa đạt XP bị đưa về mặc định.
- Chỉ Student tải Buddy; guest/Instructor/Admin không có widget hoặc request hình Buddy. Không JavaScript thì widget ẩn và luồng học bình thường vẫn dùng được. Storage bị chặn/sai vẫn có chế độ dùng tạm.
- Hỗ trợ Escape/focus, giảm chuyển động và màn hình 390/768/1366 px. Tủ đồ cuộn được và nút đóng cố định. Nhân vật thu nhỏ khi nhập văn bản/làm quiz; né player YouTube, nằm phía trên player trên điện thoại và tạm ẩn ở màn hình thấp có player.

## Mã và dữ liệu

`app/study_buddy.php` chứa tổng hợp XP, thời gian hoạt động và danh mục đồ; layout chỉ gọi cho Student. JS/CSS riêng quản lý phần trình bày. `study-space.js` phát event trạng thái focus khi mode/enabled/running đổi để Buddy đổi tư thế. Không ghi XP trong event hoặc timer.

Không sửa schema, seed, ID, handler, CSRF, quiz grading, API Gemini hay cấu hình CSP. Truy vấn đọc ba nhóm user-indexed aggregate, không có query theo từng thẻ hoặc từng trang phục. Lần đọc profile không thay dữ liệu ở cả 12 bảng.

Bộ 12 WebP alpha khoảng 336 KB được tạo bằng imagegen; [nguồn](../public/assets/buddy/SOURCES.md), [toàn bộ prompt và cách xuất](STUDY_BUDDY_IMAGE_PROMPTS_2026-11-21.md). Đây là fan-art companion theo yêu cầu, không phải asset chính thức của series.

## Kiểm mới

| Bộ kiểm | Kết quả | Phạm vi |
| --- | --- | --- |
| XP/DB in-memory | 15 đạt | XP từ bài/quiz, replay UUID, thi lại, tài khoản khác, 12 bảng không đổi khi đọc, inactivity và ngày tương lai |
| Chrome + Edge | 99 đạt, 0 page error | POST học/quiz thật, khóa/mở đồ, giả mạo tùy chỉnh món khóa, thu gọn/nhắc, session và account isolation, no-JS/storage, pose, responsive, player hai bên |
| Header cuối | 4 đạt | Nút đóng trên mobile nhận click sau khi cuộn/chọn đồ, cả hai browser |
| Hồi quy không gian học | 158 đạt, 0 page error | Đồng hồ, âm nền, storage, YouTube mẫu thật chơi trên Chrome/Edge, cuộn/đổi tab/đổi bên, luồng học native |

Bộ 158 chạy trước chỉnh CSS cuối chỉ thuộc header Buddy. Luồng media/học không đổi sau đó; bộ 99 cuối và 4 kiểm header dùng CSS cuối. Browser: Chrome 157.0.8103.80, Edge 157.0.4341.60, Windows, SQLite QA riêng.

Lượt chuẩn bị browser đầu dùng lại cookie trước khi chuyển tài khoản nên một context sau đó đã mất session; log giữ ở diagnostics/stale-session-context.json. Runner cuối lấy storageState mới sau lần đăng nhập hiện hành. Cơ chế thu hồi session của ứng dụng được giữ nguyên.

Giới hạn: chưa chạy MySQL/Docker hoặc Gemini thật; chưa kiểm Safari/điện thoại vật lý. Rules main yêu cầu một Approve từ tài khoản khác; PR này không bỏ qua review đó. PR #19 thêm nội dung và PR #22 của Quân thêm giao diện/i18n/theme cần đối chiếu khi chốt bản tích hợp; chưa tự nhận Buddy đã kiểm trên PR #22.

## Chạy lại

PHP 8.3+ / PDO SQLite trong một checkout đã có config:

```sh
php tests/study_buddy.php
```

Lệnh trên luôn chuyển sang SQLite `:memory:` trước lần DB đầu tiên. Để chạy browser, tạo **hai checkout QA riêng**, config SQLite trỏ đến tên file kết thúc `_test.sqlite`, chưa có DB. Chạy `php tools/setup.php`, rồi `STUDYMATE_DISPOSABLE_DB=1 php tests/study_buddy_fixture.php` trên từng checkout. Fixture chỉ nhận seed mới 5 users/3 courses/1 progress/1 enrollment/0 attempt và tạo Student 2 có 340 XP, hoạt động cũ 5 ngày. Không dùng DB demo.

Chạy hai server và đặt `QA_ALLOW_WRITES=1`, `QA_PLAYWRIGHT_MODULE`, `QA_OUTPUT`, `QA_BUDDY_APPS` là JSON danh sách `name`, `path` executable, `base` loopback của mỗi app rồi `node tests/study-buddy.cjs`. Mỗi engine cần DB mới; runner gửi POST thật và thay tiến độ QA. Hồi quy dùng `tests/study-space.cjs` theo báo cáo không gian học tập.

## Ảnh và log

[Đọc sách/mobile](evidence/an-study-buddy-2026-11-21/panel-390.png) · [Nghỉ cà phê](evidence/an-study-buddy-2026-11-21/coffee.png) · [Ăn mừng](evidence/an-study-buddy-2026-11-21/celebrate.png) · [Tủ đồ](evidence/an-study-buddy-2026-11-21/wardrobe-mobile.png) · [Né player](evidence/an-study-buddy-2026-11-21/youtube-mobile.png).

[99 kiểm browser](evidence/an-study-buddy-2026-11-21/results.json) · [15 kiểm XP](evidence/an-study-buddy-2026-11-21/xp-checks.txt) · [158 hồi quy](evidence/an-study-buddy-2026-11-21/regression/results.json) · [Source hashes](evidence/an-study-buddy-2026-11-21/summary.json) · [Checksums](evidence/an-study-buddy-2026-11-21/checksums.json).
