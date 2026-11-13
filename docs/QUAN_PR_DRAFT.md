# Nội dung PR phần Quân

Tiêu đề đề xuất: `fix: validate management inputs and harden Gemini responses`

## Vấn đề và hành vi sau sửa

Hoàn thiện validation Instructor/Admin trên nền #15 `81dc740`: từ chối quiz sai cấu trúc/chỉ số đáp án, chapter ID chéo khóa, trạng thái tài khoản sai và category không tồn tại. Giữ nguyên dữ liệu khi từ chối. Chuẩn hóa tham số phân trang rồi dùng truy vấn/pager hiện có, giữ filter, tổng số và vùng bảng thao tác bàn phím.

Xử lý phản hồi Gemini thiếu/sai cấu trúc, safety/empty/thought và timeout; không chuyển key theo redirect. Giữ `geminiRequest()` và transport tuple tương thích các công cụ probe/test đã có. File env mẫu chọn model đã kiểm `gemini-3.5-flash-lite`; cấu hình riêng và fallback PHP được giữ nguyên.

## Phạm vi dữ liệu và tích hợp

Không đổi schema hoặc reset DB. Nhận nền Tú/An, không thay phân trang/query/healthcheck/startup bằng bản local cũ. Delta này chuẩn bị để áp dụng trên nhánh đã có #15 sau khi nhóm hoàn tất review/tích hợp các PR nền.

README/Readme, SRS/Specification, ma trận FR, thiết kế CSDL và checklist được cập nhật. Bản Word/PDF đã kiểm bằng render; log failed và các lượt kiểm lại giữ nguyên nguồn.

## Kiểm tra

- SQLite: 270 runner checks đạt.
- MySQL/Chrome: 310 runner checks đạt, 18 màn hình ở 390/768/1366 và click phân trang.
- Compose chính thức: build, health/login ba role, 8 phân trang, 58 UTF-8, 8 model, 24 AI offline; hash đủ 12 bảng giữ qua restart/DB chậm.
- Gemini HTTP/MySQL/UI: hỏi/tóm tắt hiển thị trong Chrome; mẫu thiếu dữ liệu, injection có kiểm soát và archived có phản hồi. Quota app bằng fixture trả 429; key giả từ project mới trả 503; học/complete/quiz vẫn hoạt động.
- Lượt live đầu dừng ở ca đổi key tại chỗ; lượt lỗi riêng sau đó đạt. Không ghi thành một lần full live-suite đạt. Provider timeout/quota thật chưa được cố tình gây lỗi.

Môi trường ngày 01/11/2026: macOS, PHP 8.3.36 container, MySQL 8.4, Chrome 156.0.8078.79. Báo cáo và fingerprint: `docs/QUAN_INTEGRATION_2026-11-01.md`, `docs/evidence/quan-2026-11-01/`.

Nhờ Tú review handlers/query/AI và các kiểm tra; An review HTML Instructor/Admin, responsive, bàn phím và ảnh. Peer review, SHA sau merge và nghiệm thu chung vẫn chờ nhóm. Rubric/hạn/kênh nộp, ảnh có thanh địa chỉ, video và Submitted được chốt riêng theo yêu cầu học phần.
