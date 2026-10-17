# QA giao diện Public và Student - Lê Nguyễn Vạn An

- Ngày: 15/10/2026
- Baseline: SRS/Project Specification 2.2; clone từ GitHub `main` tại `f142c53`.
- Môi trường: PHP 8.3.6, PDO SQLite, DB seed **dùng thử riêng**; HTTP tại `http://127.0.0.1:8123`.
- Người thực hiện kiểm tra HTTP và chuẩn bị báo cáo: Codex hỗ trợ An. An cần tự kiểm tra lại bằng trình duyệt Windows/MySQL và xác nhận trước khi báo Done.
- Phạm vi: `app/views.php`, `app/layout.php`, `public/assets/app.css`. Không sửa service/action hay panel AI.

## Kiểm tra chức năng trên bản clone `main`

Bảng dưới là HTTP test với session và CSRF thật qua `public/index.php`. Tài khoản Student mới được tạo trong DB SQLite dùng thử. “Đạt” ở đây xác nhận response và dữ liệu HTML, chưa thay thế kiểm tra tương tác/hiển thị trong trình duyệt.

| Mã | URL, role và thao tác | Kỳ vọng | Thực tế | Trạng thái |
| - | - | - | - | - |
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
- Đã kiểm tra `git diff --check`. Ảnh Chrome xác nhận đáp án được chọn đổi màu nền/viền và radio nhận focus bằng Tab có viền tím.

## Kiểm tra trực quan trên Chrome Windows

- Môi trường: PHP 8.5.11, PDO SQLite với dữ liệu demo tại `http://localhost:8000` (16/10/2026).
- Bằng chứng: ảnh chụp Chrome do An cung cấp trong quá trình kiểm tra; ảnh chưa được đưa vào repository. Kết quả dưới đây chỉ phản ánh những vùng đã quan sát.
- Bảng viewport ban đầu của PR yêu cầu 390 × 844, 768 × 1024 và 1366 × 768. Các ảnh 390 × 768 và 768 × 768 không thay thế hai kích thước đầu.

| Trang / khu vực | Viewport | Kết quả thực tế | Trạng thái |
| --- | --- | --- | --- |
| Catalog: đầu trang, bộ lọc, thẻ khóa học và chân trang | 390 × 768 | Nội dung hiển thị, không quan sát thấy tràn ngang hay chồng chữ | Đạt trong phạm vi đã xem |
| Catalog: đầu trang, bộ lọc và phần đầu lưới hai thẻ | 768 × 768 | Hai cột hiển thị; phần cuối trang chưa có ảnh | Đạt một phần |
| Quiz: đáp án được chọn và focus bàn phím | Desktop (ảnh Chrome) | Đáp án được chọn đổi nền/viền; radio nhận focus có viền tím | Đạt trong phạm vi đã xem |
| Home, Detail, Login/Register, Student, Learn, Quiz, Result, Profile | Các viewport An đã mở | An xác nhận đã duyệt giao diện; chưa có danh sách trang × kích thước và ảnh đầy đủ để đối chiếu | Chưa hoàn tất bằng chứng theo từng viewport |
| Toàn bộ trang trong phạm vi PR | 390 × 844 | Chưa có kết quả/ảnh tại đúng kích thước này | Chưa hoàn tất |
| Toàn bộ trang trong phạm vi PR | 768 × 1024 | Chưa có kết quả/ảnh tại đúng kích thước này | Chưa hoàn tất |
| Toàn bộ trang trong phạm vi PR | 1366 × 768 | Ảnh quiz desktop xác nhận trạng thái chọn/focus; chưa có kết quả/ảnh đủ các trang | Chưa hoàn tất |

### Việc cần bổ sung trước khi merge

- An kiểm tra và ghi kết quả theo từng trang tại 390 × 844, 768 × 1024 và 1366 × 768; chụp ảnh minh chứng có URL và kích thước. Trang hoặc kích thước chưa kiểm tra tiếp tục để “Chưa hoàn tất”.
- Tú xác nhận luồng Public/Auth/Student trên PDO MySQL, ghi commit, môi trường, thao tác và kết quả thực tế. Chưa có kết quả MySQL trong lượt QA này.
- An review giao diện Instructor/Admin của Quân trên browser. Nếu có lỗi ở file Quân phụ trách, gửi URL, bước tái hiện và kết quả mong đợi/thực tế để Quân sửa qua PR.
- Không chạy `tests/run.php` trên DB có dữ liệu cần giữ.
