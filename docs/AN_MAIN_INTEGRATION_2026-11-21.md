# PR #26 trên main sau khi tích hợp #19 và #22

Main đã merge #22 tại `6b79ef34d632aebc07884fa981cc75b162cd4bce`, bao gồm #19 của Tú và bản sửa sidebar #22 tại `bae1a8a`. Nhánh #26 đã nhận main bằng merge commit `198de73`, giữ lịch sử công khai của các phần đã review. Mốc mã hoàn tất kiểm trước tài liệu bàn giao: `8d8bb7a5e2b532716eca45268872fce56f11972c`.

## Diff còn lại và cách tích hợp

So với main trên, thay đổi chỉ còn ghi chú, Góc tập trung, Buddy/XP, bản dịch/CSS tiện ích, cơ chế bảo vệ bản nháp khi đổi ngôn ngữ và các kiểm thử/bằng chứng liên quan. Trang chủ, logo, font/ảnh, dashboard, `app.css`, các CSS kính và handler nghiệp vụ của main được giữ nguyên. Không mang lại landing/banner IT/logo cũ, không thêm migration hoặc sửa phần hồ sơ/upload.

Xung đột merge duy nhất là `.gitattributes`: giữ cả quy tắc bằng chứng của An và Quân. Bằng chứng cũ không bị ghi lại hoặc tính thành lượt kiểm mới.

Hai vấn đề trong CSS tiện ích được xử lý khi chạy bộ sidebar mới trên bản tích hợp:

- Khung chứa outline và Buddy luôn giới hạn chiều cao theo viewport trên tablet/desktop, có cuộn nội bộ, cách header 100px; điện thoại vẫn nằm trong luồng trang, không giới hạn chiều cao. Trước sửa, khung này vượt đáy viewport 600px khi thêm Buddy dù nút bài vẫn còn nhận click.
- Bỏ các quy tắc dịch vị trí/ẩn theo dock nhạc dành cho Buddy nổi cũ. Buddy hiện là thẻ trong khu vực học. Trước sửa, trạng thái dock hiện làm thẻ dịch lên 340px trên điện thoại và sang ngang; sau sửa, dock bên trái/phải không đổi vị trí của thẻ. Có thể thu gọn/khôi phục Buddy bằng điều khiển của chính tiện ích.

## Dữ liệu QA

Các app dùng SQLite riêng trong Temp. Đã seed dữ liệu gốc rồi nhập ba khóa của #19; thư viện có sáu khóa, ba khóa bổ sung giữ ID 4/5/6. Nhập lại trên DB có tiến độ và lịch sử quiz giữ nguyên snapshot đủ 12 bảng. Các fixture ghi dữ liệu được tách riêng cho notes, Buddy, media và đối chiếu main.

Không chạy setup, reset hoặc importer trên DB demo đang dùng. Cập nhật code không tự nhập dữ liệu mới. Muốn nhập khóa mẫu vào môi trường khác, dùng cơ chế `tools/seed_demo_courses.php` theo hướng dẫn #19, sau khi cấu hình DB đích đúng.

## Kiểm lại trên bản tích hợp

Windows, PHP 8.3.31, Chrome 157.0.8103.80 và Edge 158.0.4370.33. Các con số là xác nhận của từng runner, có luồng trùng nhau; không cộng thành số chức năng độc lập.

| Bộ kiểm | Đạt | Nội dung |
| --- | ---: | --- |
| Tích hợp tiện ích | 155, cả hai browser | VI/EN, sáng/tối, 390/768/1366; ảnh viewport trang chủ khớp main; notes khi đổi bài/ngôn ngữ; một xác nhận khi lỗi lưu; Buddy không dịch vị trí khi dock hai bên hiện |
| Ghi chú | 93, cả hai browser | Tự lưu, tệp `.txt`, Unicode, đổi bài/hoàn thành, lỗi lưu/bản lưu lỗi, hai tab cùng sửa, chọn phiên bản, tách tài khoản, CSRF/action không bị thay |
| Buddy/XP | 99, cả hai browser | Trạng thái đọc/nghỉ/ăn mừng/nhắc học, tủ đồ/phụ kiện, quiz không cộng trùng, thu gọn/khôi phục, giảm chuyển động, bố cục thẻ |
| Góc tập trung/media | 158, cả hai browser | Đồng hồ, cảnh/video, Web Audio, yêu thích, dock và cuộn; video YouTube mẫu **phát thật trên cả Chrome và Edge** |
| Đóng Góc tập trung sau cuộn | 393, cả hai browser | 56 tổ hợp VI/EN × sáng/tối và các biên 390/767/768/834/991/992/1366 |
| Sidebar chọn bài của Quân | 308 mỗi browser | 44 tổ hợp mỗi browser; VI/EN × sáng/tối, cao 500/600/900px; hai thao tác cuộn, click tọa độ thật gửi một POST và chọn bài đúng; mobile/menu/overflow |
| PHP XP / nghiệp vụ | 15 / 14 | XP trong bộ nhớ, không ghi thay đổi khi đọc; fixture chấm quiz/UUID/FK/progress riêng |
| Nhập lại khóa mẫu | 3 xác nhận | Ba khóa hiện có được giữ, cùng ID và snapshot 12 bảng không đổi |

PHP lint 29 tệp app/public/database, Node syntax và whitespace phần mã đạt. `canonical.json` ghi 15 tệp thiết kế/handler/nội dung chính khớp main; `source.json` ghi fingerprint mã bàn giao.

Ca xung đột notes được làm ổn định bằng hai tab thật: thiết lập hai bản nháp và lưu ở tab B trong cùng một tác vụ trình duyệt, sau đó chờ sự kiện storage và đồng hồ tự lưu bình thường. Không dùng thời gian mô phỏng trong bản kiểm cuối; logic autosave 700ms của sản phẩm không đổi. Ca landscape Buddy được đối chiếu theo dạng thẻ trong luồng trang, thay kỳ vọng của widget nổi cũ.

Các lượt chẩn đoán ban đầu (khung rail, dịch Buddy theo dock, timeout/clock trong harness notes) được giữ tách dưới tên `diagnostic-*`; chúng không phải kết quả đạt của lượt cuối. Bản kiểm notes bằng hai tab thật cuối cùng đạt đầy đủ trên cả hai browser.

**Giới hạn:** không chạy MySQL/Compose, macOS/Safari, điện thoại thật hoặc Gemini thật trong lượt tích hợp này. Kết quả MySQL/Gemini của các thành viên trước đó không được tính là lượt chạy mới ở đây.

## Bằng chứng để review cuối

[JSON, ảnh, fingerprint và checksum](evidence/an-main-integration-2026-11-21/).

- [Trang chủ theo main](evidence/an-main-integration-2026-11-21/home-main-vi-light.png)
- [Ghi chú tiếng Anh, tối, điện thoại](evidence/an-main-integration-2026-11-21/notes-en-dark-390.png)
- [Góc tập trung desktop](evidence/an-main-integration-2026-11-21/tools-vi-light-1366.png)
- [Sidebar 834px](evidence/an-main-integration-2026-11-21/outline-834.png), [991px](evidence/an-main-integration-2026-11-21/outline-991.png)
- [Buddy/tủ đồ](evidence/an-main-integration-2026-11-21/buddy-wardrobe-mobile.png), [nhạc điện thoại](evidence/an-main-integration-2026-11-21/music-mobile.png)

PR #26 cần base `main`; không ghép lại #21/#23/#24/#25. Nhóm đối chiếu diff với main và review head mới trước khi merge #26. Các mốc c050b35/9abc2cb và số kiểm cũ trong báo cáo trước vẫn là bằng chứng lịch sử riêng.
