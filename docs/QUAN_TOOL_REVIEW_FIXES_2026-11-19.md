# Sửa công cụ theo review PR #17 — 19/11/2026

Nền trước sửa: 717c99a5c94e4541aa679896126bee1b80b1b04c. Phạm vi là hai góp ý trong review backend của Tú, không thay mã xử lý ứng dụng hoặc ghi lại bằng chứng cũ.

## Đóng gói release

Đã tái hiện tệp .txt và dump .sql chưa được Git theo dõi lọt ZIP cũ. package_release.py lấy danh sách từ Git index bằng ls-files --stage -z, chỉ nhận tệp thường thuộc phạm vi source. Loại symlink, .env/config riêng, khóa riêng, dump/backup và DB/session kể cả lỡ tracked. Giữ .env.example, docker/config.php, database/schema.mysql.sql và storage/.gitkeep. Tệp mới cần add/commit trước đóng gói; manifest giữ SHA nền, trạng thái working tree và hash nội dung candidate. Không ghi đè ZIP đã có, không cho output nằm trong checkout.

## Cô lập cấu hình QA

compose_env.py loại các biến ứng dụng và biến chọn project/Compose do host export. Runner compose.py và live_http.py dùng cùng môi trường này khi up/exec, chẩn đoán và cleanup. Giữ cấu hình kết nối Docker gồm PATH/HOME, DOCKER_CONFIG, DOCKER_CONTEXT, DOCKER_HOST và TLS/plugin discovery. Env-file, override và tên project QA tiếp tục do runner chọn. Khóa AI thật của host không được truyền qua Compose interpolation.

## Kiểm tra

33 kiểm release/config đạt, không skip. Lượt Compose thật có host WEB_PORT, credential, DEMO_PASSWORD, model/key, DB_WAIT_TIMEOUT và profile sai vẫn đạt health, đăng nhập ba role, hồi quy, restart và startup chờ MySQL; hash 12 bảng giữ nguyên. Kiểm live readiness dùng Compose config thật và HTTP loopback thật: Compose và runner dùng cùng cổng QA, cleanup được cô lập. Riêng up/down/key loading của kiểm readiness được mô phỏng và dừng trước login/provider; không nhận đây là live Gemini hoặc một lượt live Apache/MySQL hoàn chỉnh.

Nguồn là kiểm tự động cục bộ macOS/Docker. Không chạy lại full SQLite/MySQL/Chrome, Windows/Edge hoặc Gemini thật trong lượt này. Mã runtime ứng dụng giữ nguyên; các kết quả cũ được giữ riêng. Xem [bằng chứng, hash và phạm vi](evidence/quan-2026-11-19-tools/README.md).

## Review tiếp

Sau khi Quân commit/push, Tú review lại hai điểm trên đúng SHA mới; An hoàn tất phần form/QA của mình. Chờ kết luận review trước merge. Nghiệm thu cùng SHA main cuối, Gemini thật và hồ sơ nộp tiếp tục thực hiện riêng.
