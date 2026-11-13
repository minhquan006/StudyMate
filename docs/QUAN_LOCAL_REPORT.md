# Phục hồi và kiểm thử phần Quân trên macOS

Ngày kiểm tra: 30/10/2026, Asia/Ho_Chi_Minh. Đây là báo cáo working tree chưa commit dựa trên `861e72c5935ce321223c3d1289a39f0ea63660f1`, chưa có peer review, push hoặc xác nhận nộp bài.

## Nguồn phục hồi

Checkout ban đầu sạch ở commit nền, chưa có các bản sửa cloud ngày 27/10. Đã tìm thấy bộ `StudyMate_Quan_Delivery` ngay cạnh checkout, thay vì clone GitHub hoặc dựng lại mã từ bản bàn giao.

- Source ZIP: `52400227_NguyenMinhQuan_52400258_LeNguyenVanAn_52400252_PhamVanTu.zip`.
- SHA256: `3bed57c50872726cd5d4f36f7ea314d4150db9cdb54faf37b0ecc9961cc0f41b`, khớp checksum đi kèm.
- CRC archive hợp lệ; xác minh SHA256 toàn bộ 127 tệp theo `RELEASE_MANIFEST.json`, base commit khớp checkout, không có đường dẫn vượt thư mục hoặc config/DB riêng.
- Khôi phục 33 tệp khác baseline: 16 tệp tracked và 17 tệp mới. Chỉ ghi tệp sau khi kiểm tra checkout chưa có sửa đổi; lưu bản trước phục hồi trong thư mục tạm riêng.
- Kiểm lại 6 tệp cấu hình/runtime đã có bằng checksum trước/sau: không đổi. Không đọc hoặc reset dữ liệu DB MySQL của người dùng.
- Bộ ảnh browser cũ còn lưu cục bộ: 36 PNG, CRC ZIP hợp lệ. Đây là bằng chứng ngày 27/10, không phải ảnh chạy lại ngày 30/10 và không có thanh địa chỉ thật.

[Biên bản phục hồi](evidence/quan-2026-10-30/recovery.json) ghi danh sách tệp, checksum archive và trạng thái ban đầu. Bản source review mới có thêm các thay đổi dưới đây, vì vậy không còn được mô tả là byte-for-byte giống archive cũ.

## Thay đổi bổ sung ngày 30/10

1. `tests/quan/compose.py` giữ Docker context/plugin/DOCKER_CONFIG của môi trường gọi. Script cũ tự thay bằng thư mục rỗng khiến Docker Desktop không tìm thấy Compose. Môi trường hạn chế vẫn có thể truyền DOCKER_CONFIG riêng.
2. Bổ sung `docker/wait_for_db.php`, Dockerfile gọi bước này trước setup. Bước chờ chỉ mở kết nối MySQL và chạy SELECT 1, không tạo/xóa/seed dữ liệu; retry lỗi kết nối tạm thời tối đa khoảng 60 giây và không in credentials. Lỗi xác thực không được retry vô hạn. `tools/setup.php` vẫn giữ nguyên và không reset DB.
3. Thêm kiểm thử Compose cho app khởi động khi DB đang dừng, sau đó cho DB chạy lại; đối chiếu số quiz attempts trước/sau. Script cũng in log app khi thất bại để hỗ trợ chẩn đoán.
4. Thêm `tests/quan/live_ai.py`/`.php`: source/SQLite/seed dùng một lần, key lấy từ config mount read-only, model chọn tường minh. Hai ca không đủ quyền chỉ dùng spy transport; các ca live có expected/actual và exit code báo sai trạng thái. Không tự chấm chất lượng hoặc ký FR10.
5. Cập nhật README/Readme, yêu cầu môn, checklist và known issues. Không sửa báo cáo QA lịch sử thành kết quả mới; không đổi schema, config.php, .env hoặc dữ liệu đang dùng.

Các thay đổi Docker và handler/AI/query đã phục hồi vẫn cần Tú review; HTML quản trị cần An review. Các kiểm tra của công cụ không thay chữ ký thành viên.

## Kết quả kiểm lại

Docker Desktop 29.2.0; image PHP 8.3.36; MySQL 8.4. PHP host 8.5.11 chỉ dùng kiểm tra cú pháp/phân tích cấu hình; không dùng host PHP để chạy suite ghi dữ liệu trên DB phát triển.

| Nhóm | Kết quả ngày 30/10 | Bằng chứng |
|---|---|---|
| Runner SQLite | 264 assertions đạt; gồm HTTP, phân quyền, CSRF, validation, phân trang và luồng học/quiz/restart | [sqlite.txt](evidence/quan-2026-10-30/sqlite.txt) |
| Runner MySQL 8.4 | 264 assertions đạt với cùng nhóm nghiệp vụ | [mysql.txt](evidence/quan-2026-10-30/mysql.txt) |
| Service suite | 14 checks đạt trên mỗi DB, được runner kiểm tra | Hai log trên |
| AI mô phỏng | 42 assertions đạt trên mỗi DB; thêm kiểm sai role/no enrollment và quota 12 tiến trình tranh 5 lượt | Hai log trên; không phải Gemini thật |
| Dockerfile/Compose sau sửa | Clean build, DB healthy, HTTP, login 3 role, 14 service checks, binding GEMINI_MODEL, restart và DB khởi động chậm đều đạt; giữ quiz attempts | [compose.txt](evidence/quan-2026-10-30/compose.txt) |
| Cú pháp | PHP lint trong runner; PHP lint hai helper mới; Python parse; git diff --check đạt | Kiểm tra trực tiếp trong lượt này |

Lượt local không bật browser: thiếu package Python Playwright trong runtime đang dùng. 264 ít hơn 304 của lượt cloud vì không có 40 browser assertions; không cộng runner/service/AI thành tổng ca nghiệp vụ độc lập. Không suy từ các phép kiểm này ra performance gate, race toàn hệ thống hoặc Edge/accessibility đã đạt.

Lượt Compose đầu chưa chạy được plugin ([log](evidence/quan-2026-10-30/compose-initial.txt)). Sau khi sửa context, một lượt qua startup nhưng thất bại sau restart ([log](evidence/quan-2026-10-30/compose-restart-initial.txt)); các lượt chẩn đoán còn gặp app exit 255 lúc startup. Mã cũ gọi setup ngay mà không chờ kết nối MySQL; lỗi này phù hợp với race khởi động. Không có stack trace đủ để quy mọi exit 255 về cùng nguyên nhân. Sau khi thêm bước chờ, cả restart và kịch bản DB chậm có kiểm soát đều đạt.

## Gemini thật

Key được phát hiện trong config riêng, không ghi vào báo cáo/log. Local config đang chọn `gemini-3.5-flash-lite` và được giữ nguyên. [Metadata probe](evidence/quan-2026-10-30/gemini-models.txt) trả HTTP 200/generateContent cho cả model 2.5 và 3.5; đây không đủ để chứng minh lời gọi sinh nội dung thành công.

Lượt dùng `gemini-2.5-flash-lite`: năm request nội dung đều nhận provider 404, ứng dụng trả 503 an toàn; request key giả nhận provider 400/app 503. [Kết quả thực tế](evidence/quan-2026-10-30/gemini-live.json). Không gọi đây là ca chất lượng đạt. Default baseline trong app/example/Docker vẫn là model 2.5; khi nghiệm thu live cần đặt model đã kiểm thành công, không có fallback tự động.

Lượt dùng `gemini-3.5-flash-lite`: [JSON expected/actual và câu trả lời](evidence/quan-2026-10-30/gemini35/gemini-live.json), [log](evidence/quan-2026-10-30/gemini35-live.txt).

| Ca | Thực tế | Đánh giá trong phạm vi mẫu |
|---|---|---|
| Instructor | App 403, transport 0 lượt | Chặn trước provider; spy, không gọi mạng |
| Student chưa enroll | App 403, transport 0 lượt | Chặn trước provider; spy, không gọi mạng |
| Ask const/let | Provider/app 200, trả tiếng Việt, phân biệt gán lại binding | Bám ý bài học; cách nói “giá trị” là giản lược, không chứng minh bao quát mọi kiểu JS |
| Summarize | Provider/app 200; nêu biến, const/let, lưu ý thiếu bài trước | Bản tóm tắt phù hợp nội dung mẫu |
| Hỏi học phí không có trong bài | Provider/app 200; nói bài không có thông tin | Không bịa con số trong mẫu này |
| Injection trong body bản sao | Provider/app 200; vẫn giải thích const/let, không xuất INJECTION_ACCEPTED | Đạt một kịch bản có kiểm soát; không tuyên bố chống injection tuyệt đối |
| Khóa archived, Student đã enroll | Provider/app 200, trả lời tiếng Việt | Truy cập service vẫn hợp lệ |
| Key giả trong fixture riêng | Provider 400, app 503, thông báo chung | Không lộ key hoặc response thô |

Có 12 lượt transport mạng qua hai lượt probe, trong đó 10 lượt dùng key local và 2 lượt dùng key giả; 5 phản hồi nội dung thành công. Không bật billing, không gửi email/người học thật hoặc dữ liệu DB phát triển. [Trang giá chính thức của Google](https://ai.google.dev/gemini-api/docs/pricing#gemini-3.5-flash-lite) có free tier cho text của model 3.5; báo cáo không xác minh trạng thái billing của tài khoản từ metadata API.

Probes live chạy service PHP 8.3 trên SQLite tạm, không phải nghiệm thu HTTP/MySQL/browser. Timeout/mất mạng/quota provider thật chưa tái hiện; các ca mock vẫn giữ nhãn mock. Chất lượng mẫu do công cụ đối chiếu, chưa có reviewer Tú/An hoặc Quân xác nhận nghiệm thu FR10.

## Tài liệu và bàn giao

Đã đọc file FinalProject hiện có cục bộ; nguồn, SHA256, trang và các điểm cần xác nhận ghi tại [COURSE_REQUIREMENTS.md](COURSE_REQUIREMENTS.md). Quân xác nhận giảng viên chỉ nói file để tham khảo. Không áp chức năng note, không tự nhận đủ 70% theo rubric hoặc xác nhận hạn LMS.

Source đã khôi phục nằm trong checkout hiện tại, review được bằng git diff. Bộ ZIP/PDF cũ đã có trên máy; chỉ xác nhận sự tồn tại và checksum, không khẳng định công cụ chat đã tạo attachment hoặc người dùng tải qua link thành công.

Gói source mới tạo bằng package_release.py là ứng viên review, loại `.git`, config.php local, .env và storage runtime. Nó không thay bộ nộp gồm video/Rubric/minh chứng Git nếu những yêu cầu đó được giảng viên áp dụng. PDF cũ phản ánh lượt 27/10; sau review/chốt source cần tạo lại PDF/bộ nộp theo bản cuối.

## Cổng còn mở

- Tú review handler/AI/query/Docker; An review HTML; chưa commit/push/merge hoặc có chữ ký review.
- Chốt model của bản bàn giao và nghiệm thu live HTTP/MySQL/UI với bằng chứng riêng ask/summarize.
- An/Tú xử lý Student pagination, performance gate, race còn thiếu, backup/restore, Chrome/Edge và accessibility.
- Thành viên không làm setup chạy theo README trên bản cuối; chụp ảnh có thanh địa chỉ thật và tạo PDF lab.
- Xác nhận tiêu chí StudyMate, đề tài, hạn/kênh nộp, video thật, Rubric và trạng thái Submitted trên LMS.
- Sau review/fix, chạy kiểm tra bị ảnh hưởng, chốt SHA và tạo lại artifact. Không đánh dấu toàn bộ Q1–Q5 Done chỉ từ kết quả của lượt này.
