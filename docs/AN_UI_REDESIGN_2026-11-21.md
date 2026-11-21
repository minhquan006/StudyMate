> Bản bàn giao hiện tại theo mẫu Udemy: [AN_UI_UDEMY_2026-11-21.md](AN_UI_UDEMY_2026-11-21.md). Nội dung dưới đây là lịch sử lượt trước.

# Tái cấu trúc giao diện StudyMate — An, 21/11/2026

PR [#20](https://github.com/minhquan006/StudyMate/pull/20), nhánh `codex/an-ui-demo-20261121`, nền main `9cb5dea77d23684dc258198ffa2c4e3c9dee47dc`. Bản giao diện và bộ kiểm ở `7c76783f90ea8a0dd064c75b994e1b4f8a0901da`; runtime giống `3c1b377`, commit kế tiếp chỉ sửa selector của runner. Codex hỗ trợ An triển khai và kiểm tự động trên Windows.

## Bố cục bàn giao

- **Điều hướng chung:** logo chữ StudyMate, tìm kiếm toàn trang bằng GET, hàng danh mục thật từ DB, liên kết khu vực theo vai trò. Tìm kiếm và danh mục hoạt động khi tắt JavaScript.
- **Trang chủ:** giới thiệu ngắn, ví dụ HTML minh họa bằng mã giao diện, các lĩnh vực kèm số khóa công khai, khóa mới nhất và hướng dẫn ba bước học. Tối đa sáu khóa mới, đủ chỗ hiển thị nội dung mẫu của Tú.
- **Thư viện:** bộ lọc ở cột trái, kết quả ở cột phải, mỗi khóa là một hàng gồm thumbnail/tên/mô tả/giảng viên/hành động. Trên điện thoại, bộ lọc chuyển lên trên; kết quả giữ thumbnail cạnh thông tin. Có tổng kết quả, giá trị lọc hiện tại, xóa lọc và hướng phục hồi khi rỗng.
- **Chi tiết khóa:** phần giới thiệu toàn chiều rộng, tên/mô tả/giảng viên/số chương và bài; hộp tham gia bên cạnh; điều hướng Tổng quan/Nội dung/Hoàn thành, chương có thể đóng mở bằng bàn phím. Student đã tham gia thấy Tiếp tục học.
- **Việc học của tôi:** thanh điều hướng riêng, số khóa đã tham gia/hoàn thành, tìm khóa đã tham gia và thẻ tiến độ/điểm thật từ DB.
- **Màn hình học:** vùng đọc rộng và mục lục bên phải trên laptop; Hỏi/Tóm tắt nằm dưới bài để có đủ chỗ cho phản hồi dài. Trên điện thoại, bài học và trợ lý đứng trước mục lục, có liên kết đi thẳng tới từng phần. Nút Bài tiếp theo gửi action `last_lesson` hiện có với CSRF và đúng ID theo thứ tự chương/bài; đi tiếp không tự đánh dấu hoàn thành.
- **Giao diện chung:** nền trắng, màu chủ đạo xanh dương, viền nhẹ, thumbnail HTML/CSS, JavaScript và bảng dữ liệu dựng bằng HTML/CSS local. Font Inter local có OFL; nút, field, focus, quiz, kết quả và thông báo dùng hệ thống kiểu chung.

## Tham khảo và cách áp dụng

Đây là lựa chọn bố cục của StudyMate sau khi đối chiếu các trang chính thức; các mẫu được áp dụng theo dữ liệu/chức năng hiện có của dự án.

| Nguồn | Cách áp dụng ở StudyMate |
| --- | --- |
| [Coursera](https://www.coursera.org/) và [Udemy](https://www.udemy.com/) | Tìm kiếm rõ ở header, khám phá theo chủ đề, thông tin khóa và CTA có thứ bậc |
| [edX](https://www.edx.org/) | Nhóm lĩnh vực và danh sách khóa rõ ràng để tìm điểm bắt đầu |
| [Codecademy catalog](https://www.codecademy.com/catalog) | Tách bộ lọc với kết quả, dùng ngôn ngữ hình ảnh gần nội dung lập trình |
| [MIT OpenCourseWare](https://ocw.mit.edu/) | Nhấn mạnh thông tin học phần, giảng viên và mục lục nội dung |

Các hướng dễ đọc, học từng bước và tiến độ rõ trong yêu cầu tham khảo Khan Academy/Duolingo được thể hiện qua số bài đã học, trạng thái bài hiện tại và xem lại quiz. Lượt này chưa quan sát được đầy đủ giao diện hai trang đó qua công cụ. Logo, nội dung và thumbnail dùng nhận diện của StudyMate; thumbnail là minh họa trang trí, không phải ảnh chụp bài tập đang chạy.

## Hợp đồng dữ liệu và form

Giữ schema, seed, ID, handler, chấm điểm, bảo mật và `forms.js` của dự án. Phần giao diện đọc các trường `title`, `description`, `category_name`, `instructor_name`, `cover_key`, chương/bài, tiến độ và điểm có sẵn. Số khóa ở danh mục chỉ tính `published`. Phần Bạn sẽ tìm hiểu lấy tối đa sáu tiêu đề bài đầu.

Tú có thể đưa đối tượng, kiến thức cần có và mục tiêu vào `description` nhiều dòng, trong giới hạn 2.000 ký tự của form hiện có. Mô tả được escape; nội dung bài dùng Markdown đang có. Các form POST giữ `action`, `csrf`, ID, `return_page`/`return_tab`, và quiz giữ `request_id`/`confirm`. Hai bộ runner được giới hạn selector danh sách bài trong `.outline` để nút Bài tiếp theo không bị tính thành một bài trùng.

## Bằng chứng mới

Bằng chứng nằm riêng trong [an-ui-2026-11-21-redesign](evidence/an-ui-2026-11-21-redesign/summary.json). Bản [bàn giao trước](AN_UI_DEMO_2026-11-21.md) và bằng chứng của bản đó là lịch sử.

| Bộ kiểm | Kết quả | Nguồn mã |
| --- | --- | --- |
| UI và luồng học Chrome/Edge | **632 kiểm tra đạt**, 0 JavaScript page error; **15 ảnh** | Runtime `7c76783` tương đương `3c1b377`; harness `7c76783` |
| Form review / hủy rời quiz / form ban đầu | **34 + 16 + 22 = 72 ca đạt** trên Chrome/Edge | `3c1b377` |
| Service / pagination / cấu hình model / AI offline / UTF-8 ba vai trò | **14 + 8 + 8 + 24 + 58 = 112 kiểm tra đạt** | `3c1b377` |
| PHP syntax | **33 tệp tracked đạt** | `3c1b377` |

Nguồn runtime PHP/CSS/JS của hai commit giống nhau. Manifest [summary.json](evidence/an-ui-2026-11-21-redesign/summary.json) ghi SHA-256 chuẩn hóa LF của nguồn đã commit. Các runner ghi fingerprint byte của workspace lúc chạy; Windows có thể dùng CRLF ở tệp không sửa. [checksums.json](evidence/an-ui-2026-11-21-redesign/checksums.json) dùng LF cho artifact chữ và byte gốc cho PNG.

Coverage UI: 18 màn hình Public/Student/Instructor/Admin tại **390/768/1366 px**, HTTP 200, lỗi PHP, tràn ngang, nhãn field, nút tối thiểu 44px, skip link, cuộn bảng/mã bằng bàn phím, contrast chữ trên trắng, tiêu đề dài và phản hồi AI dài. Thêm kiểm tìm kiếm toàn trang, điều hướng danh mục, bố cục filter desktop, anchor chi tiết, đóng chương bằng bàn phím, nút Bài tiếp theo, thứ tự nội dung mobile và tìm kiếm/lọc khi tắt JavaScript.

Luồng thật qua PHP: đăng nhập, tìm/xóa lọc, tham gia học, CTA tiếp tục học, chọn bài bằng bàn phím, hoàn thành sáu bài, nộp quiz, xem kết quả/giải thích, logout. Form runner kiểm validation, pending, gửi đúp, Back, hủy rời quiz và native form khi tắt JavaScript. Mỗi nhóm form dùng DB/quota QA riêng, đặt lại giữa nhóm, không tác động DB demo của nhóm.

Hai nút Hỏi/Tóm tắt gửi đúng action/mode/course/lesson/CSRF và kiểm trạng thái AI chưa cấu hình. Ảnh phản hồi dài dùng fixture có nhãn **ngoại tuyến**, chỉ nằm trong checkout QA tạm.

**Giới hạn:** SQLite QA trên Windows; lượt này chưa chạy MySQL/Docker hoặc Gemini thật, chưa kiểm trên điện thoại vật lý. Quân cần kiểm lại provider thật và môi trường MySQL sau khi tích hợp nội dung của Tú. Các bằng chứng Gemini thật trước đây thuộc lượt nghiệm thu của nhóm, không được tính vào lượt này.

## Ảnh trước và sau

Ảnh trước dùng main `9cb5dea`, SQLite seed cùng fixture service để có quiz/result. Ảnh sau dùng runtime mới trên DB QA tương ứng. Có thể phát sinh lịch sử attempt QA bổ sung khi chạy luồng, không liên quan dữ liệu demo thật.

| Màn hình | Trước laptop | Sau laptop | Trước mobile | Sau mobile |
| --- | --- | --- | --- | --- |
| Trang chủ | [Ảnh](evidence/an-ui-2026-11-21/before/home-1366.png) | [Ảnh](evidence/an-ui-2026-11-21-redesign/browser/home-1366.png) | [Ảnh](evidence/an-ui-2026-11-21/before/home-390.png) | [Ảnh](evidence/an-ui-2026-11-21-redesign/browser/home-390.png) |
| Thư viện | [Ảnh](evidence/an-ui-2026-11-21/before/courses-1366.png) | [Ảnh](evidence/an-ui-2026-11-21-redesign/browser/courses-1366.png) | [Ảnh](evidence/an-ui-2026-11-21/before/courses-390.png) | [Ảnh](evidence/an-ui-2026-11-21-redesign/browser/courses-390.png) |
| Chi tiết | [Ảnh](evidence/an-ui-2026-11-21/before/detail-1366.png) | [Ảnh](evidence/an-ui-2026-11-21-redesign/browser/detail-1366.png) | [Ảnh](evidence/an-ui-2026-11-21/before/detail-390.png) | [Ảnh](evidence/an-ui-2026-11-21-redesign/browser/detail-390.png) |
| Học | [Ảnh](evidence/an-ui-2026-11-21/before/learn-1366.png) | [Ảnh](evidence/an-ui-2026-11-21-redesign/browser/learn-1366.png) | [Ảnh](evidence/an-ui-2026-11-21/before/learn-390.png) | [Ảnh](evidence/an-ui-2026-11-21-redesign/browser/learn-390.png) |
| Quiz | [Ảnh](evidence/an-ui-2026-11-21/before/quiz-1366.png) | [Ảnh](evidence/an-ui-2026-11-21-redesign/browser/quiz-1366.png) | [Ảnh](evidence/an-ui-2026-11-21/before/quiz-390.png) | [Ảnh](evidence/an-ui-2026-11-21-redesign/browser/quiz-390.png) |
| Kết quả | [Ảnh](evidence/an-ui-2026-11-21/before/result-1366.png) | [Ảnh](evidence/an-ui-2026-11-21-redesign/browser/result-1366.png) | [Ảnh](evidence/an-ui-2026-11-21/before/result-390.png) | [Ảnh](evidence/an-ui-2026-11-21-redesign/browser/result-390.png) |

Ảnh bổ sung: [Việc học của tôi laptop](evidence/an-ui-2026-11-21-redesign/browser/student-1366.png), [mobile](evidence/an-ui-2026-11-21-redesign/browser/student-390.png), [AI fixture offline](evidence/an-ui-2026-11-21-redesign/browser/ai-offline-fixture-390.png).

## Chạy lại

Chuẩn bị checkout/config/DB SQLite dùng một lần theo README và chạy fixture `tests/run.php` để có quiz/result Student 1. Dùng Node, Playwright và Chrome/Edge cài ngoài source như hướng dẫn [runner UI](AN_UI_DEMO_2026-11-21.md#chạy-lại-phần-ui). Chỉ `QA_BASE_URL` loopback và `QA_ALLOW_WRITES=1` được chấp nhận. Runner tạo tiến độ/quiz của Student 2 nên không trỏ vào dữ liệu demo thật. Fixture AI là tùy chọn, qua `QA_AI_PREVIEW_PATH`.

Log: [UI](evidence/an-ui-2026-11-21-redesign/browser/results.json), [form review](evidence/an-ui-2026-11-21-redesign/forms/review/results.json), [hủy quiz](evidence/an-ui-2026-11-21-redesign/forms/quiz-exit/results.json), [form ban đầu](evidence/an-ui-2026-11-21-redesign/forms/original/results.json), [PHP](evidence/an-ui-2026-11-21-redesign/php-checks.txt).
