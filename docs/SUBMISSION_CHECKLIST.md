# Checklist gói nghiệm thu cuối StudyMate

Checklist cũ cho mốc frontend 12/10 được lưu tại `archive/SUBMISSION_CHECKLIST_2026-10-12.md`; không dùng hạn cũ đó làm hạn nộp bản cuối. Hạn chính thức phải do Quân xác minh với giảng viên.

## Đã chuẩn bị kỹ thuật

- [x] Nhánh `codex/tu-final-acceptance`, PR #13 do Tú tạo; main còn chờ review/merge.
- [x] Docker/MySQL, DB test riêng và kiểm chạy lại; 14 service checks MySQL và SQLite.
- [x] 84 ca HTTP tích hợp, gồm gửi đồng thời, chống trùng và rollback khi lỗi.
- [x] Phân trang/tổng số/truy vấn tổng hợp; bộ dữ liệu hiệu năng 100/500/100.
- [x] 235 kiểm tra Chromium; thêm 235 Google Chrome và 235 Microsoft Edge thật tự động.
- [x] 24 kiểm tra AI mô phỏng; AI disabled không chặn học/quiz.
- [x] Backup/restore cả 12 bảng, có bằng chứng cloud và ảnh SHA256 khớp trên máy Tú.
- [x] Hướng dẫn tiếng Việt, mô tả PR, SRS/Specification, báo cáo và kịch bản demo.

## Điều kiện chưa thể tự đánh dấu

- [ ] Quân/An review theo ownership và duyệt; merge main, ghi SHA cuối rồi chạy lại phần liên quan.
- [ ] An trực tiếp cài độc lập và review trực quan, bàn phím, lỗi/empty trên máy nhóm.
- [ ] Quân cung cấp khóa Gemini riêng, xác nhận free tier và kiểm ask/summarize/provider/quota thật.
- [ ] Quân xác minh rubric, hạn nộp, cách nộp và quy định Git/video/AI (xem course-rules.md).
- [ ] Cả ba nghiệm thu cùng SHA đã merge và tự trình bày trong video 1080p.
- [ ] Nếu môn yêu cầu, chụp ảnh trình duyệt có thanh địa chỉ thật, không ghép URL.
- [ ] Bổ sung minh chứng Git thật, kiểm ZIP cuối, nộp và xác nhận Submitted.

## Kiểm gói trước nộp

Source phải có README/Readme.txt, app, public, database/schema.php, database/schema.mysql.sql, seed, Dockerfile/compose, docs/tests/tools. Không chứa `.env`, config.php local, khóa API, DB/session hoặc backup SQL cá nhân. `docker/config.php` là cấu hình Docker dùng biến môi trường, phải giữ.

ZIP hiện tạo là ứng viên source để review, không mặc nhiên là gói bài nộp đủ video/rubric. Mở kiểm ZIP sau lần đóng gói cuối. Không dùng kết quả hoặc video của commit cũ nếu code liên quan đã thay đổi.
