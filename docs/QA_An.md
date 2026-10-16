# QA giao diện Public và Student - Lê Nguyễn Vạn An

- Ngày: 15/10/2026
- Baseline: SRS/Project Specification 2.2; clone từ GitHub `main` tại `f142c53`.
- Môi trường: PHP 8.3.6, PDO SQLite, DB seed **dùng thử riêng**; HTTP tại `http://127.0.0.1:8123`.
- Người thực hiện kiểm tra HTTP và chuẩn bị báo cáo: Codex hỗ trợ An. An cần tự kiểm tra lại bằng trình duyệt Windows/MySQL và xác nhận trước khi báo Done.
- Phạm vi: `app/views.php`, `app/layout.php`, `public/assets/app.css`. Không sửa service/action hay panel AI.

## Kiểm tra chức năng trên bản clone `main`

Bảng dưới là HTTP test với session và CSRF thật qua `public/index.php`. Tài khoản Student mới được tạo trong DB SQLite dùng thử. “Đạt” ở đây xác nhận response và dữ liệu HTML, chưa thay thế kiểm tra tương tác/hiển thị trong trình duyệt.

| Mã | URL, role và thao tác | Kỳ vọng | Thực tế | Trạng thái |
| --- | --- | --- | --- | --- |
| A1-01 | `?page=home`, khách | Có khóa published, không có draft | HTML/CSS hiện; CSDL draft ẩn | Đạt (HTTP) |
| A1-02 | `?page=courses`, khách | Chỉ hai khóa published | Đếm 2; khóa draft không có | Đạt (HTTP) |
| A1-03 | `?page=courses&q=JavaScript` | Tìm theo tên | Chỉ có JavaScript cơ bản | Đạt (HTTP) |
| A1-04 | `?page=courses&category=1` | Lọc danh mục web | Chỉ có HTML và CSS cơ bản | Đạt (HTTP) |
| A1-05 | `?page=courses&q=no-match-987` | Có empty state | Hiện “Không tìm thấy khóa học” | Đạt (HTTP) |
| A1-06 | `?page=detail&id=1` và `id=3`, khách | Published có outline; draft bị chặn | 200 có outline; draft 404 | Đạt (HTTP) |
| A2-01 | `?page=login`, khách | Form có email/password/CSRF/action | Các input và hidden action đúng | Đạt (HTML/HTTP) |
| A2-02 | POST login sai mật khẩu | Báo lỗi rõ, không vào Student | 401, “Email hoặc mật khẩu không đúng” | Đạt (HTTP) |
| A2-03 | `?page=register`, khách | Họ tên/email/password/xác nhận | Có các label và action register | Đạt (HTML/HTTP) |
| A2-04 | POST register hợp lệ | Tạo Student, tới dashboard | 200, lời chào tên tài khoản mới | Đạt (HTTP) |
| A3-01 | `?page=profile`, Student mới | Tên/email đúng tài khoản | Đúng tên/email đã đăng ký | Đạt (HTTP) |
| A3-02 | POST enroll khóa 1 | Tới trang học | 200, có nút đánh dấu bài | Đạt (HTTP) |
| A3-03 | `?page=quiz&id=1` khi chưa đủ bài | Không mở quiz | 403 | Đạt (HTTP) |
| A3-04 | `?page=learn&id=1&lesson=...`, hoàn thành 6 bài | Bài mở được, trạng thái lưu | Sáu trang 200, sáu POST hoàn thành và thông báo thành công | Đạt (HTTP) |
| A3-05 | `?page=quiz&id=1` sau học đủ | Mở quiz | 200, có form submit_quiz | Đạt (HTTP) |
| A3-06 | POST quiz bỏ trống, xác nhận | Chưa đạt; lưu lịch sử | Result 0%, chưa đạt; history có lần nộp | Đạt (HTTP) |
| A3-07 | POST quiz đúng 5/5 | Đạt; dashboard hoàn thành | Result 100%, đạt; Student hiện hoàn thành | Đạt (HTTP) |
| A2-05 | POST logout, sau đó mở Student | Mất phiên | Về Home; Student trả 401 | Đạt (HTTP) |

Kiểm tra cú pháp: `php -l app/views.php` và `php -l app/layout.php` đều không báo lỗi.

## Lỗi UI được phát hiện và sửa trong branch này

- `public/assets/app.css` có style `.answer-option.chosen`, nhưng các radio quiz native không gán class `chosen` và không có JavaScript để làm việc đó. Vì vậy lựa chọn đã tick không đổi màu viền/nền như style dự định.
- Bổ sung `:has(input:checked)` cho trạng thái được chọn và `:focus-within` cho focus bàn phím. Không đổi form POST hoặc cách chấm trên server.
- Đã kiểm tra `git diff --check`. **Cần kiểm tra lại màu lựa chọn và focus trực quan trên Chrome Windows** trước khi ghi nhận sửa lỗi đã nghiệm thu.

## Kiểm tra trực quan cần An hoàn tất trên máy Windows

Trình duyệt kiểm thử trong môi trường hỗ trợ không mở được server local của workspace (`ERR_CONNECTION_REFUSED`); chưa có ảnh before/after đáng tin cậy. Không ghi các viewport dưới đây là “Đạt” khi chưa mở thực tế.

| Viewport | Trang cần kiểm | Kỳ vọng | Trạng thái |
| --- | --- | --- | --- |
| 390 × 844 | Home, Catalog, Detail, Login/Register, Student, Learn, Quiz, Result, Profile | Không tràn ngang; link/menu/nút và form dùng được | Chưa kiểm tra trực quan |
| 768 × 1024 | Các trang trên | Grid và form không che nội dung; bảng rộng cuộn trong vùng bảng | Chưa kiểm tra trực quan |
| 1366 × 768 | Các trang trên | Bố cục desktop rõ, điều hướng và nút đủ chỗ | Chưa kiểm tra trực quan |

Cách làm: chạy bản `main` có `config.php` riêng theo README, mở DevTools → Toggle device toolbar, nhập ba kích thước. Kiểm tra `document.documentElement.scrollWidth <= window.innerWidth` trong Console trên từng trang; xem bằng mắt menu, form, nội dung, nút và bảng. Chụp ảnh có URL/thanh địa chỉ cho bằng chứng nộp bài. Dùng Chrome để chọn một đáp án quiz và Tab tới radio nhằm xác nhận CSS vừa sửa; chụp trước/sau nếu cần.

## Phần phối hợp còn mở

- Tú xác nhận form/action contract và chạy lại các luồng với PDO MySQL trên clone `main`; kiểm tra email trùng, dữ liệu sai và đăng nhập Student thật.
- An review giao diện Instructor/Admin của Quân trên browser ở ba viewport. Nếu phát hiện lỗi trong file Quân, ghi URL, bước tái hiện, expected/actual, gửi Quân sửa qua PR.
- Sau kiểm tra thực tế, An cập nhật bảng viewport và ảnh vào QA trước khi gọi A4/A5 Done. Không chạy `tests/run.php` trên DB có dữ liệu cần giữ.
