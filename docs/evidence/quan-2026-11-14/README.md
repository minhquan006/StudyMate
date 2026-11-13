# Bằng chứng tích hợp phần Quân — 14/11/2026

Source: main aac8705 sau khi merge #14/#15/#16, cộng 96 file phần Quân chưa commit. Ba fingerprint *.txt.source.json cùng source-fingerprint.json của phần form khớp runtime hiện tại. summary.json ghi hash của 96 file payload để đối chiếu với commit sau này.

Quân tự chạy verify.py --suite all trong Terminal: SQLite 270, MySQL/Chrome 310 (đã gồm 40 browser assertions/18 ảnh), Compose đạt. Không cộng các suite chồng lấn thành tổng ca nghiệp vụ độc lập. Các thời gian và log gốc được giữ nguyên.

forms/: lượt kiểm tự động bổ sung trong phiên review trên macOS, Chrome 157, PHP 8.3.36/Apache, MySQL 8.4.13, DB/volumes riêng; 17 ca form và 8 ca quiz-exit đạt. Dùng image PHP có sẵn, mount snapshot read-only; không ghi thành một lần clean build mới. Chạy các nhóm QA_CASE_SET=review và quiz-exit của tests/form-feedback.cjs. Không thay kiểm trực quan hoặc review độc lập của Tú/An.

diagnostics/: hai lần chuẩn bị harness dừng trước khi chạy ca form: thiếu mountpoint storage trong bản sao tạm và lỗi scope của đoạn probe PHP. Đã sửa công cụ kiểm tra, giữ nguyên runtime của ứng dụng, rồi chạy lượt đạt riêng. Các container/DB/volume do các lượt này tạo đã được dọn.

Gemini thật, Edge/Windows và video/hồ sơ nộp không được kiểm trong lượt mới. Kết quả ngày 01/11 giữ nguyên ở thư mục lịch sử.

Các log và ảnh ở đây không có config.php, .env hoặc khóa Gemini. checksums.json đối chiếu các file bằng chứng, không dùng làm chữ ký nghiệm thu nhóm.
