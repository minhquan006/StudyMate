# Đối chiếu yêu cầu môn học do Quân cung cấp

Cập nhật 30/10/2026. Phần lab dưới đây dựa trên nội dung Quân cung cấp. Đã đọc trực tiếp file `503073-FinalProject-V1.docx` đang lưu cạnh checkout; chưa truy cập eLearning hoặc nhận Rubric dành cho StudyMate. Ngày 30/10 Quân xác nhận giảng viên **chỉ nói file để tham khảo**. Giữ phạm vi StudyMate, không tự áp chức năng note hoặc mở rộng stack.

| Yêu cầu được cung cấp | Đối chiếu StudyMate | Trạng thái / việc cần bổ sung |
|---|---|---|
| Nhóm tối đa3 SV, chốt đăng ký đề tài | SRS có Quân, An, Tú; đề tài StudyMate | Quân kiểm tra đăng ký thành công tại eLearning; không có bằng chứng trạng thái đăng ký trong repo |
| Template: frontend ít nhất5 trang | Có home, courses, detail, login, register, student, learn, quiz, result, profile | Có mã PHP tương ứng; không tự chứng minh bộ template tĩnh đã được nộp |
| Backend có menu và giao diện chức năng | Instructor quản lý khóa/nội dung/quiz/học viên; Admin users/courses/categories | Có code, HTTP và ảnh kiểm thử; lấy ảnh bản cuối theo yêu cầu lab |
| Bài template nộp PDF màn hình và bộ web tĩnh HTML/CSS/JS | Repo hiện là PHP render HTML, CSS local; JS không bắt buộc cho luồng hiện tại | Cần Quân kiểm tra bài template đã nộp; bản source PHP không được tự coi là thay thế mọi tệp nộp giai đoạn trước |
| Thiết kế sơ bộ CSDL, nộp PDF | Có schema12 bảng, SRS và hai sơ đồ ERD | Đã chuẩn bị PDF thiết kế để review; Quân xác nhận đã nộp đúng bài |
| Hoàn thành tối thiểu70% frontend bằng PHP | Có các trang Public/Student và backend PHP với PDO/session thật | Có bằng chứng chức năng, nhưng chưa có mẫu số/cách chấm nên không tuyên bố đã đạt70% theo điểm môn |
| PDF giao diện PHP, chụp thanh địa chỉ Web | Có screenshot kiểm thử; ảnh tự động không có toolbar | Cần chụp browser thật có URL rồi tạo/nộp PDF; không ghép hoặc vẽ thanh địa chỉ giả |
| Code thuần PHP | PHP8.3+, PDO, native session, không Laravel/React | Phù hợp kiến trúc hiện có |
| Tham khảo 503073-FinalProject-V1.docx | Đã đọc văn bản và kiểm bản render ngày 30/10 | File có rubric ứng dụng note và yêu cầu hồ sơ; cách áp dụng cho đề tài StudyMate còn cần giảng viên xác nhận |

## Nội dung đã đọc trong file tham khảo

Nguồn: `503073-FinalProject-V1.docx`, SHA256 `1657b158ad020fe4924f451a0bac4128c36bed2983b3763a406c8999b73b033c`. Số trang dưới đây theo bản render LibreOffice 15 trang ngày 30/10, không phải số trang được giả định từ XML.

| Nội dung trong file | Vị trí | Xử lý cho StudyMate |
|---|---|---|
| Mô tả ứng dụng note; yêu cầu tuân thủ nội dung đỏ | Mở đầu, trang 1; rubric mục IV | Có khác biệt với đề tài nhóm. Theo xác nhận của Quân, giữ StudyMate; không tự chép 32 chức năng note vào SRS hoặc tự chấm tương đương |
| UI/UX, responsive, offline và triển khai online nằm trong rubric mẫu | Mục IV, trang 11 | Dùng để trao đổi tiêu chí; không tự thêm offline, service worker hoặc hosting ngoài phạm vi đã chốt |
| Bốn tuần lịch liên tiếp, mỗi thành viên ít nhất hai commit có ý nghĩa mỗi tuần; có quy định minh chứng Git | Mục V, trang 12–13 | Chưa xác minh kỳ phát triển chính thức hoặc mức áp dụng cho nhóm. Không tạo lịch sử, chữ ký hoặc commit giả. ZIP review hiện không có `.git`, không thay bộ minh chứng lịch sử nếu tiêu chí này áp dụng |
| Vấn đáp riêng để kiểm tra đóng góp và hiểu mã nguồn | Mục VI, trang 13 | Mỗi thành viên cần giải thích và thao tác phần mình; không quy công việc do công cụ tạo thành peer review của Tú/An |
| Rubric do giảng viên cung cấp, source, Readme, video demo của tất cả thành viên tối thiểu 1080p | Mục VII, trang 13–14 | Đã xác nhận các nội dung này có trong file tham khảo. Chưa nhận Rubric áp dụng StudyMate, chưa có video thật; thời lượng 6–8 phút vẫn là kế hoạch nội bộ |
| Nộp ZIP đúng tên qua eLearning; thiếu source/video/Rubric có hậu quả về điểm | Mục VII, trang 14–15 | Chuẩn bị checklist và xác nhận với giảng viên/LMS; không coi ZIP source review là bộ nộp hoàn chỉnh |

File tham khảo không xác nhận ngày 01/11/2026 là hạn LMS của nhóm. Các yêu cầu lab 5 trang, PHP 70% và ảnh có thanh địa chỉ vẫn được ghi theo thông tin Quân cung cấp, không gán nguồn sai sang rubric note.

## Bộ ảnh PHP đề xuất

Chụp ít nhất Home, Catalog, Detail, Login, Register, Student dashboard, Learning, Quiz, Result, Instructor editor và Admin. Dùng dữ liệu demo thật; quiz/result phải thao tác thực tế. Mỗi ảnh có thanh địa chỉ trình duyệt thật, tiêu đề và mô tả ngắn; không để key/mật khẩu/đường dẫn chứa secret trong ảnh. Làm rõ chế độ DB và model AI đã thực sự dùng.

PDF thiết kế CSDL và PDF kiểm thử tự động được chuẩn bị trong thư mục bàn giao ngoài repo. Chúng không tự chứng minh trạng thái Submitted trên eLearning và không thay PDF màn hình PHP có addressbar.


## Cập nhật ngày 01/11/2026

Thông tin Quân được giảng viên nói là dự kiến khoảng 10/11; chưa có xác nhận ngày/giờ/kênh nộp qua LMS. Công cụ đọc Chrome bị từ chối quyền ghi màn hình nên chưa kiểm tra được cổng học tập trong lượt này. Các yêu cầu lab và file tham khảo ở trên giữ đúng nguồn; không đổi chúng thành rubric StudyMate đã được xác nhận.
