# Kiểm tra phần Quân trên nền main đã tích hợp — 14/11/2026

Nền main: aac87052135bea490856c648cc44f7b9bab57db8, đã nhận PR #14, #15 và #16. Source kiểm ngày 14/11 là nền này cộng phần Quân, sau đó được commit tại 83e8b68. Bản sửa ID ngày 15/11 có báo cáo và fingerprint riêng trong QUAN_REVIEW_FIXES_2026-11-15.md. Hash payload và runtime nằm trong [bằng chứng](evidence/quan-2026-11-14/README.md).

## Nội dung

Validation Instructor/Admin từ chối quiz sai cấu trúc/chỉ số đáp án, chapter chéo khóa, trạng thái tài khoản sai và category không tồn tại. Phân trang dùng helper kiểm tham số rồi giữ query/filter/tổng số chung. Gemini bổ sung xử lý timeout, JSON/safety/empty/thought và chặn chuyển key qua redirect. Giữ nguyên schema, startup, healthcheck, giao diện và forms.js của Tú/An.

## Kiểm mới

Quân chạy verify.py --suite all trong Terminal, trên bản local này:
- SQLite: 270 kiểm tra đạt.
- MySQL/Chrome: 310 kiểm tra đạt, gồm 40 browser assertions và 18 ảnh Instructor/Admin ở 390/768/1366.
- Dockerfile/Compose từ bản sao source sạch: build, health, login ba role, hồi quy phân trang/UTF-8/model/AI offline đạt; hash 12 bảng giữ sau restart và khởi động DB chậm.

Lượt tự động bổ sung trong phiên review: 17 ca form và 8 ca quiz-exit đạt trên Chrome 157/macOS, PHP 8.3.36/Apache, MySQL 8.4.13. Giữ action/mode/CSRF của Hỏi/Tóm tắt; native validation/lỗi server không gây kẹt pending; gửi lặp và Back; hủy logout bằng chuột/Enter giữ đáp án, xác nhận lại gửi đúng một POST; nộp quiz không cảnh báo nhầm; form khi tắt JavaScript. Không có JavaScript page error ở các nhóm này.

Đối chiếu 96/96 file payload đúng gói, không có file ngoài phạm vi trước khi bổ sung hồ sơ mới; hai README giống nhau; kiểm whitespace ngoài docs/evidence/ đạt; log bằng chứng giữ nguyên định dạng gốc. Các fingerprint runtime của ba suite khớp runtime tại 83e8b68; không chuyển kết quả này thành lượt kiểm bản sửa ID ngày 15/11. Hai lần chuẩn bị harness form bị lỗi được lưu riêng trong diagnostics; không sửa mã ứng dụng để làm pass.

## Giới hạn và review

Không gọi Gemini thật trong lượt 14/11; kết quả live ngày 01/11 giữ đúng nguồn/phạm vi lịch sử. Chưa thay review độc lập của Tú/An, kiểm Windows/Edge, nghiệm thu chung trên SHA đã merge hoặc xác nhận nộp bài. Quân tự commit/push; Tú review handlers/query/Gemini, An review HTML và tương tác form trên PR.
