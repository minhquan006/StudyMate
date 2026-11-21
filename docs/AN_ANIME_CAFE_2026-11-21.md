# Anime Café — 21/11/2026

Runtime `b301d99a981250f31dbf0afd4105bd1ca4a1a420` của nhánh `codex/an-study-space-20261121`, bổ sung cho PR #21. Các báo cáo trước giữ nguyên nguồn/SHA; đây là lượt kiểm riêng phần café.

## Đã thêm

- Lựa chọn **Anime Café** thứ tư, bốn thẻ chia hai cột. Lựa chọn được lưu theo tài khoản/trình duyệt bằng schema localStorage hiện có.
- Video `public/assets/spaces/anime-cafe.mp4`: 1920 × 1080, H.264, 25 fps, 10 giây/250 frame, lặp, muted/playsinline; 5.50 MB. Không có audio stream.
- Tạo bản web bằng PyAV/libx264 CRF 24, preset medium, MP4 faststart từ tệp người dùng cung cấp: 3840 × 2160, H.264/AAC, 10 giây, 50.03 MB. File gốc trong Downloads được giữ nguyên. Không gọi bản web là 4K.
- Ảnh JPEG từ frame đầu làm thumbnail và nền dự phòng. CSS thêm mưa trên vùng cửa kính bên trái; clip gốc là cảnh quán có nắng, hiệu ứng mưa không được ghi sẵn trong video.
- Tiếng mưa dùng kênh âm nền hiện có. Jazz Lofi do người dùng dán link trong tab Nhạc; bản này chưa kèm một bản nhạc Jazz cụ thể. Chọn cảnh không tự phát tiếng mưa hoặc nhạc.
- Đổi rừng/café thay đúng source và poster; chuyển sang cảnh tĩnh/tắt chuyển động/đóng công cụ giải phóng source. Giảm chuyển động, tiết kiệm dữ liệu, tab ẩn và ảnh dự phòng khi lỗi tiếp tục được hỗ trợ.
- Không đổi schema DB, seed, ID, header, handler, CSRF, form hoặc chấm điểm. Phần cập nhật video chỉ thuộc công cụ học trên trình duyệt.

## Kiểm mới

- **55 kiểm tra Anime Café đạt** trên Chrome 157.0.8103.80 / Edge 157.0.4341.60: giải mã video thật, đủ 10 giây, tự qua cuối clip rồi lặp tiếp; đổi rừng/café/tĩnh và đổi nhanh; lưu qua reload; mưa độc lập; không autoplay audio; giảm chuyển động; giả lập tải video thất bại và giữ đúng ảnh café.
- Kiểm 390/768/1366 px trên hai trình duyệt: không tràn ngang, bốn thẻ thực sự thành hai cột. PNG ghi lại một khung hình, không đại diện video chuyển động.
- **158 kiểm tra hồi quy công cụ học đạt**, 0 JavaScript page error. YouTube official sample thật vào trạng thái Đang phát trên cả Chrome/Edge; cùng iframe được giữ khi cuộn/đổi tab công cụ/đổi bên. Đồng hồ, âm nền, favorites/storage, native form và no-JavaScript vẫn được kiểm bằng runner cũ.
- PHP lint của tệp sửa, `node --check public/assets/study-space.js` và `git diff --check` đạt.

Giới hạn: SQLite QA riêng trên Windows, chưa chạy lại MySQL/Docker/Gemini thật hoặc kiểm điện thoại vật lý/Safari. Kiểm mưa và media tự động không thay đánh giá âm thanh bằng tai nghe. Lượt chuẩn bị runner café điều chỉnh kiểm số cột sang vị trí thẻ thật vì native fieldset có cách trả computed grid khác; source sản phẩm không đổi theo điều kiện này.

## Dùng thử

Trong màn hình học: Không gian học tập → Không gian → **Anime Café** → bật Chuyển động nền. Tab Âm thanh → Mưa → Bật âm nền. Tab Nhạc → dán link Jazz Lofi yêu thích → Mở nhạc → Phát. Âm lượng mưa và nhạc chỉnh riêng; video nền luôn không tiếng.

Nguồn media do người dùng gửi trực tiếp; chưa có tác giả/giấy phép bên ngoài kèm tệp. [Ghi nguồn](../public/assets/spaces/SOURCES.md), [metadata và hash video](evidence/an-anime-cafe-2026-11-21/media.json).

- [Laptop](evidence/an-anime-cafe-2026-11-21/cafe-1366.png) · [Tablet](evidence/an-anime-cafe-2026-11-21/cafe-768.png) · [Điện thoại](evidence/an-anime-cafe-2026-11-21/cafe-390.png).
- [55 ca mới](evidence/an-anime-cafe-2026-11-21/cafe-results.json) · [158 ca hồi quy](evidence/an-anime-cafe-2026-11-21/regression/results.json).
- [Source hashes](evidence/an-anime-cafe-2026-11-21/summary.json) · [Artifact checksums](evidence/an-anime-cafe-2026-11-21/checksums.json).
