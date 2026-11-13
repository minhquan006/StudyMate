# Kiểm thử độc lập PR #15 — hỗ trợ An

## Phiên bản và phạm vi

- An: `f8565fb24b0557b5e5075a235ffc4cb5762a203b`.
- Tú: `1a27ec1bc939b7b98324dc6f95ec7aaa175f484a`.
- Bản kiểm thử: merge-tree hai commit trên, thay ba file bằng resolved/ trong ZIP StudyMate_PR15_Gui_An_2026-10-31.zip. Không tạo merge commit.
- Hai conflict được tái hiện: README.md và app/management_views.php. Readme.txt đồng bộ README.md.
- Môi trường thực chạy: Linux cloud, Docker, PHP 8.3.36, MySQL 8.4.13, Chromium 154.0.8037.57. Project/DB/volume test riêng.
- Đây là kết quả công cụ chạy trong cloud, không phải An tự chạy trên máy Windows hoặc xác nhận review của Quân/Tú.

## Kết quả

| Kiểm tra | Kết quả |
|---|---|
| Dockerfile và Compose của bản tích hợp | Build thành công; app và MySQL healthy |
| PHP lint | 28 file đạt |
| Service MySQL | 14/14 |
| Phân trang | 8/8 |
| Tìm kiếm Unicode | 58/58: Public 14, Student 14, Admin 30 |
| Cấu hình model | 8/8 |
| Hợp đồng AI offline | 24/24; không gọi Gemini thật |
| Browser | 54 lượt màn hình: 18 route × 3 chiều rộng 390/768/1366; cao 900; 0 audit failures |
| Browser chức năng | Đăng nhập 3 role, phân trang/clamp, tổng toàn bộ, giữ bộ lọc và empty state đạt các assertion trong runner |
| Bàn phím ở ba bảng Admin | Tab tới vùng bảng; focus hiển thị; role/label và cuộn ngang bằng ArrowRight đạt, viewport 390×844 |
| Fingerprint image | 309 file COPY vào image khớp source; storage/.gitkeep bị loại theo .dockerignore, không tính vào đối chiếu |
| Setup lặp và restart | Health OK; hash đủ 12 bảng không đổi |

## Cách chạy browser

Dùng adapter ngoài source từ tests/ui-audit.mjs: chọn Chromium có sẵn, thêm --no-sandbox cho môi trường container, đổi nhãn browser thành Chromium và ghi nguồn ứng viên chưa commit thay cho git HEAD. Giữ các assertion chức năng/layout của runner; lưu screenshot Chromium. Không tuyên bố đã chạy Chrome hoặc Edge trong lượt này.

## Giới hạn và việc tiếp theo

- Chưa gọi Gemini thật; AI offline không nghiệm thu FR10.
- Chưa test Chrome/Edge Windows hoặc trực tiếp truy cập Docker trên máy An.
- 54 lượt màn hình không phải 54 ca nghiệp vụ độc lập; kiểm tra tự động không thay nghiệm thu trực quan cuối của nhóm.
- Chưa tạo commit, push, comment hoặc merge PR. Checkout /workspace/StudyMate giữ nguyên.
- An review staged diff và tự commit bản merge trên máy mình. Khi commit có SHA mới, gắn kết quả này với nguồn candidate nêu trên; không sửa nhãn thành test đã chạy trên SHA mới.
- Review của nhóm, A4 độc lập do người thật thực hiện, Gemini, ảnh có thanh địa chỉ và video cuối vẫn theo checklist chung.

## Hash ba file đã giải conflict

- `README.md`: `54583eb3d985941dcf3c29073d67736f0f5da4c3039328e37bbe869b927cd663`
- `Readme.txt`: `54583eb3d985941dcf3c29073d67736f0f5da4c3039328e37bbe869b927cd663`
- `app/management_views.php`: `904721ec7f447e8297c55bae79bd02e4498fde380d6212701a608935c9f8f994`

Thời điểm hoàn tất: 2026-10-31T22:54:12.430102+07:00
