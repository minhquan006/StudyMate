# StudyMate — Báo cáo kiểm thử thủ công của Quân

- Người kiểm thử: Nguyễn Minh Quân — MSSV 52400227.
- Thời gian: 15–16/10/2026 (Asia/Ho_Chi_Minh).
- Mã nguồn: https://github.com/minhquan006/StudyMate
- Nhánh làm việc: `fix/instructor-workflow`.
- Môi trường: macOS, PHP local tại `http://localhost:8001`, PDO MySQL, trình duyệt Chrome.
- Model thiết lập cho đợt kiểm thử AI: `gemini-3.5-flash-lite`.
- Phương pháp: thao tác thủ công trên giao diện; đối chiếu ảnh, nhật ký và xác nhận của người kiểm thử. Không bao gồm kết quả chạy bộ kiểm thử tự động.

## 1. Dữ liệu kiểm thử

| Thành phần | Giá trị |
| --- | --- |
| Khóa kiểm thử | ID 4 — Kiểm thử Instructor 15-10 đã chỉnh sửa |
| Chủ khóa | `instructor1@studymate.test` |
| Instructor không sở hữu khóa | `instructor2@studymate.test` |
| Học viên đã ghi danh | `student2@studymate.test` |
| Học viên chưa ghi danh khóa ID 4 | `student1@studymate.test` |
| Quản trị viên | `admin@studymate.test` |
| Nội dung | 1 chương, 1 bài học, quiz 5 câu; ngưỡng đạt 70% |
| Điểm dùng để kiểm tra bảo toàn kết quả | Lần đầu 80%; lần làm lại 60%; điểm tốt nhất giữ 80% |

Không ghi API key hoặc mật khẩu kết nối CSDL vào báo cáo. `config.php` là cấu hình riêng trên máy, không được Git theo dõi.

## 2. Quy ước bằng chứng

- **NK**: kết quả trong nhật ký `QA_Quan.md` do người kiểm thử cung cấp.
- **XN**: người kiểm thử xác nhận đạt trong phiên hướng dẫn; chưa có ảnh riêng trong bộ ảnh tham chiếu ở mục 7.
- **A1–A6**: ảnh đã đối chiếu, được liệt kê ở mục 7.
- **Đạt giao diện**: chỉ xác nhận giao diện; chưa kết luận về yêu cầu POST được sửa trực tiếp.

Các ca có nguồn XN là kết quả người kiểm thử báo lại, không phải thao tác được công cụ kiểm thử tự động thực hiện. Nguồn bằng chứng của từng ca được ghi riêng trong bảng.

## 3. Instructor và luồng học tập

| Mã | Thao tác | Kết quả mong đợi | Kết quả thực tế | Đánh giá | Nguồn |
| --- | --- | --- | --- | --- | --- |
| Q-I01 | Tạo khóa mới | Lưu ở trạng thái draft | Tạo khóa ID 4, hiển thị Bản nháp | Đạt | NK |
| Q-I02 | Sửa thông tin khóa và tải lại | Lưu bền thông tin mới | Tên khóa được sửa thành Kiểm thử Instructor 15-10 đã chỉnh sửa; tải lại vẫn giữ thông tin đã lưu | Đạt | XN |
| Q-I05a | Xuất bản khóa chưa có chương | Từ chối, giữ draft | Báo “Khóa cần ít nhất một chương.”; vẫn Bản nháp | Đạt | NK |
| Q-I05b | Xuất bản khóa có chương chưa có bài | Từ chối, giữ draft | Báo “Mỗi chương cần ít nhất một bài.”; vẫn Bản nháp | Đạt | NK |
| Q-I05c | Xuất bản khi chưa có quiz hợp lệ | Từ chối, giữ draft | Báo “Cần quiz hợp lệ với ít nhất 5 câu.”; vẫn Bản nháp | Đạt | NK |
| Q-I06a | Xuất bản khóa đủ nội dung và quiz | Chuyển sang published | Hiển thị “Đã cập nhật trạng thái.” và “Đã xuất bản” | Đạt | NK |
| Q-I06b | Tìm khóa đã xuất bản trong danh sách công khai | Hiển thị khóa đã xuất bản | Tìm thấy đúng 1 khóa với tên và mô tả đã lưu | Đạt | NK |
| Q-I07 | Instructor khác mở trang sửa khóa ID 4 | Từ chối, không hiện form sửa | Giảng viên thứ hai nhận “Không tìm thấy khóa học.” | Đạt | NK |
| Q-E01 | Student2 ghi danh và mở khóa ID 4 | Truy cập được bài học | Hiển thị bài Giới thiệu khóa học, tiến độ ban đầu 0% | Đạt | NK |
| Q-I08a | Đưa khóa đã có enrollment về nháp | Từ chối, giữ published | Báo “Chỉ đưa published chưa enrollment về nháp.” | Đạt | NK |
| Q-I08b | Mở tab Nội dung sau khi có enrollment | Khóa thao tác đổi cấu trúc trên giao diện | Báo cấu trúc đã đóng; không có form thêm bài và nút xóa | Đạt giao diện | NK |
| Q-Q00 | Mở quiz trước khi hoàn thành đủ bài | Chặn truy cập/làm quiz | Người kiểm thử xác nhận bị chặn khi chưa hoàn thành đủ bài | Đạt | XN |
| Q-Q01 | Nộp quiz đúng 4/5 câu | Điểm 80%, đạt ngưỡng 70% | Hiển thị 80%, Đạt, ngưỡng 70% | Đạt | NK |
| Q-C01 | Hoàn thành đủ bài và đạt quiz | Ghi nhận hoàn thành khóa | Dashboard hiển thị tiến độ 100%, đã hoàn thành, điểm tốt nhất 80% | Đạt | NK |
| Q-Q02 | Làm lại đúng 3/5 câu, bỏ trống 2 câu | Điểm 60%, chưa đạt | Hiển thị 60%, chưa đạt | Đạt | XN |
| Q-C02 | Xem Dashboard sau lần làm lại điểm thấp | Giữ hoàn thành và điểm tốt nhất | Tiến độ 100%, đã hoàn thành, điểm tốt nhất 80% | Đạt | XN |
| Q-I09 | Chủ khóa xem danh sách học viên khóa ID 4 | Hiển thị đúng kết quả học tập | Học viên thứ hai: tiến độ 100%, điểm tốt nhất 80, hoàn thành Có | Đạt | XN |

## 4. Admin — danh mục và tài khoản

| Mã | Thao tác | Kết quả mong đợi | Kết quả thực tế | Đánh giá | Nguồn |
| --- | --- | --- | --- | --- | --- |
| Q-A01 | Tạo Danh mục kiểm thử Quân | Danh mục mới xuất hiện | Người kiểm thử xác nhận tạo thành công | Đạt | XN |
| Q-A02 | Đổi tên thành Danh mục kiểm thử Quân đã sửa, tải lại | Giữ tên mới | Người kiểm thử xác nhận tên mới được lưu | Đạt | XN |
| Q-A03 | Xóa danh mục kiểm thử chưa được sử dụng | Danh mục biến mất sau khi tải lại | Người kiểm thử xác nhận xóa thành công | Đạt | XN |
| Q-A04 | Xóa danh mục đang có khóa học sử dụng | Từ chối xóa | Hiển thị “Danh mục đang được sử dụng.”; người kiểm thử xác nhận bước đạt | Đạt | A1, XN |
| Q-A05 | Khóa Student2, tải lại trang Student trong phiên đang mở | Phiên cũ không được vào khu vực học viên | Báo “Phiên đăng nhập không hợp lệ hoặc tài khoản đã bị khóa.”; không hiện Dashboard | Đạt | A2 |
| Q-A06 | Đăng nhập Student2 khi tài khoản đang bị khóa | Từ chối đăng nhập | Hiển thị “Tài khoản đã bị khóa.” | Đạt | A3 |
| Q-A07 | Mở khóa Student2 rồi đăng nhập lại | Đăng nhập được, bảo toàn kết quả | Đăng nhập thành công, Dashboard còn 1 khóa đã hoàn thành; người kiểm thử xác nhận tiến độ 100% và điểm tốt nhất 80% | Đạt | A4, XN |

## 5. Lưu trữ và xuất bản lại khóa học

| Mã | Thao tác | Kết quả mong đợi | Kết quả thực tế | Đánh giá | Nguồn |
| --- | --- | --- | --- | --- | --- |
| Q-A08 | Admin lưu trữ khóa ID 4 đang published | Chuyển sang archived | Người kiểm thử xác nhận trạng thái Đã lưu trữ | Đạt | XN |
| Q-A09 | Tìm khóa ID 4 trong danh sách công khai sau lưu trữ | Khóa không xuất hiện | Người kiểm thử xác nhận không còn thấy khóa trong danh sách | Đạt | XN |
| Q-A10 | Student1 mở đường dẫn giới thiệu đã lưu của khóa archived | Không thể ghi danh mới | Người kiểm thử xác nhận không thể ghi danh mới qua đường dẫn cũ | Đạt | XN |
| Q-A11 | Student2 đã ghi danh mở lại bài học và quiz của khóa archived | Tiếp tục học, giữ kết quả | Người kiểm thử xác nhận vẫn truy cập được bài học/quiz và giữ tiến độ, điểm, trạng thái hoàn thành | Đạt | XN |
| Q-I10 | Chủ khóa xuất bản lại khóa ID 4 từ archived | Chuyển sang published nếu đủ điều kiện | Người kiểm thử xác nhận trạng thái Đã xuất bản | Đạt | XN |
| Q-I11 | Student1 xem danh sách và trang giới thiệu sau xuất bản lại | Khóa xuất hiện và có lựa chọn ghi danh | Người kiểm thử xác nhận khóa xuất hiện, mở được trang giới thiệu và có lựa chọn ghi danh; chưa bấm ghi danh trong ca này | Đạt giao diện | XN |
| Q-C03 | Student2 xem kết quả sau xuất bản lại | Bảo toàn kết quả học tập | Người kiểm thử xác nhận tiến độ 100%, điểm tốt nhất 80%, đã hoàn thành | Đạt | XN |

## 6. AI/Gemini

| Mã | Thao tác | Kết quả mong đợi | Kết quả thực tế | Đánh giá | Nguồn |
| --- | --- | --- | --- | --- | --- |
| Q-AI01 | Tắt AI/chưa điền key rồi bấm Tóm tắt | Thông báo chưa cấu hình, không làm mất nội dung học | Hiển thị “AI chưa được cấu hình. Học và quiz vẫn hoạt động.”; bài học vẫn hiển thị, tiến độ 100% | Đạt | A5 |
| Q-AI02 | Mở bài học và quiz sau lỗi chưa cấu hình AI | Luồng học và quiz tiếp tục hoạt động | Người kiểm thử xác nhận vẫn truy cập được, kết quả hoàn thành được giữ | Đạt | XN |
| Q-AI03 | Bật AI với key local hợp lệ, dùng Tóm tắt | Trả bản tóm tắt tiếng Việt bám sát bài | Người kiểm thử xác nhận đạt; ảnh A6 minh chứng một phản hồi AI thành công, không phân biệt riêng nút đã dùng | Đạt | XN; A6 minh chứng chung |
| Q-AI04 | Gửi câu hỏi về các bước người học cần thực hiện | Nêu đọc nội dung, đánh dấu hoàn thành và làm quiz | Người kiểm thử xác nhận câu trả lời đúng nội dung; ảnh A6 có phản hồi phù hợp mục tiêu bài học | Đạt | XN; A6 minh chứng chung |
| Q-AI05 | Hỏi học phí chính xác theo bài học không có thông tin học phí | Nói rõ thiếu dữ liệu, không tự nêu con số | Người kiểm thử xác nhận đạt | Đạt | XN |
| Q-AI06 | Gửi câu hỏi trống | Từ chối dữ liệu không hợp lệ | Người kiểm thử xác nhận báo “Câu hỏi cần 1–1.000 ký tự.” | Đạt | XN |
| Q-AI07 | Gửi 6 yêu cầu hợp lệ trong cùng một phút đồng hồ, không có lượt gọi từ cửa sổ khác | 5 lượt đầu trả lời; lượt 6 bị giới hạn tại ứng dụng | Người kiểm thử xác nhận báo “Hết quota AI. Vui lòng thử lại sau.” ở lượt vượt giới hạn | Đạt | XN |
| Q-AI08 | Mở bài học và quiz khi AI bị giới hạn lượt | Học và quiz vẫn sử dụng được | Người kiểm thử xác nhận truy cập bình thường, tiến độ không đổi | Đạt | XN |
| Q-AI09 | Gửi lại 1 yêu cầu khi sang phút tiếp theo | Trả lời trở lại nếu còn hạn mức ngày và provider | Người kiểm thử xác nhận AI hoạt động trở lại | Đạt | XN |

Thông báo “Provider đã hết quota miễn phí.” thuộc hạn mức của Google, khác với giới hạn tại ứng dụng. Q-AI07 xác nhận hành vi trong kịch bản một học viên gọi lần lượt; chưa kiểm tra cạnh tranh nhiều tài khoản.

## 7. Ảnh tham chiếu

Ảnh được trao đổi trong phiên hỗ trợ. Bản Markdown này chưa nhúng hoặc đính kèm ảnh vào repository; có thể bổ sung các ảnh gốc vào `docs/images/qa-quan/` trước khi nộp minh chứng.

| Mã ảnh | Tên ảnh gốc | Nội dung quan sát được |
| --- | --- | --- |
| A1 | `image(20261015-164650).png` | Admin nhận thông báo danh mục đang được sử dụng |
| A2 | `image(20261015-165057).png` | Trang Student chặn phiên không hợp lệ/tài khoản bị khóa |
| A3 | `image(20261015-165120).png` | Đăng nhập bị từ chối vì tài khoản đã bị khóa |
| A4 | `image(20261015-165146).png` | Student2 đăng nhập thành công sau mở khóa; còn 1 khóa đã hoàn thành |
| A5 | `image(20261015-173307).png` | Chưa cấu hình AI; bài học hiển thị và tiến độ 100% |
| A6 | `image(20261015-173600).png` | Khung AI hiển thị phản hồi tiếng Việt đúng nội dung mục tiêu bài học |

Các thông báo được đối chiếu ở mức giao diện; chưa thu thập mã HTTP hoặc log request làm bằng chứng cho những ca này.

## 8. Phạm vi cần kiểm tra bổ sung

Các ca trong báo cáo xác nhận những luồng đã thao tác, chưa đại diện cho nghiệm thu toàn bộ dự án. Các mục sau chưa được xác nhận trong đợt kiểm thử này:

- Gửi POST trực tiếp để vượt khóa cấu trúc/quiz sau khi có enrollment; giả mạo ID hoặc role trong yêu cầu ghi dữ liệu.
- Thêm, sửa, xóa và đổi thứ tự đầy đủ các chương, bài học và câu hỏi ở các trạng thái cho phép.
- Các biên quiz 5–20 câu, dữ liệu lựa chọn không hợp lệ, CSRF và yêu cầu đồng thời.
- Khóa/mở tài khoản mà phiên cũ chưa tải lại trong khoảng bị khóa: xác nhận phiên đó không tự dùng lại sau khi tài khoản được mở khóa.
- AI với câu hỏi vượt 1.000 ký tự ở phía server, API key sai, timeout, mất mạng, giới hạn 20 lượt/ngày/người và 100 lượt/ngày/toàn ứng dụng.
- Giới hạn AI toàn ứng dụng khi nhiều tài khoản gọi đồng thời; khả năng chống chèn chỉ dẫn trong nội dung bài học.
- Kiểm thử hồi quy đầy đủ trên bản main sau khi merge và trên môi trường khác.

## 9. Thay đổi đi kèm đợt kiểm thử

- `config.example.php`: đổi model mặc định từ `gemini-2.5-flash-lite` sang `gemini-3.5-flash-lite`; giữ `ai_enabled=false` và `gemini_api_key=''`.
- `docs/QA_Quan.md`: lưu kết quả kiểm thử thủ công, nguồn bằng chứng và phạm vi còn cần kiểm tra.
- Theo kết quả `git status` trên máy kiểm thử, lần cập nhật này chưa có thay đổi mã xử lý Instructor, Admin hoặc AI. Báo cáo không khẳng định đã sửa lỗi mã nguồn trong nhánh này.
