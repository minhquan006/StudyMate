# Bàn giao cho Tú — đợt nghiệm thu 27/10/2026

Đây là kết quả thực hiện kỹ thuật theo tin nhắn phân công của Quân. **Chưa có task nào được tự nhận Done theo quy tắc của nhóm**: phải có người khác review, merge main, ghi SHA bản nghiệm thu và chạy lại phần liên quan. Không dùng kết quả bản cũ để thay cho bản cuối. Các bằng chứng mới nằm trong `docs/evidence/final/`; ảnh cũ vẫn giữ làm lịch sử.

## Tình trạng từng việc

| Mã | Phần đã chuẩn bị / kiểm chứng | Còn phải làm bởi người thật |
|---|---|---|
| T1 | Docker PHP 8.3 + MySQL 8.4, DB test riêng; 14 service checks; kiểm setup lặp, restart giữ dữ liệu | Quân review, merge main, xác nhận SHA main cuối |
| T2 | Phân trang 12, giới hạn tối đa 50 cho Student/Instructor/Admin/roster; tổng số độc lập trang hiện tại; aggregate progress/best score, không SELECT từng dòng | An/Quân review UI và bộ lọc |
| T3 | HTTP với phiên độc lập: enroll/progress lặp; UUID cùng/khác payload; DB snapshot trước/sau yêu cầu từ chối; trigger gây lỗi insert để chứng minh rollback toàn bộ | Quân review kiểm thử CSDL/bảo mật |
| T4 | DB riêng đúng 100 khóa/500 bài/100 users; warm-up 5, đo 30 HTTP catalog, p95 nearest rank, thông số máy trong performance.json | Chạy lại trên máy chấm nếu cấu hình khác; không suy ra khả năng chịu tải đồng thời từ phép đo tuần tự |
| T5 | Lệnh setup/health/backup/restore; restore DB mới, so khớp cả 12 bảng; kịch bản demo và gói source sạch | Hỗ trợ nhóm chạy bản merge cuối |
| A1 | Phân trang trước/sau, trang hiện tại, giữ bộ lọc, tổng số và empty state | An review |
| A2 | Chromium tự động ở 390/768/1366; ảnh Instructor/Admin/AI panel cùng public/Student | An rà trực quan, Quân review ảnh |
| A3 | Label/focus/Tab, nút chính tối thiểu 44 px; lỗi/empty thật được test | Đã bổ sung 235 ca trên Google Chrome 156 và 235 ca trên Microsoft Edge 142 bản Linux chính hãng; An còn review trực quan/khả năng sử dụng trên máy nhóm |
| A4 | Cài từ thư mục source sạch, project/volumes riêng; không dùng config.php, .env hoặc DB cũ | An phải tự làm một lượt độc lập chỉ theo README để thỏa điều kiện người kiểm tra độc lập |
| A5 | Bộ ảnh viewport thật và kịch bản video đã chuẩn bị | Ba người quay/nói thật video 1080p; ảnh có thanh địa chỉ thật nếu môn yêu cầu; không ghép thanh địa chỉ |
| Q1 | Ma trận FR01–FR12 bên dưới, checklist/rủi ro và người phụ trách | Quân xác minh rubric, hạn nộp chính thức, Git/video/cách nộp/quy định AI với giảng viên |
| Q2 | Direct POST sai quyền/ID; xóa/đổi thứ tự ở published/archived; CSRF mọi action; dữ liệu từ chối không đổi | Tú review handler/bảo mật, An review UI; Quân không tự duyệt |
| Q3 | 24 kiểm tra hợp đồng AI **offline**: lỗi key/mạng/timeout/quota và Unicode; HTTP AI disabled không chặn quiz | Quân cấu hình khóa Gemini riêng trong .env, thử ask/summarize thật và lỗi/quota provider. Không gửi khóa vào chat/Git |
| Q4 | README/SRS/Specification thống nhất model `gemini-2.5-flash-lite`, cấu hình `AI_MODEL`; cập nhật phân trang và giới hạn thực tế | Quân review tài liệu, xác minh model/free-tier còn dùng được bằng provider thật |
| Q5 | Báo cáo, known issues, checklist và ZIP source có kiểm tra cấu trúc/integrity, manifest | Chốt commit đã review, quay video, bổ sung rubric/Git evidence thật, kiểm ZIP cuối rồi nộp và xác nhận Submitted |

## Ma trận FR

| FR | Ca nghiệm thu / bằng chứng | Chủ trì | Trạng thái |
|---|---|---|---|
| FR01 | Đăng ký bỏ qua role giả, login 3 role, revoke phiên, profile | Tú | HTTP đã kiểm; chờ review |
| FR02 | Wrong role/owner/enrollment, admin lock, CSRF từng action | Tú/Quân | HTTP đã kiểm; chờ review |
| FR03 | Catalog published, search, category, phân trang, empty | An/Tú | Browser + HTTP; chờ review |
| FR04 | Enroll đồng thời chỉ 1 dòng, archived chặn người mới | Tú | HTTP + DB snapshot |
| FR05 | Complete đồng thời chỉ 1 dòng, archived vẫn học | Tú/An | HTTP + DB snapshot |
| FR06 | 14 service checks; UUID/cạnh tranh/rollback; quiz/result thật | Tú | MySQL + HTTP + browser |
| FR07 | Tạo course/chapter/lesson/quiz/publish, ID sai, cấu trúc khóa | Quân | HTTP; Tú review |
| FR08 | Category restrict, cấm khóa Admin, khóa/mở thu hồi phiên | Quân | HTTP; Tú review |
| FR09 | Tổng số toàn tập, trang 12/tối đa50, progress/best aggregate | Tú/An/Quân | MySQL + browser |
| FR10 | Offline error/quota/input; disabled vẫn học/quiz | Quân | **Chưa nghiệm thu provider thật** |
| FR11 | 3 kích thước, không overflow toàn trang, label/focus/nút | An | Chromium + Chrome/Edge thật tự động; kiểm trực quan của An còn thiếu |
| FR12 | Compose sạch, setup lặp/restart/restore, hướng dẫn, ZIP | Tú/Quân | Kỹ thuật đã chuẩn bị; review/video/nộp còn thiếu |

## Nghiệm thu chung bắt buộc

1. Sau review và merge, cả ba dùng cùng SHA main; ghi `git rev-parse HEAD`. DB nghiệm thu riêng, AI free tier của nhóm, không dùng dữ liệu cá nhân.
2. Quân đăng nhập Instructor: tạo khóa → chương → bài → quiz → publish; kiểm sai owner/CSRF.
3. An đăng ký Student mới → tìm khóa → enroll → học/ghi tiến độ → quiz → kết quả → hỏi/tóm tắt AI thật.
4. Tú đối chiếu enrollment/progress/quiz_answers trong MySQL, thử lặp/concurrent, chạy setup và restart không mất dữ liệu; kiểm backup/restore DB riêng.
5. Quân Admin khóa Student, kiểm phiên Student cũ bị chặn; mở khóa không hồi sinh phiên cũ. Thử truy cập sai quyền.
6. Ghi expected/actual, bằng chứng và SHA, không bỏ qua lỗi. Nếu sửa code sau quay video, chạy lại kiểm tra và quay lại phần bị ảnh hưởng.

## Kịch bản video 1080p

- 0:00–0:30: cả ba giới thiệu tên/phần việc thật, hiện SHA đã review, PHP/MySQL và health.
- 0:30–2:30 Quân: Instructor CRUD/publish + Admin lock/revoke, giải thích quyền.
- 2:30–5:00 An: Student toàn luồng, responsive, hỏi/tóm tắt AI thật (không quay .env).
- 5:00–6:30 Tú: dữ liệu MySQL, chống gửi trùng, 14 checks, restart và backup/restore.
- 6:30–7:00: giới hạn thật, tài liệu và kết quả test. Nội dung/màn hình phải đọc được; ba người tự trình bày, không tạo đóng góp giả.

## Known issues / phần không thể làm thay

- Chưa có Gemini credential dùng được trong môi trường này: không khẳng định FR10 đạt, không gọi giả rồi gắn nhãn thật. Dùng `.env` trên máy nhóm, `AI_ENABLED=1`, `GEMINI_API_KEY` nhập riêng; không bật billing. `AI_MODEL` phải được probe với key thật.
- Chưa có rubric/hạn nộp chính thức từ giảng viên. 01/11 là mốc kế hoạch nội bộ, không tự biến thành deadline hệ thống.
- Không có người thật khác review/merge, độc lập cài đặt hoặc thuyết trình. Tú chuyển branch/patch cho Quân và An theo ownership; không tự duyệt thay họ.
- Đã có bằng chứng Chrome/Edge thật chạy tự động trên Linux. Kiểm trực quan bởi An, ảnh browser toolbar theo yêu cầu môn, video ba người, Insights/contributions và trạng thái nộp bài vẫn cần nhóm làm thật.
- Bản SRS `.docx` đã được tái xuất từ Markdown bằng Pandoc; nhóm cần review nội dung trước khi xuất bản PDF cuối.
- PHP built-in server chỉ local; bản nghiệm thu MySQL dùng Apache/Docker. Quota AI dùng flock một host, không phải giới hạn phân tán.


## Bổ sung sau khi Tú tự chạy trên Windows

Tú đã cung cấp kết quả 14/14 kiểm tra MySQL, log restart và ảnh backup/restore trên Windows. Hai mã SHA256 của bản xuất dữ liệu đã sắp xếp đều là `3429a288d96d820f027238832b2594c3afc55a5591ef6f6526fc8e93ae4d8353`. Bản khôi phục có đủ 12 bảng. Đây là bằng chứng do Tú cung cấp, được ghi trong `evidence/final/tu-windows-results.json`; không giả lập rằng công cụ truy cập được máy Windows.

Đã chạy thêm 235 kiểm tra trên Google Chrome 156.0.8078.47 và 235 kiểm tra trên Microsoft Edge 142.0.3595.94 chính hãng, chế độ tự động headless trên Linux. Bản cài được kiểm SHA256 theo metadata HTTPS chính thức. Kết quả trong `evidence/final/chrome/` và `edge/`. Hai lượt này không thay việc An trực tiếp review giao diện hoặc kiểm riêng trên Windows.

Bộ HTTP mở rộng đạt 84 ca, bổ sung trọn luồng khóa mới: Instructor tạo/publish → Student mới enroll → hoàn thành bài → nộp quiz → thấy 100 điểm và trạng thái hoàn thành; preview không ghi dữ liệu; AI chưa cấu hình không làm thay đổi DB. Kết quả trong `evidence/final/integrated-http/`.

Đường mạng tới Gemini từ cloud trả proxy 403, không có GEMINI_API_KEY trong host/container. Đã lưu yêu cầu domain và khóa vào bản nháp cài đặt cloud; chưa áp dụng/publish và chưa có khóa nên không thể gọi provider thật. API GitHub để đọc/quản lý PR cũng trả Forbidden; Git đọc/đẩy nhánh vẫn hoạt động. Main chưa chứa thay đổi này tại thời điểm kiểm tra. Không tự duyệt hoặc vượt yêu cầu review để merge.
