# Giao diện StudyMate theo mẫu Udemy — An, 21/11/2026

Bản bàn giao hiện tại của [PR #20](https://github.com/minhquan006/StudyMate/pull/20), nhánh `codex/an-ui-demo-20261121`, nền main `9cb5dea`. Runtime ở `768042c`; commit `dd4ba34` chỉ cập nhật runner, có cùng runtime. Các commit sau bổ sung hồ sơ/ảnh. Codex hỗ trợ An triển khai và kiểm tự động trên Windows.

## Bố cục đã sửa theo mẫu

Đã đối chiếu trực tiếp [trang chủ Udemy](https://www.udemy.com/) trên laptop và màn hình hẹp. Bản này dùng Udemy làm mẫu chính cho phần khám phá khóa học:

- Thông báo ngắn trên cùng có nút đóng; header gồm nhận diện StudyMate, menu **Khám phá**, tìm kiếm lớn, đăng nhập/đăng ký và liên kết theo vai trò.
- Banner ảnh rộng với hộp giới thiệu trắng đặt lên ảnh, chữ vừa phải, CTA tím. Trên điện thoại, hộp nội dung chuyển xuống dưới ảnh.
- Phần khám phá lĩnh vực có đoạn giới thiệu bên trái và ba thẻ hình bên phải. Số khóa lấy từ DB, chỉ tính khóa công khai. Điện thoại cuộn ngang thẻ bằng thao tác chạm hoặc bàn phím.
- Khu vực khóa học có các liên kết chủ đề và trạng thái đang chọn; lọc bằng GET `subject`, hoạt động khi tắt JavaScript. Chủ đề chưa có khóa hiện hướng phục hồi. Tối đa tám khóa thật mới nhất.
- Thẻ khóa trên trang chủ gọn, ít viền, thumbnail/tên/giảng viên/miễn phí và link chi tiết. Tại laptop 1366 px, hàng có tối đa bốn thẻ.
- Thống nhất màu tím, chữ xám đậm, nền trắng, footer tối; trang chi tiết có phần giới thiệu tối. Thư viện, khu vực Student, bài học, quiz và kết quả dùng cùng hệ thống kiểu và các bố cục của PR.

Menu Khám phá dùng `details/summary` native; đóng bằng Escape hoặc nhấp ngoài là phần bổ sung bằng `discovery.js`. Tắt JavaScript vẫn mở/đóng menu và dùng được các link, tìm kiếm và bộ lọc. `forms.js`, handler, schema, seed, ID, CSRF, request ID và quy tắc chấm điểm giữ theo dự án. Các trường hiển thị phối hợp với Tú vẫn theo [hợp đồng dữ liệu](AN_UI_REDESIGN_2026-11-21.md#hợp-đồng-dữ-liệu-và-form).

## Ảnh và nhận diện

Banner dùng ảnh chụp “Photo Of Woman Looking On Computer” của **RF._.studio _**, [Pexels photo 3059748](https://www.pexels.com/photo/photo-of-woman-looking-on-computer-3059748/), theo [Pexels License](https://www.pexels.com/license/). Tệp JPEG khoảng 98 KB được phục vụ local, trình duyệt crop bằng CSS. Người trong ảnh chỉ minh họa tự học, không được giới thiệu là người dùng/giảng viên hay người bảo trợ StudyMate. Nguồn và giấy phép nằm tại [SOURCES.md](../public/assets/images/SOURCES.md).

Thumbnail là HTML/CSS do nhóm dùng cho web, JavaScript và bảng dữ liệu. Tên, nội dung và logo chữ vẫn là StudyMate; không dùng ảnh AI hoặc tài sản hình ảnh của Udemy. Các số lượng, trạng thái, tiến độ và điểm hiển thị lấy từ dữ liệu dự án.

## Bằng chứng mới

| Bộ kiểm | Kết quả | Nguồn |
| --- | --- | --- |
| Chrome/Edge, 18 màn hình tại 390/768/1366 px và luồng học | **652 kiểm tra đạt**, 0 JavaScript page error | Runtime `768042c`, harness `dd4ba34` |
| Form review / hủy rời quiz / form ban đầu | **34 + 16 + 22 = 72 ca đạt** | `768042c` |
| Service / pagination / cấu hình model / AI offline / UTF-8 ba vai trò | **14 + 8 + 8 + 24 + 58 = 112 kiểm tra đạt** | `768042c` |
| PHP syntax | **33 tệp tracked đạt** | `768042c` |

Coverage gồm tràn ngang, nhãn trường, nút tối thiểu 44 px, skip link, contrast chữ, bảng/mã cuộn bằng bàn phím, đăng nhập, tìm/xóa lọc, tham gia học, chọn/chuyển bài, hoàn thành bài, nộp quiz và xem giải thích. Thêm kiểm ảnh local, menu Khám phá bằng Enter/Escape, đóng thông báo, cuộn nhóm lĩnh vực và lọc chủ đề trang chủ khi bật/tắt JavaScript.

Một lượt runner trước dừng ở thao tác click tìm kiếm sau fragment navigation khi JavaScript tắt, do chờ phần tử ổn định của headless browser. Giữ [diagnostic](evidence/an-ui-2026-11-21-udemy/diagnostics/native-anchor-click-run.json). Đã đối chiếu native Enter sau trạng thái đó và click nút tìm kiếm từ đầu trang; runner cuối kiểm riêng cả hai thao tác, runtime không thay đổi.

**Giới hạn:** lượt mới dùng SQLite QA trên Windows, chưa chạy lại MySQL/Docker hoặc Gemini thật, chưa kiểm điện thoại vật lý. Hỏi/Tóm tắt kiểm đúng POST/CSRF/mode và lỗi AI disabled; phản hồi dài dùng fixture có nhãn ngoại tuyến trong checkout QA tạm. Quân cần kiểm provider thật/MySQL trên SHA tích hợp cùng nội dung của Tú.

Manifest [summary.json](evidence/an-ui-2026-11-21-udemy/summary.json) ghi nguồn và SHA-256. Artifact chữ chuẩn hóa LF, ảnh dùng byte gốc; xem [checksums.json](evidence/an-ui-2026-11-21-udemy/checksums.json). Các bằng chứng ở thư mục trước là lịch sử. Các nhóm form dùng DB/quota QA riêng, không tác động DB demo của nhóm.

## Ảnh trước/sau và log

Ảnh trước dùng main `9cb5dea` và fixture SQLite service. Ảnh sau là bản Udemy mới với dữ liệu QA tương ứng; các luồng kiểm có thể thêm lịch sử attempt dùng một lần. Có 15 ảnh full-page, thêm ảnh viewport trang chủ và menu.

| Màn hình | Trước laptop | Sau laptop | Trước mobile | Sau mobile |
| --- | --- | --- | --- | --- |
| Trang chủ | [Ảnh](evidence/an-ui-2026-11-21/before/home-1366.png) | [Ảnh](evidence/an-ui-2026-11-21-udemy/browser/home-1366.png) | [Ảnh](evidence/an-ui-2026-11-21/before/home-390.png) | [Ảnh](evidence/an-ui-2026-11-21-udemy/browser/home-390.png) |
| Thư viện | [Ảnh](evidence/an-ui-2026-11-21/before/courses-1366.png) | [Ảnh](evidence/an-ui-2026-11-21-udemy/browser/courses-1366.png) | [Ảnh](evidence/an-ui-2026-11-21/before/courses-390.png) | [Ảnh](evidence/an-ui-2026-11-21-udemy/browser/courses-390.png) |
| Chi tiết | [Ảnh](evidence/an-ui-2026-11-21/before/detail-1366.png) | [Ảnh](evidence/an-ui-2026-11-21-udemy/browser/detail-1366.png) | [Ảnh](evidence/an-ui-2026-11-21/before/detail-390.png) | [Ảnh](evidence/an-ui-2026-11-21-udemy/browser/detail-390.png) |
| Học | [Ảnh](evidence/an-ui-2026-11-21/before/learn-1366.png) | [Ảnh](evidence/an-ui-2026-11-21-udemy/browser/learn-1366.png) | [Ảnh](evidence/an-ui-2026-11-21/before/learn-390.png) | [Ảnh](evidence/an-ui-2026-11-21-udemy/browser/learn-390.png) |
| Quiz | [Ảnh](evidence/an-ui-2026-11-21/before/quiz-1366.png) | [Ảnh](evidence/an-ui-2026-11-21-udemy/browser/quiz-1366.png) | [Ảnh](evidence/an-ui-2026-11-21/before/quiz-390.png) | [Ảnh](evidence/an-ui-2026-11-21-udemy/browser/quiz-390.png) |
| Kết quả | [Ảnh](evidence/an-ui-2026-11-21/before/result-1366.png) | [Ảnh](evidence/an-ui-2026-11-21-udemy/browser/result-1366.png) | [Ảnh](evidence/an-ui-2026-11-21/before/result-390.png) | [Ảnh](evidence/an-ui-2026-11-21-udemy/browser/result-390.png) |

Ảnh bổ sung: [Student laptop](evidence/an-ui-2026-11-21-udemy/browser/student-1366.png), [Student mobile](evidence/an-ui-2026-11-21-udemy/browser/student-390.png), [AI ngoại tuyến](evidence/an-ui-2026-11-21-udemy/browser/ai-offline-fixture-390.png), [menu Khám phá](evidence/an-ui-2026-11-21-udemy/explore-desktop.png).

Log: [UI](evidence/an-ui-2026-11-21-udemy/browser/results.json), [form review](evidence/an-ui-2026-11-21-udemy/forms/review/results.json), [hủy quiz](evidence/an-ui-2026-11-21-udemy/forms/quiz-exit/results.json), [form ban đầu](evidence/an-ui-2026-11-21-udemy/forms/original/results.json), [PHP](evidence/an-ui-2026-11-21-udemy/php-checks.txt). Cách chuẩn bị và chạy lại ở [hướng dẫn runner](AN_UI_DEMO_2026-11-21.md#chạy-lại-phần-ui), chỉ dùng DB/loopback QA riêng. Runner hiện tại có thêm fingerprint `discovery.js` và ảnh banner.
