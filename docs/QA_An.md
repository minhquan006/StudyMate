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


## Kiểm tra trực quan trên Chrome Windows (16/10/2026)

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

### Các việc chưa hoàn tất tại thời điểm 16/10

- An kiểm tra và ghi kết quả theo từng trang tại 390 × 844, 768 × 1024 và 1366 × 768; chụp ảnh minh chứng có URL và kích thước. Trang hoặc kích thước chưa kiểm tra tiếp tục để “Chưa hoàn tất”.
- Tú xác nhận luồng Public/Auth/Student trên PDO MySQL, ghi commit, môi trường, thao tác và kết quả thực tế. Chưa có kết quả MySQL trong lượt QA này.
- An review giao diện Instructor/Admin của Quân trên browser. Nếu có lỗi ở file Quân phụ trách, gửi URL, bước tái hiện và kết quả mong đợi/thực tế để Quân sửa qua PR.
- Không chạy `tests/run.php` trên DB có dữ liệu cần giữ.

## Kiểm tra giao diện bổ sung ngày 17/10/2026

Lượt này bổ sung kết quả theo viewport và ảnh; các dòng “chưa hoàn tất” ở mục ngày 16/10 phản ánh trạng thái lịch sử, không thay thế bảng ngày 17/10.

### Phạm vi

Kiểm tra giao diện Public, Auth và Student của StudyMate theo phần công việc của An. Kết quả dưới đây dựa trên thao tác thực tế trong Google Chrome và các vùng màn hình đã quan sát.

### Môi trường kiểm thử

- Ngày kiểm tra: 17/10/2026.
- Hệ điều hành: Windows (Chrome/DevTools theo ảnh).
- PHP: môi trường ngày 16/10 đã ghi PHP 8.5.11; ảnh ngày 17/10 không ghi lại đầu ra `php -v`, nên chưa xác nhận lại phiên bản cho lượt này.
- Commit mã ứng dụng đang kiểm tra: chưa ghi lại `git rev-parse HEAD` tại thời điểm mở Chrome. Commit `04355f8` chứa CSS được review; các commit tiếp theo đến `ead2b18` chỉ sửa báo cáo, nhưng chưa dùng chúng làm mã định danh bản chạy.
- Trình duyệt: Google Chrome, chế độ Responsive trong DevTools.
- Kích thước: 390 × 844, 768 × 1024 và 1366 × 768.
- Ứng dụng chạy tại `http://localhost:8000`.
- Tài khoản sử dụng: Student demo.
- Cơ sở dữ liệu local trong lượt kiểm tra này: PDO SQLite.

### Kết quả kiểm tra giao diện

| Trang / khu vực | 390 × 844 | 768 × 1024 | 1366 × 768 |
| --- | --- | --- | --- |
| Home | Đã xem phần đầu và cuối trang | Đã xem phần đầu và cuối trang | Đã xem phần đầu và cuối trang |
| Danh sách khóa học | Đã xem bộ lọc, thẻ khóa học và chân trang | Đã xem bộ lọc, thẻ khóa học và chân trang | Đã xem bộ lọc, thẻ khóa học và chân trang |
| Chi tiết khóa học | Đã xem phần đầu và cuối trang | Đã xem phần đầu và cuối trang | Đã xem phần đầu và cuối trang |
| Đăng nhập | Đã xem biểu mẫu | Đã xem biểu mẫu và chân trang | Đã xem biểu mẫu và chân trang |
| Đăng ký | Đã xem biểu mẫu; chưa có ảnh xác nhận riêng phần cuối trang | Đã xem biểu mẫu và chân trang | Đã xem biểu mẫu và chân trang |
| Student | Đã xem thống kê, khóa học và chân trang | Đã xem thống kê, khóa học và chân trang | Đã xem thống kê, khóa học và chân trang |
| Bài học | Đã xem phần đầu và cuối trang | Đã xem phần đầu và cuối trang | Đã xem phần đầu và cuối trang |
| Quiz | Đã chọn đáp án, xem phần đầu và nút nộp bài | Đã chọn đáp án, xem phần đầu và nút nộp bài | Đã chọn đáp án, xem phần đầu và nút nộp bài |
| Kết quả | Đã xem điểm và đáp án | Đã xem điểm, đáp án đến câu cuối và chân trang | Đã xem điểm, đáp án đến câu cuối và chân trang |
| Profile | Đã xem thông tin tài khoản | Đã xem thông tin tài khoản và chân trang | Đã xem thông tin tài khoản và chân trang |

**Nhận xét:** Trong các vùng đã xem, chưa quan sát thấy nội dung bị cắt, tràn ngang hoặc chồng lấn. Các trang dài đã được cuộn để xem phần đầu và phần cuối; một số vùng giữa trang không có ảnh riêng nên kết luận chỉ áp dụng cho phạm vi đã quan sát.

### Ảnh minh chứng trong repository

Ảnh gốc từ Chrome/DevTools, giữ nguyên kích thước và URL như được chụp. Mỗi ảnh chỉ chứng minh vùng hiển thị trong khung hình; bảng kết quả bên trên ghi rõ giới hạn khi xem trang dài.

| Trang | 390 × 844 | 768 × 1024 | 1366 × 768 | Nội dung trong ảnh |
| --- | --- | --- | --- | --- |
| Home | [Ảnh](images/qa-an/2026-10-17/390-home.png) | [Ảnh](images/qa-an/2026-10-17/768-home-bottom.png) | [Ảnh](images/qa-an/2026-10-17/1366-home.png) | Phần đầu trang; ảnh tablet ghi phần cuối |
| Danh sách khóa học | [Ảnh](images/qa-an/2026-10-17/390-courses.png) | [Ảnh](images/qa-an/2026-10-17/768-courses.png) | [Ảnh](images/qa-an/2026-10-17/1366-courses.png) | Bộ lọc và thẻ khóa học |
| Chi tiết khóa học | [Ảnh](images/qa-an/2026-10-17/390-detail.png) | [Ảnh](images/qa-an/2026-10-17/768-detail.png) | [Ảnh](images/qa-an/2026-10-17/1366-detail.png) | Thông tin và nội dung khóa |
| Đăng nhập | [Ảnh](images/qa-an/2026-10-17/390-login.png) | [Ảnh](images/qa-an/2026-10-17/768-login.png) | [Ảnh](images/qa-an/2026-10-17/1366-login.png) | Biểu mẫu và tài khoản demo |
| Đăng ký | [Ảnh](images/qa-an/2026-10-17/390-register.png) | [Ảnh](images/qa-an/2026-10-17/768-register.png) | [Ảnh](images/qa-an/2026-10-17/1366-register.png) | Biểu mẫu; phần cuối mobile chưa có ảnh riêng |
| Student | [Ảnh](images/qa-an/2026-10-17/390-student.png) | [Ảnh](images/qa-an/2026-10-17/768-student.png) | [Ảnh](images/qa-an/2026-10-17/1366-student.png) | Thống kê và khóa học |
| Bài học | [Ảnh](images/qa-an/2026-10-17/390-learn.png) | [Ảnh](images/qa-an/2026-10-17/768-learn.png) | [Ảnh](images/qa-an/2026-10-17/1366-learn.png) | Nội dung bài và tiến độ |
| Quiz | [Ảnh](images/qa-an/2026-10-17/390-quiz.png) | [Ảnh](images/qa-an/2026-10-17/768-quiz.png) | [Ảnh](images/qa-an/2026-10-17/1366-quiz.png) | Đáp án được chọn; ảnh không chứng minh thao tác Tab |
| Kết quả | [Ảnh](images/qa-an/2026-10-17/390-result.png) | [Ảnh](images/qa-an/2026-10-17/768-result.png) | [Ảnh](images/qa-an/2026-10-17/1366-result.png) | Điểm và đáp án |
| Profile | [Ảnh](images/qa-an/2026-10-17/390-profile.png) | [Ảnh](images/qa-an/2026-10-17/768-profile.png) | [Ảnh](images/qa-an/2026-10-17/1366-profile.png) | Thông tin tài khoản |

Ảnh [Home ở 768 × 1024, phần đầu](images/qa-an/2026-10-17/768-home-top.png) là ảnh cắt khung nội dung, không hiện dòng kích thước DevTools; ảnh phần cuối có dòng kích thước 768 × 1024.

Ảnh bổ sung ở các vị trí khác của trang: [Home desktop](images/qa-an/2026-10-17/1366-home-bottom.png), [Catalog desktop](images/qa-an/2026-10-17/1366-courses-bottom.png), [Home mobile](images/qa-an/2026-10-17/390-home-bottom.png), [Catalog mobile](images/qa-an/2026-10-17/390-courses-bottom.png), [Detail mobile](images/qa-an/2026-10-17/390-detail-bottom.png), [Student mobile](images/qa-an/2026-10-17/390-student-bottom.png), [Learn mobile](images/qa-an/2026-10-17/390-learn-bottom.png), [Result mobile](images/qa-an/2026-10-17/390-result-bottom.png), [Detail tablet](images/qa-an/2026-10-17/768-detail-bottom.png), [Student tablet](images/qa-an/2026-10-17/768-student-bottom.png), [Learn tablet](images/qa-an/2026-10-17/768-learn-bottom.png), [Result tablet](images/qa-an/2026-10-17/768-result-bottom.png), [Detail desktop](images/qa-an/2026-10-17/1366-detail-bottom.png), [Student desktop](images/qa-an/2026-10-17/1366-student-bottom.png), [Quiz desktop](images/qa-an/2026-10-17/1366-quiz-bottom.png), [Result desktop](images/qa-an/2026-10-17/1366-result-bottom.png).

Trạng thái focus bằng Tab được ghi trong lượt kiểm tra 16/10; ảnh ở lượt 17/10 cho thấy viền đáp án được chọn nhưng không tự chứng minh phím Tab đã được bấm.

### Luồng thao tác đã thực hiện

1. Mở các trang Public: Home, danh sách khóa học và chi tiết khóa học.
2. Kiểm tra biểu mẫu Đăng nhập và Đăng ký.
3. Đăng nhập bằng tài khoản Student demo.
4. Mở Student, bài học, Quiz, kết quả và Profile.
5. Kiểm tra giao diện ở ba kích thước màn hình nêu trên.

### Giới hạn của lượt kiểm thử

- Lượt kiểm tra này dùng PDO SQLite trên máy local; chưa xác nhận hoạt động với MySQL.
- `docs/QA_Tu.md` ghi nhận Tú đã chạy bản `StudyMate_PHP_Integrated.zip` riêng trên Docker `php:8.3-apache` và MySQL 8.4 ngày 12/10/2026, trong đó có login và tiến độ/quiz. Đó là kết quả do Tú thực hiện trên bản ZIP ngoài repo, chưa chứng minh đầy đủ các luồng của nhánh này trên MySQL. Cần Tú xác nhận commit được chạy và các ca còn thiếu: đăng ký/đăng xuất, tìm/lọc, tham gia khóa, điều kiện mở quiz, kết quả/lịch sử. Xem [báo cáo của Tú](QA_Tu.md).
- Chưa kiểm tra phần Instructor, Admin và AI trong phạm vi QA của An.
- Những khu vực chưa quan sát đầy đủ được ghi rõ trong bảng, không tính là đã xác nhận đạt.

## Bổ sung kết quả kiểm thử MySQL của Tú ngày 17/10/2026

Tú đã kiểm tra các luồng Public, Auth và Student trên nhánh `feature/an-week3-qa`, commit `0259699`. Môi trường kiểm thử là Windows Docker Desktop, PHP 8.3.35 có PDO MySQL, MySQL 8.4 ở trạng thái healthy và ứng dụng chạy tại `http://localhost:8083` trong project `studymate-an-qa`. Đây là kết quả Tú ghi trong phần trao đổi của [PR #9](https://github.com/minhquan006/StudyMate/pull/9), không phải lượt kiểm tra SQLite và giao diện responsive của An ở các mục phía trên.

| Nội dung | Kết quả Tú ghi nhận |
| --- | --- |
| Public | Hiển thị 2 khóa học đã xuất bản; tìm kiếm bằng JavaScript cho 1 kết quả phù hợp và 0 kết quả với từ khóa không tồn tại; bộ lọc danh mục hoạt động. |
| Auth | Đăng ký tài khoản Student, đăng nhập, đăng xuất và kiểm tra trường hợp xác thực bị từ chối. |
| Student và bài học | Ghi danh khóa học; tiến độ bài học thay đổi từ 16,67% đến 100%. |
| Quiz và kết quả | Quiz bị chặn khi chưa hoàn thành bài học; sau khi đủ điều kiện, bài làm đạt 5/5 câu (100%), có kết quả `id=1`. |
| Dashboard và lịch sử | Dashboard hiển thị trạng thái hoàn thành; lịch sử có liên kết đến kết quả. |

**Giới hạn:** Trong lượt kiểm tra này, Tú chưa xác nhận responsive, trạng thái focus, dữ liệu còn được lưu sau khi khởi động lại Docker/MySQL hoặc các yêu cầu HTTP đồng thời. Phần Instructor, Admin và AI nằm ngoài phạm vi kết quả trên. Các ghi chú “chưa xác nhận MySQL” ở mục kiểm tra của An và mục ngày 16/10 phản ánh trạng thái **trước khi Tú kiểm tra**, không phải trạng thái hiện tại của PR.
