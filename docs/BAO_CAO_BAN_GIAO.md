# Báo cáo bàn giao StudyMate cho Tú

Ngày lập: 27/10/2026. PR #13: https://github.com/minhquan006/StudyMate/pull/13 . Nhánh: codex/tu-final-acceptance. Đây là ứng viên để nhóm review, chưa phải xác nhận đã merge hoặc nộp bài.

## Kết quả đã thực hiện

| Hạng mục | Kết quả | Nguồn bằng chứng |
|---|---|---|
| PHP/MySQL và SQLite | 14/14 kiểm tra nghiệp vụ mỗi DB; lint đạt | mysql-service.txt, sqlite-and-lint.txt |
| Luồng HTTP tích hợp | 84 ca đạt; tạo khóa → enroll → học → quiz → kết quả và hoàn thành | integrated-http/http-results.json |
| Gửi đồng thời/rollback | Enrollment/progress không trùng; UUID cùng/khác payload; lỗi insert hoàn tác toàn bộ attempt | Bộ HTTP và snapshot DB |
| Phân trang/thống kê | 12 mặc định, tối đa 50; số query cố định ở 12 và 50 dòng | query-budget.json |
| Hiệu năng | 100 khóa, 500 bài, 100 users; 5 làm nóng + 30 HTTP catalog; p95 gốc 3,14 ms | performance.json; không suy ra tải đồng thời |
| Chromium | 235 kiểm tra đạt, 390/768/1366 | browser-results.json |
| Google Chrome chính hãng | 156.0.8078.47, 235 kiểm tra đạt trên Linux tự động | chrome/browser-results.json |
| Microsoft Edge chính hãng | 142.0.3595.94, 235 kiểm tra đạt trên Linux tự động | edge/browser-results.json |
| AI mô phỏng | 24 kiểm tra đạt; không gọi provider thật | ai-offline.txt |
| Backup/restore/restart cloud | Dữ liệu 12 bảng khớp, setup lặp/restart giữ dữ liệu | restore.json, restart-and-clean-install.json |
| Máy Windows của Tú | 14 MySQL checks đạt; backup 36.990 byte, restore đủ 12 bảng và hai hash dữ liệu khớp | tu-windows-results.json, ảnh/log Tú cung cấp |

Các đường dẫn bằng chứng tính từ docs/evidence/final/. Ứng dụng PHP và public assets vẫn khớp bản đã kiểm theo validated-source-manifest.json; lần này bổ sung công cụ kiểm thử và tài liệu, không sửa logic ứng dụng.

## Bằng chứng máy Tú

Commit Tú đã chạy: f19f12089b695a08cb9fe117934ffae6db0d05a0. Cùng SHA256 dữ liệu DB gốc/khôi phục: 3429a288d96d820f027238832b2594c3afc55a5591ef6f6526fc8e93ae4d8353. Nguồn là ảnh và log Tú gửi, không phải công cụ truy cập từ xa vào Windows. Tiến độ sau restart do Tú xác nhận, tách khỏi việc log chỉ cho thấy HTTP 200.

## Việc thực sự chưa làm được

1. Gemini thật: host/container không có key; proxy tới generativelanguage.googleapis.com trả 403. Đã lưu yêu cầu domain/key trong bản nháp cài đặt cloud, chưa áp dụng/publish. Không coi AI mô phỏng là FR10 đạt. Có tools/probe_gemini.php để tự động thử hai request ask/summarize sau khi Quân cấp cấu hình hợp lệ.
2. Review/merge: API GitHub trả Forbidden, trong khi Git đọc/đẩy nhánh hoạt động. PR #13 đã do Tú mở, cần Quân/An review thật theo ownership. Không tự duyệt hoặc bỏ qua bảo vệ main.
3. Cài độc lập và review trực quan bởi An: kiểm tự động Chrome/Edge đã xong, nhưng không thể nhận thay việc An trực tiếp làm.
4. Rubric/hạn nộp/quy định Git, video, AI: chưa có tài liệu chính thức; Quân cần xác minh và cập nhật course-rules.md.
5. Video và nộp bài: đã chuẩn bị kịch bản/slide/source; chưa có ba người trình bày, ảnh toolbar theo yêu cầu môn, thông tin đăng nhập hệ thống nộp hoặc quyền xác nhận Submitted.

## Cách tiếp tục tối thiểu

- Quân/An review PR #13, phản hồi góp ý và duyệt; sau merge ghi SHA main rồi chạy lại phần liên quan.
- Quân nhập GEMINI_API_KEY riêng, AI_ENABLED=1 trong .env; chỉ dùng free tier và không bật billing. Build lại, chạy lệnh probe trong README, kiểm chất lượng và quota provider thật. Không gửi khóa vào chat.
- Ba người dùng cùng SHA cuối, chạy nghiệm thu theo FINAL_ACCEPTANCE.md; trình bày video 1080p theo slide và kịch bản. Không coi slide hoặc bản demo tự động là video ba người đã hoàn tất.
- Quân xác minh quy định chính thức, bổ sung bằng chứng thật và kiểm ZIP cuối trước khi nộp.

## Phần Tú trình bày

1. Hiện SHA và Docker/MySQL đang chạy; giải thích document root public/.
2. Chạy 14 checks trên project QA dùng một lần, nêu ví dụ quiz UUID và FK.
3. Minh họa progress trước/sau restart, giải thích setup không reset DB.
4. Chỉ file backup ngoài repo, DB restore riêng và hai mã SHA256 dữ liệu khớp.
5. Giải thích phân trang/tổng số, số query không tăng theo dòng và giới hạn phép đo p95.
6. Nêu đúng phần còn thiếu: Gemini thật, review, nghiệm thu cùng commit và nộp bài. Các commit công cụ mang tên Codex, không nhận thành đóng góp tự viết của sinh viên.
