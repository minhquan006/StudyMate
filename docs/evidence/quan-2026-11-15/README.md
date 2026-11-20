# Bằng chứng sửa ID PR #17 — 15/11/2026

Nền mã trước sửa: 83e8b68. Các lần kiểm sau chạy trên candidate chưa commit; không gán SHA nền thành SHA của bản sửa. Ba runtime fingerprint và manifest gói cập nhật dùng để đối chiếu với commit Quân sẽ tự tạo.

before-sqlite.txt: tái hiện ba ca Tú nêu trên SQLite riêng. Cả ba trả 303 và đổi dữ liệu; before-runtime-fingerprint.json ghi mã ứng dụng cũ.

sqlite.txt: 447 runner checks đạt. mysql.txt: 487 runner checks đạt, gồm 40 browser assertions và 18 ảnh Chrome 157/macOS ở 390/768/1366. Trong mỗi driver, 50 ca ID sai trả 422 và hai ca ID hợp lệ nhưng không tồn tại trả 404; 52 lần từ chối đều so sánh snapshot đủ 12 bảng trước/sau. Các luồng tạo/sửa/xóa hợp lệ vẫn chạy. Không cộng các assertion hoặc suite chồng lấn thành tổng ca nghiệp vụ độc lập.

compose.txt: build Dockerfile/Compose của repository từ bản sao source sạch, PHP 8.3.36, MySQL 8.4, health/login/hồi quy đạt; hash dữ liệu 12 bảng giữ sau restart và khi MySQL khởi động muộn. Các project/container/volumes dùng riêng đã được dọn.

Không gọi Gemini thật trong lượt này; không chạy lại Windows/Edge của An. Ảnh là viewport thật, không có thanh địa chỉ browser. Giữ nguyên bằng chứng 14/11 và các lượt lịch sử; log raw giữ nguyên khoảng trắng/định dạng. Kiểm whitespace mã/tài liệu cần loại docs/evidence/ khỏi phạm vi. checksums.json là kiểm tra integrity, không phải chữ ký review của thành viên.
