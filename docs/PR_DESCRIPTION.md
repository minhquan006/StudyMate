## Mục đích

Chuẩn bị bản StudyMate để Tú và nhóm nghiệm thu trên Docker/MySQL. Bổ sung phân trang cho Student, Instructor, Admin và danh sách học viên; tổng số và tiến độ được tính từ dữ liệu toàn bộ, tránh truy vấn riêng từng dòng.

## Thay đổi chính

- Phân trang mặc định 12 dòng, tối đa 50; giữ bộ lọc và hiển thị trạng thái không có dữ liệu.
- Sửa quyền đọc mã nguồn của Apache trong Docker và lỗi mã CSRF 419 bị trả thành 500.
- Thống nhất model Gemini mặc định `gemini-2.5-flash-lite`, hỗ trợ `AI_MODEL`; xử lý phản hồi AI sai cấu trúc.
- Thêm kiểm thử HTTP, request đồng thời, chống gửi trùng và hoàn tác giao dịch khi lỗi trên DB dùng riêng cho kiểm thử.
- Bổ sung công cụ sao lưu/khôi phục, đóng gói source và tài liệu bàn giao T1–T5/A1–A5/Q1–Q5.

## Kết quả kiểm tra đã có

| Nội dung | Kết quả |
|---|---|
| Kiểm thử nghiệp vụ MySQL | 14/14 đạt |
| Kiểm thử nghiệp vụ SQLite | 14/14 đạt |
| Kiểm thử HTTP trên bản Docker/MySQL cài sạch | 78 ca đạt |
| Kiểm thử hợp đồng AI bằng dữ liệu mô phỏng, không gọi Gemini | 24 kiểm tra đạt |
| Trình duyệt ở 390/768/1366 px | Chromium 235, Google Chrome 156 đạt 235, Microsoft Edge 142 đạt 235; các lượt tự động trên Linux |
| Số truy vấn khi đổi từ 12 lên 50 dòng | Không tăng theo số dòng |
| Sao lưu/khôi phục và khởi động lại | Dữ liệu cả 12 bảng được giữ nguyên |
| Hiệu năng catalog, 100 khóa/500 bài/100 users | p95 HTTP nội bộ 3,14 ms; 5 lần làm nóng, 30 lần đo tuần tự |

Bằng chứng và SHA mã nguồn đã kiểm: `docs/evidence/final/README.md`. Phép đo hiệu năng trên không thay cho kiểm thử tải đồng thời hoặc đo trên máy chấm.

## Đề nghị kiểm tra trước khi merge

- Quân kiểm backend, CSDL, bảo mật và kết quả kiểm thử của phần Tú.
- An kiểm giao diện, phân trang và các màn hình Instructor/Admin.
- Tú kiểm handler/AI do Quân phụ trách theo phân công; người viết không tự duyệt phần của mình.

## Các điều kiện còn thiếu để đánh dấu Done

- Có người khác review, merge vào `main`, ghi SHA cuối và kiểm tra lại phần liên quan.
- Quân thử hỏi/tóm tắt Gemini thật, lỗi provider và quota bằng khóa riêng; 24 kiểm tra mô phỏng không thay nghiệm thu FR10.
- An kiểm trực quan và cài đặt độc lập chỉ theo README; các lượt Chrome/Edge tự động đã có bằng chứng.
- Quân xác nhận rubric, hạn nộp và yêu cầu Git/video/AI chính thức.
- Ba người tự trình bày trong video 1080p; kiểm gói nộp cuối và trạng thái nộp thành công.

Ảnh tự động chỉ chụp nội dung trang, không có thanh địa chỉ trình duyệt thật. Kết quả Windows do Tú cung cấp đã ghi riêng trong `docs/evidence/final/tu-windows-results.json`. Các commit do công cụ tạo mang tên Codex; không tính thay đóng góp cá nhân của sinh viên.
