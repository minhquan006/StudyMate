# Bằng chứng mới — 27/10/2026

Commit mã nguồn đã kiểm: `c1e0664edf220f07e6664459b3568c1c01550423` trên nhánh `codex/tu-final-acceptance`; commit kế tiếp chỉ thêm bằng chứng. Code chưa tự coi là main đã review.

Môi trường chạy: PHP 8.3.36 (cli) (built: Oct 21 2026 15:57:18) (NTS); Docker Apache, MySQL 8.4.13. `validated-source-manifest.json` đối chiếu hash tất cả PHP ứng dụng/schema/seed và public assets trong image cài sạch với code commit trên.

- `mysql-service.txt`: 14 kiểm tra nghiệp vụ trên DB riêng, không phải DB phát triển.
- `sqlite-and-lint.txt`: hồi quy 14 kiểm tra nghiệp vụ và kiểm tra cú pháp.
- `http-results.json`: 78 ca Docker/MySQL source sạch, phiên độc lập, request đồng thời và hoàn tác giao dịch bằng trigger test root trong DB dùng một lần. Không cấp SUPER cho app, không giảm xác minh/trust toàn máy.
- `ai-offline.txt`: 24 kiểm tra AI mô phỏng, không gọi Gemini thật.
- `browser-results.json` + PNG: 235 kiểm tra Chromium, 390/768/1366; có quiz/result, Instructor/Admin/AI panel. Không có thanh địa chỉ hoặc phiên bản Chrome/Edge thật.
- `performance.json`: đúng 100 người dùng/100 khóa/500 bài, 5 lượt làm nóng và 30 lượt tải HTTP, p95 theo phương pháp nearest-rank và cấu hình máy. Kết quả chỉ áp dụng phép đo tuần tự này.
- `query-budget.json`: số query cố định khi tăng trang 12 lên 50.
- `restore.json`: backup/restore vào DB mới, hash nội dung toàn bộ 12 bảng khớp.
- `restart-and-clean-install.json`: source không dùng config/env/DB cũ; setup lặp và restart giữ dữ liệu/health.

Lỗi setup ban đầu (quyền Apache, mã CSRF trên Apache, thiếu dung lượng cache) đã được xử lý và chạy lại. Xem ../../FINAL_ACCEPTANCE.md để biết phần chờ người review, khóa Gemini thật, rubric, video và nộp bài.


## Bổ sung sau lượt kiểm đầu

- `chrome/`: Google Chrome 156 chính hãng, 235 ca tự động trên Linux đạt; có ảnh mẫu Instructor 390 px.
- `edge/`: Microsoft Edge 142 chính hãng, 235 ca tự động trên Linux đạt; có ảnh mẫu Admin 390 px.
- `integrated-http/`: 84 ca đạt, bổ sung Student hoàn thành khóa do Instructor vừa tạo và preview không ghi dữ liệu.
- `tu-windows-results.json`: ghi nhận ảnh/log do Tú cung cấp, gồm 14 checks MySQL và hash dữ liệu 12 bảng sau restore khớp. Không phải kết quả do công cụ điều khiển Windows.
- `gemini-live-blocked.json`: probe không gửi request vì chưa có cấu hình/key. Không coi là provider đã đạt.

Các binary Chrome/Edge được tải qua HTTPS từ repository chính hãng, kiểm SHA256 theo metadata chính thức. Các lượt này là headless trên Linux; không thay review trực quan hoặc cài độc lập của An.

Báo cáo tổng hợp: `../../BAO_CAO_BAN_GIAO.md`; checklist hiện hành: `../../SUBMISSION_CHECKLIST.md`. Checklist frontend 12/10 cũ đã chuyển vào archive để tránh nhầm hạn nộp.
