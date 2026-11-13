# Tin nhắn đề nghị review phần Quân

@Tú @Vạn An, mình đã ghép xong phần Instructor/Admin/Gemini với bản nền của An `81dc740`, có sửa nền Tú `1a27ec1`. Phần bổ sung gồm validation quiz/chapter và trạng thái Admin, chuẩn hóa tham số phân trang dùng lại query chung, xử lý phản hồi/lỗi Gemini và đồng bộ tài liệu. Không thay vùng bảng, tìm UTF-8 hoặc startup mà hai bạn đã làm.

Kết quả local: 270 kiểm tra SQLite; 310 kiểm tra MySQL/Chrome, trong đó có 18 màn hình quản trị ở 390/768/1366; Compose build/health/login đạt và hash 12 bảng giữ qua restart/DB chậm. Các suite hồi quy của hai bạn cũng đạt. Các con số có phần lặp giữa suite, không cộng thành tổng ca nghiệp vụ độc lập.

Gemini `gemini-3.5-flash-lite` đã trả nội dung thật qua Apache/MySQL; hỏi và tóm tắt hiển thị trong Chrome. Có thêm mẫu thiếu thông tin, injection có kiểm soát và khóa archived. Lượt nội dung dừng ở ca đổi key trong cùng project; lượt khởi động mới bằng key giả đạt 503, quyền/quota app và học/quiz sau lỗi. Mình giữ cả log dừng và log kiểm lại, chưa ghi một lượt full live-suite đạt. Provider timeout/quota thật không được cố tình gây lỗi.

Nhờ Tú review `app/instructor_actions.php`, `app/admin_actions.php`, `app/management_support.php`, phần nối trong `app/services.php` và `app/ai_service.php`: ưu tiên quyền/CSRF, từ chối không đổi DB, transport tuple, decoder/timeout và tương thích probe/test cũ. Xem các câu trả lời live để xác nhận đúng ngữ cảnh bài. Ghi nhận xét theo file/hành vi cụ thể; ca nào cần kiểm lại thì nêu expected/actual và môi trường.

Nhờ An review HTML Instructor/Admin đang giữ từ nhánh của An, kiểm trực quan các màn hình mới ghép, focus/bàn phím/vùng bảng và luồng Student sau khi gặp lỗi AI. Ảnh Chrome tự động là bằng chứng viewport, chưa thay ảnh có thanh địa chỉ hoặc kiểm lại Edge trên SHA cuối.

Về tích hợp Git: hoàn tất review #14 rồi merge; #15 đổi base sang main, kiểm lại diff và review trước khi merge. Phần Quân sẽ commit riêng trên nền đã nhận `81dc740`, tránh đưa lại thay đổi của hai bạn vào commit của mình. Bản hiện tại chưa có commit mới của Quân; không ghi CI xanh khi GitHub chưa có check/status.

Sau góp ý, mình sửa và kiểm lại phần bị ảnh hưởng. Cả ba chốt cùng SHA, chạy kịch bản Instructor → Student → quiz/Gemini → Admin/DB, hoàn thiện phần trình bày và hồ sơ. Khoảng 10/11 vẫn là mốc dự kiến; mình sẽ đối chiếu rubric, hạn và kênh nộp khi có thông tin chính thức. Không đánh dấu Submitted trước khi nộp thực tế.
