# Kiểm công cụ PR #17 — 19/11/2026

Nền: 717c99a5c94e4541aa679896126bee1b80b1b04c. Candidate bổ sung bản sửa chưa commit tại thời điểm kiểm; xác minh bằng tools.source.json và compose.txt.source.json, không nhận các log này là lượt chạy trên một SHA chưa tồn tại.

Nguồn: kiểm tự động cục bộ macOS/Docker. Đây không phải review độc lập của Tú/An hoặc kết quả Cloud của Tú. Dữ liệu kiểm dùng project và volume riêng; cleanup đã hoàn tất. Không gọi Gemini thật.

- before-release.json: hai tệp local vô hại chưa được Git theo dõi lọt ZIP cũ.
- before-compose.json: WEB_PORT của host 49123 ghi đè cổng 49124 trong env-file trước sửa.
- release-and-env.txt: 33 kiểm tra đạt, không skip; ZIP chỉ lấy tệp Git hợp lệ, bỏ tệp nhạy cảm kể cả tracked; config Compose dùng cổng/credential/model QA và giữ Docker context/TLS.
- live-readiness.json: giải Compose config thật và gửi một HTTP health thật tới server loopback theo cổng QA, trong khi host WEB_PORT=49123. Up/down và đọc key được mô phỏng; không khởi động live containers, không đăng nhập hay gọi provider. Cleanup nhận cùng môi trường cô lập.
- compose.txt: build/chạy Compose thật với WEB_PORT, mật khẩu, DEMO_PASSWORD, DB_WAIT_TIMEOUT, AI key/model và profile của host cố ý sai (giá trị mẫu vô hại). Health/login ba role, service/pagination/UTF-8/model/AI offline, restart và chờ DB đạt; hash đủ 12 bảng giữ nguyên.

Không chạy lại toàn bộ suite SQLite/MySQL/Chrome hoặc Windows/Edge trong lượt sửa công cụ này. Runtime ứng dụng giữ nguyên so với 717c99a; các bằng chứng 14/11 và 15/11 giữ nguyên nguồn/ngày/SHA, không được đổi thành kết quả mới. Log gốc được giữ nguyên kể cả whitespace.
