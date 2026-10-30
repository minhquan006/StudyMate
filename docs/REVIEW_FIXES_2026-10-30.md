# Xử lý góp ý PR #14 — 30/10/2026

Nền: `593141fa2a021d7f1744dc2e7e20fe16c31423ad`. Các file sửa hiện **chưa commit, chưa push**. Công cụ thực hiện sửa và kiểm tra cloud; Tú tự xem diff, commit và push. Manifest SHA256 của mã đã chạy: [source-manifest.json](evidence/review-2026-10-30/source-manifest.json).

## Những mục đã sửa

1. `searchQuery()` dùng chung cho Public/Student/Admin, giới hạn 100 ký tự Unicode sau trim, kiểm UTF-8 hợp lệ trước truy vấn. Cả ba tab Admin và form Student có `maxlength=100`. Không cần mbstring để cắt chuỗi. Escape `%`, `_`, `!` giữ nguyên.
2. `docker/start.sh` chờ MySQL bằng PDO/SELECT 1 trước setup, sau đó exec Apache. `DB_WAIT_TIMEOUT` mặc định 60 giây, nhận 1–300; lỗi mạng thử lại, sai tài khoản/cấu hình dừng ngay, không in mật khẩu. Kết nối readiness không chỉ định dbname để setup lần đầu vẫn có thể tạo DB. Không thêm reset/seed vào bước chờ.
3. Compose và config mẫu/Docker dùng `GEMINI_MODEL`; nếu chưa đặt thì nhận `AI_MODEL` cũ, cuối cùng mới dùng mặc định dự phòng. Không chọn một tên “3.5” chưa có định danh model đầy đủ để thay vào code. Tên model dùng được với key thật cần Quân xác nhận. Probe hiện ghi `provider_http_status`, kể cả 404.
4. Đồng bộ README/Readme, nhánh #14 và `BUILDX_CONFIG`; cập nhật hướng dẫn/Tài liệu SRS và Specification. Báo cáo/bằng chứng cũ giữ nguyên nguồn/SHA.

## Kiểm tra đã chạy

DB mới riêng `studymate_review_fixes_test`, Docker MySQL 8.4.13 / PHP 8.3.36. Không dùng volume/DB đang lưu tiến độ của Tú.

| Kiểm tra | Kết quả |
|---|---|
| Tái hiện trên mã gốc #14 | 33 ký tự tìm thấy; hồi quy thất bại ở 34 ký tự, đúng lỗi review |
| Tìm kiếm UTF-8/render/SQL, fixture rollback | 58 đạt: Public 14, Student 14, Admin 30 |
| Request HTTP tìm kiếm | 13 đạt; query đúng trả 200 và có kết quả, query sai dạng/UTF-8 trả 422 |
| Khởi động lại khi DB dừng rồi bật DB | App chờ và tự phục hồi, không cần restart app lần hai |
| Snapshot dữ liệu | Hash từng bảng trong cả 12 bảng giữ nguyên qua restart/setup lặp |
| Timeout và cấu hình khởi động | 13 checks đạt; DB không có kết nối dừng sau khoảng 3 giây với timeout=3; sai mật khẩu dừng ngay |
| Cấu hình model | 8 PHP + 4 Compose đạt, gồm tên cũ, tên mới và ưu tiên khi đặt cả hai |
| MySQL service / AI offline | Chạy lại 14/14 và 24/24 đạt; không có request Gemini thật |
| Docker build và cú pháp | Build đầy đủ image đạt; PHP lint, `sh -n docker/start.sh`, Compose parse, `git diff --check` đạt |
| Không dùng hàm mbstring | Giới hạn 100 ký tự vẫn đúng khi disable `mb_strlen,mb_substr` |

Log/JSON trong [evidence/review-2026-10-30](evidence/review-2026-10-30/summary.json). Các hash bảng chỉ mô tả DB test này, không thay hash backup/restore Windows của Tú. Kiểm khởi động sử dụng source/entrypoint mới bind mount vào image PHP có sẵn. Build đầy đủ image từ Dockerfile cũng đã đạt riêng, log `docker-build.txt`. Không chạy lại toàn bộ 84 HTTP/235 browser chỉ để gắn SHA mới.

## Bàn giao cho nhóm

- Tú cập nhật **cùng nhánh** `tu/nghiem-thu-thu-cong`; PR #14 tự cập nhật. Không tạo thêm PR để thay #14.
- Theo xác nhận của Quân, Tú đóng #13 với ghi chú “Được thay thế bởi #14”, giữ lịch sử và bằng chứng nguyên trạng. Không merge #13.
- An nhận bản sửa nền, kiểm lại ca tìm kiếm/khởi động bị ảnh hưởng trên #15. Đợi #14 review và merge xong rồi đổi base #15 sang `main`; kiểm diff chỉ còn phần của An.
- Quân cung cấp đúng định danh model đã probe với key của nhóm và nghiệm thu Gemini trên source tích hợp. Không có credential tại môi trường kiểm này nên chưa nghiệm thu provider thật.
- Quy tắc rubric/hạn nộp chính thức vẫn chờ xác nhận của giảng viên theo thông tin Quân; không coi mốc dự kiến 10/11 là hạn chính thức.

Kết quả mới chứng minh các ca vừa chạy trên mã có fingerprint nêu trên. Sau khi Tú commit, ghi thêm SHA thật và liên kết manifest; không đổi SHA gốc trong bằng chứng 27/10.
