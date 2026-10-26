# Bằng chứng mới — 27/10/2026

Code commit: `c1e0664edf220f07e6664459b3568c1c01550423` trên branch `codex/tu-final-acceptance`; commit kế tiếp chỉ thêm bằng chứng. Code chưa tự coi là main đã review.

Runtime: PHP 8.3.36 (cli) (built: Oct 21 2026 15:57:18) (NTS); Docker Apache, MySQL 8.4.13. `validated-source-manifest.json` đối chiếu hash tất cả PHP ứng dụng/schema/seed và public assets trong image cài sạch với code commit trên.

- `mysql-service.txt`: 14 service checks trên DB riêng, không phải DB phát triển.
- `sqlite-and-lint.txt`: hồi quy 14 checks và lint.
- `http-results.json`: 78 ca Docker/MySQL source sạch, phiên độc lập/concurrency và rollback bằng trigger test root trong DB dùng một lần. Không cấp SUPER cho app, không giảm xác minh/trust toàn máy.
- `ai-offline.txt`: 24 kiểm tra offline, không gọi Gemini thật.
- `browser-results.json` + PNG: 235 kiểm tra Chromium, 390/768/1366; có quiz/result, Instructor/Admin/AI panel. Không có thanh địa chỉ hoặc phiên bản Chrome/Edge thật.
- `performance.json`: đúng 100 users/100 courses/500 lessons, 5 warm-ups + 30 HTTP loads, nearest-rank p95 và cấu hình máy. Kết quả chỉ áp dụng phép đo tuần tự này.
- `query-budget.json`: số query cố định khi tăng trang 12 lên 50.
- `restore.json`: backup/restore vào DB mới, hash nội dung toàn bộ 12 bảng khớp.
- `restart-and-clean-install.json`: source không dùng config/env/DB cũ; setup lặp và restart giữ dữ liệu/health.

Lỗi setup ban đầu (quyền Apache, mã CSRF trên Apache, thiếu dung lượng cache) đã được xử lý và chạy lại. Xem ../../FINAL_ACCEPTANCE.md để biết phần chờ người review, credential thật, rubric, video và nộp bài.
