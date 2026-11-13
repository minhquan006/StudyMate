# Tích hợp và kiểm thử phần Quân ngày 01/11/2026

Phần Instructor/Admin/AI của Quân đã được ghép với source của Tú/An và kiểm thử trên dữ liệu riêng. Bản này chuẩn bị cho review chéo; chưa có commit của Quân, merge, chữ ký thành viên hoặc xác nhận nộp bài.

## Source đã đối chiếu

- GitHub #14: `1a27ec1bc939b7b98324dc6f95ec7aaa175f484a`.
- GitHub #15: `81dc740916360c4f6229cd1026837939bd44fd10`, hai parent `f8565fb` và `1a27ec1`.
- Đã nhận và xác minh Git blob đủ **310 file** của `81dc740` trước khi ghép phần Quân, gồm bằng chứng lịch sử của Tú/An. Không tạo lại lịch sử Git; HEAD checkout vẫn `861e72c`, có thay đổi local.
- Đã lưu bản trước tích hợp. Cấu hình riêng và storage được đối chiếu checksum trước/sau; không reset hoặc dùng DB phát triển để chạy test.

Trước khi đóng gói, đã kiểm lại GitHub: #14 có head tài liệu `24f6e1b`, #15 có head tài liệu `a44e23c`. Diff so với mốc mã đã kiểm chỉ gồm 17 file tài liệu/bằng chứng; đã nhận đầy đủ và xác minh Git blob từng file. Runtime vẫn là mốc `81dc740` cộng phần Quân, không ghi thành lượt chạy suite mới. Manifest riêng: `evidence/quan-2026-11-01/remote-document-updates.json`. Báo cáo và bằng chứng mới của Tú/An giữ nguyên nguồn/ngày/SHA; bản delta Quân bỏ qua các file này để không nhận thay phần đóng góp của hai bạn.

## Nội dung phần Quân

1. Quiz từ chối chỉ số đáp án không phải số nguyên, câu hỏi/lựa chọn sai cấu trúc; chapter ID chéo khóa trả 404. Từ chối giữ nguyên dữ liệu.
2. Admin từ chối `is_active` sai thay vì ép thành khóa; cập nhật category không tồn tại trả 404; giữ thu hồi mọi phiên cũ sau khóa/mở.
3. Dùng nguyên giao diện/truy vấn của #15. Helper `managementPage()` chỉ chuẩn hóa tham số cho `pageQuery()` của Tú: mặc định 12, tối đa 50, clamp trang; chuỗi/array sai quay về mặc định. Giữ tổng toàn tập, bộ lọc, truy vấn tổng hợp và vùng bảng của An.
4. Gemini dùng cùng callable transport dạng `[status, body, elapsed]` của Tú; giữ `geminiRequest()` để probe và các test hiện có hoạt động. Bổ sung decoder cho timeout 408/504, JSON/safety/empty, bỏ thought parts và không chuyển key theo redirect. Không nhận endpoint/transport từ POST.
5. Startup chính vẫn là `docker/start.sh` và `tools/wait_for_db.php` của Tú. Đường helper cũ trong docker chỉ gọi lại helper chung, không giữ hai cách chờ DB độc lập.
6. `.env.example` chọn `gemini-3.5-flash-lite`, model đã gọi thành công trong lượt MySQL/HTTP/UI. Giữ fallback PHP cũ và biến `AI_MODEL` để tương thích; không thay file cấu hình riêng hay tự fallback khi request lỗi.

## Kiểm tra đã chạy

Ngày 01/11/2026, macOS, PHP container 8.3.36, MySQL 8.4, Chrome 156.0.8078.79. Docker project/container/network và DB test riêng được dọn sau từng lượt. Mật khẩu test sinh riêng; key local chỉ dùng trong fixture live ngoài build context/public.

| Nhóm kiểm tra | Kết quả | Phạm vi |
|---|---|---|
| Runner SQLite | 270 kiểm tra đạt | HTTP, quyền/CSRF, validation, phân trang, luồng học/quiz/restart |
| Runner MySQL và Chrome | 310 kiểm tra đạt | Cùng nghiệp vụ; thêm 18 ảnh Instructor/Admin ở 390/768/1366 và click phân trang |
| Service suite | 14 đạt trong từng runner và Compose | Không cộng lặp thành ca nghiệp vụ độc lập |
| AI mô phỏng Quân | 42 assertions trong runner đạt, thêm spy sai role/chưa enroll | Không gọi provider |
| Hồi quy Tú/An trên Compose | 8 phân trang, 58 UTF-8, 8 model, 24 AI offline đạt | Giữ các assertion của hai nhánh |
| Dockerfile và Compose chính thức | Build, DB/app health, login ba role và model binding đạt | MySQL QA riêng, Apache thật |
| Restart và DB chậm | App tự chờ/phục hồi; hash đủ 12 bảng giữ nguyên | Không reset hoặc seed lại dữ liệu |
| Gemini thật qua HTTP/MySQL/UI | Hỏi/tóm tắt hiển thị trong Chrome; thiếu thông tin, injection có kiểm soát và archived có phản hồi | Model `gemini-3.5-flash-lite`, nội dung seed công khai |
| Lỗi/quyền live HTTP | Sai role/chưa enroll 403; fixture quota app 429; key giả 503; học/complete/quiz vẫn dùng được | Không cố tiêu quota provider hoặc tạo timeout mạng thật |

Log, ảnh, source fingerprint và lịch sử lượt chạy ở [evidence/quan-2026-11-01](evidence/quan-2026-11-01/). Fingerprint của lượt MySQL, Compose và live khớp runtime của bản bàn giao; đổi tài liệu không được ghi thành lượt kiểm runtime mới.

## Các lượt dừng và cách xử lý

Lượt MySQL có browser đầu tiên dừng vì Python không có Playwright, sau khi các kiểm tra HTTP đã chạy. Runner được chuyển sang Playwright Node có sẵn; chạy lại MySQL/Chrome đạt 310. Đây là sửa công cụ kiểm thử, không phải sửa lỗi ứng dụng để bỏ assertion.

Lượt live đầu đã có năm phản hồi nội dung hợp lệ nhưng dừng ở ca đổi file key trong cùng project; trạng thái trả về không đạt kỳ vọng key giả. Log cũ giữ nguyên `failed`. Chưa đủ bằng chứng để kết luận nguyên nhân chi tiết hoặc cho rằng provider chấp nhận key giả. Lượt mới khởi động bằng key giả ngay từ đầu đã đạt 503, quyền/quota app và học/quiz sau lỗi. Runner cũng bổ sung restart khi đổi cấu hình PHP; chế độ gộp sau thay đổi này chưa được chạy lại toàn bộ để tránh gọi lặp các mẫu nội dung đã đạt.

## Đối chiếu câu trả lời

Hỏi const/let phân biệt khả năng gán lại binding; tóm tắt giữ các ý biến, const, let và tên rõ nghĩa có trong lesson 7. Mẫu thiếu học phí nói nội dung không đủ, không tự bịa con số. Mẫu injection không trả marker điều khiển; một mẫu không chứng minh chống prompt injection tuyệt đối. Student đã enroll tiếp tục hỏi được ở khóa archived. Các câu trả lời và ảnh giữ riêng trong evidence để Tú/Quân đối chiếu; chưa thay review độc lập của thành viên.

Google công bố model `gemini-3.5-flash-lite` và free tier text tại [trang model](https://ai.google.dev/gemini-api/docs/models/gemini-3.5-flash-lite), [trang giá](https://ai.google.dev/gemini-api/docs/pricing#gemini-3.5-flash-lite), đối chiếu 01/11/2026. Lượt này không thay billing và không xác minh trạng thái billing tài khoản qua API.

## Review và bước chốt

- Tú review handlers, `management_support.php`/`services.php`, AI transport/decoder và bộ test. An review Instructor/Admin, focus/keyboard và ảnh.
- Bản source dựa trên #15 để tránh mất sửa của Tú/An. Sau khi #14/#15 được review/merge, đối chiếu delta Quân với main mới trước khi Quân tự commit; không ghi đè source từ baseline cũ.
- About GitHub đề xuất: **StudyMate - E-learning website built with PHP, PDO and MySQL**. Chưa đổi metadata GitHub trong lượt này.
- Hạn/rubric/kênh nộp vẫn chờ giảng viên/LMS. Công cụ đọc Chrome bị từ chối quyền ghi màn hình nên chưa đối chiếu được LMS; không chuyển mốc dự kiến khoảng 10/11 thành hạn chính thức.
- Cả ba nghiệm thu trên cùng SHA sau review/merge, chụp ảnh có thanh địa chỉ thật và quay phần trình bày theo yêu cầu được xác nhận. Không tự tạo chữ ký review, video thành viên hoặc trạng thái Submitted.
