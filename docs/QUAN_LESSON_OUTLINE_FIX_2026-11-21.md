# Sửa chọn bài học trên sidebar tablet

Review của An trên PR #22 tại `7f374926acdf3b2c6a1fa9e08b204b39e7777299` phát hiện header chặn click chọn bài sau khi cuộn trang và cuộn bên trong sidebar.

## Tái hiện và sửa

Đăng nhập Student bằng fixture riêng, mở khóa JavaScript (khóa 2), bài 8, đặt viewport 834×600 hoặc 991×600. Cuộn trang xuống 650px rồi cuộn sidebar xuống cuối. Với bản cũ, `outline.scrollTop=147`, nút bài 7 nằm ở y≈59,6–107,6. Điểm giữa y≈83,6 trúng `.glass-nav-wrap`, trong khi header kết thúc ở y=86. Click tọa độ thật không gửi POST `last_lesson` và không chuyển bài. Hai kích thước đều tái hiện; tổng 6 xác nhận, không lỗi JavaScript.

Thay đổi mã chạy duy nhất là bỏ `.outline { top: 20px; }` trong breakpoint `max-width:991px` của `public/assets/app.css`. Sidebar kế thừa `top:100px` và giới hạn chiều cao `calc(100vh - 120px)` sẵn có. Ở cùng ca 600px, nút bài 7 chuyển xuống y≈139,6–187,6 và nhận click. Quy tắc điện thoại dưới 768px vẫn cho sidebar nằm trong luồng trang; desktop tiếp tục dùng offset cơ sở.

## Kiểm tra trên bản sửa

Chạy `tests/quan/verify_display.py` trên bản sao SQLite dùng riêng, macOS Chrome **158.0.8127.36**:

- **1.052 kiểm tra Chrome** đạt, gồm **308 kiểm tra sidebar mới** qua `tests/quan/lesson_outline.cjs`.
- Sidebar bao phủ 44 tổ hợp Việt/Anh × sáng/tối: rộng 390/767/768/834/991/992/1366px, cao 600px; kiểm thêm 834/991px với chiều cao 500 và 900px.
- Tablet/desktop được cuộn trang xuống 650px, danh sách được cuộn nội bộ. Khi màn hình thấp làm hàng cần chọn ra ngoài vùng cuộn, chỉ cuộn bên trong sidebar để hàng đó xuất hiện; không dịch trang để né header.
- Dùng click tọa độ thật, xác nhận đúng một POST `last_lesson` cho khóa 2/bài 7, URL chuyển từ bài 8 sang bài 7, menu chính vẫn đóng, không tràn ngang và không lỗi JavaScript.
- Các ca hiển thị còn lại tiếp tục kiểm lưu lựa chọn, chế độ hệ thống, vai trò, ngữ cảnh tìm kiếm/phân trang, form/quiz chưa lưu và no-JS.
- **441 kiểm tra PHP** cho tùy chọn/bản dịch và **14 kiểm tra nghiệp vụ** trong fixture đạt. Kiểm cú pháp JavaScript và diff whitespace đạt.

Lượt này không chạy Edge/Safari, điện thoại thật, MySQL/Compose hoặc Gemini thật. AI tắt trong fixture; dữ liệu demo không được dùng. Runner đã dừng server và xóa bản sao SQLite sau khi kiểm.

## Bằng chứng và cách chạy lại

[Kết quả, hash nguồn và checksum](evidence/quan-outline-2026-11-21/) ghi nền trước sửa và hash nội dung đã kiểm; commit chứa bằng chứng là bản bàn giao. Kết quả cũ của #22 giữ nguyên SHA và phạm vi cũ.

| Viewport | Trước sửa | Sau sửa, trước click |
| --- | --- | --- |
| 834×600 | [Ảnh trước](evidence/quan-outline-2026-11-21/outline-before-834.png) | [Ảnh sau](evidence/quan-outline-2026-11-21/outline-after-834.png) |
| 991×600 | [Ảnh trước](evidence/quan-outline-2026-11-21/outline-before-991.png) | [Ảnh sau](evidence/quan-outline-2026-11-21/outline-after-991.png) |

Dùng lệnh `python3 tests/quan/verify_display.py` theo [hướng dẫn kiểm giao diện](../tests/quan/README.md#kiểm-giao-diện-kính-và-lựa-chọn-hiển-thị), truyền đường dẫn PHP/Node/Playwright/Chrome theo máy. Runner tự tạo DB riêng; không chạy `tests/run.php` trực tiếp trên DB demo.
