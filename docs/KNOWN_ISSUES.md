# Giới hạn và bước chốt nghiệm thu StudyMate

Cập nhật 01/11/2026 theo [báo cáo tích hợp Quân](QUAN_INTEGRATION_2026-11-01.md). Báo cáo 27/10 và 30/10 là lịch sử, giữ nguyên nguồn và SHA.

| Mã | Phạm vi | Trạng thái hiện tại | Bước chốt |
|---|---|---|---|
| G01 | Gemini thật | Đã có HTTP/MySQL/Chrome hỏi và tóm tắt, thiếu dữ liệu, injection mẫu và archived; key giả 503 kiểm riêng | Tú review mã/chất lượng mẫu; cả nhóm nghiệm thu SHA cuối |
| G02 | Phân trang và hiệu năng | Đã nhận phân trang Student/Instructor/Admin/roster và aggregate của Tú; bằng chứng performance cũ ở docs/evidence/final | Tú đối chiếu phần bị thay đổi; không gọi lượt cũ là benchmark mới |
| G03 | Yêu cầu môn và LMS | FinalProject chỉ tham khảo; khoảng 10/11 chưa là hạn LMS được xác minh | Quân xác nhận rubric/ngày/kênh nộp, đăng ký đề tài và Submitted |
| G04 | Ảnh giao diện | Có ảnh Chrome tự động; chúng không có browser toolbar | An chụp thanh địa chỉ thật và tạo PDF giao diện theo yêu cầu lab |
| G05 | Review và Git | Source tích hợp chưa commit; chưa có chữ ký review của Tú/An hoặc SHA cuối | Review, Quân tự commit, nhóm merge rồi nghiệm thu chung |
| G06 | Đồng thời và quota | Có bằng chứng của Tú; suite Quân kiểm 12 tiến trình tranh 5 quota và atomic rejection | Không suy thành kiểm thử tải/phân tán; Tú chốt hồi quy cần thiết |
| G07 | Browser | Lượt mới Chrome 18 màn hình phần Quân; Chrome/Edge của An giữ mốc cũ | An review trực quan/keyboard và phần cần kiểm lại trên máy nhóm |
| G08 | Model và lỗi provider | Profile 3.5 đã gọi thật; fallback PHP 2.5 giữ tương thích; provider quota/timeout thật chưa cố gây lỗi | Đặt GEMINI_MODEL tường minh; giữ mock và live riêng, không đổi billing |
| G09 | Video và hồ sơ | Có kịch bản và footage diễn tập của An | Cả ba trình bày thật, hoàn thiện hồ sơ theo yêu cầu đã chốt |

Lượt live đầu dừng ở ca đổi key tại chỗ; ca key giả khởi động trong project mới đã đạt 503. Báo cáo giữ nguyên cả hai lượt và không khẳng định nguyên nhân chi tiết khi chưa đủ dữ liệu. Mọi test ghi dữ liệu dùng DB/container riêng, không reset DB phát triển.
