## Mục đích

Bàn giao bản nghiệm thu Docker/MySQL qua PR #14, nhánh `tu/nghiem-thu-thu-cong` vào `main`. PR này thay #13. Tú tự thực hiện commit/push; mã nguồn và kiểm tra cloud có AI hỗ trợ.

## Sửa theo review của Quân ngày 30/10/2026

- Tìm kiếm Public/Student và ba tab Admin dùng chung giới hạn 100 ký tự UTF-8; không cắt giữa byte tiếng Việt. Thêm hồi quy 33/34, 100/101 ký tự, phân trang, emoji, wildcard và query lỗi.
- Chờ kết nối MySQL có thời hạn trước setup trên cả lần chạy đầu và restart; không reset dữ liệu. Hết thời gian chờ thì dừng với mã lỗi; sai mật khẩu bị từ chối ngay.
- Thống nhất `GEMINI_MODEL`, giữ tương thích `AI_MODEL` cũ. Model thực tế phải được Quân probe với key thật; không mặc định coi `gemini-2.5-flash-lite` còn khả dụng. Probe ghi cả mã HTTP provider để phân biệt 404 với lỗi ứng dụng.
- Đồng bộ README/Readme, nhánh bàn giao, `BUILDX_CONFIG`, SRS/Specification và hướng dẫn Tú.

## Kiểm tra mới trên mã sửa chưa commit

58 kiểm tra tìm kiếm, 13 kiểm tra HTTP tìm kiếm, 13 kiểm tra khởi động/dữ liệu, 8 kiểm tra cấu hình PHP và 4 trường hợp cấu hình Compose đều đạt. Chạy lại 14 service checks MySQL và 24 kiểm tra AI offline đều đạt. PHP lint, shell syntax và `git diff --check` đạt.

Nguồn: `docs/evidence/review-2026-10-30/summary.json` và `source-manifest.json`, nền `593141f`. Môi trường cloud/DB test riêng; không phải kiểm trên Windows của Tú. Build image Docker từ Dockerfile đã đạt. Các ca restart chạy source/entrypoint mới bằng bind mount trên image PHP có sẵn.

Bằng chứng 27/10 (84 HTTP, 235 kiểm tra mỗi Chrome/Edge, hiệu năng, backup/restore và kết quả Windows do Tú cung cấp) giữ nguyên nguồn/SHA tại `docs/evidence/final/`. Không ghi lại thành kết quả vừa chạy trên commit mới.

## Còn cần trước khi chốt

- Tú tự kiểm diff, commit/push và bổ sung SHA thật của commit mới khi báo nhóm.
- Quân/An review; chỉ merge sau khi đủ điều kiện nhánh `main`.
- An nhận sửa nền vào #15, kiểm lại phần bị ảnh hưởng; sau khi #14 merge thì đổi base #15 sang `main` và kiểm diff.
- Quân chốt tên model/key và kiểm Gemini thật trên bản tích hợp, gồm ask/summarize, lỗi/quota. Kiểm tra offline không thay FR10.
- Nhóm nghiệm thu chung trên SHA cuối, video có cả ba người, xác nhận rubric/hạn nộp chính thức và kiểm gói nộp.
