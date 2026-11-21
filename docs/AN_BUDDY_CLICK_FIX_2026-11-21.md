# Sửa Buddy chặn click sau khi rời ô nhập — 21/11/2026

Runtime `a9fbc229b207626c353cfb83f3eb409084cfd4eb`, dựa trên nhánh bản thử `6a7691b`. Chỉ sửa `public/assets/study-buddy.js`; phần ghi chú đang phát triển không nằm trong PR sửa lỗi này.

## Tái hiện và sửa

Ở màn học, mở Không gian học tập → Nhạc, nhập link YouTube và tên, rồi bấm Lưu yêu thích. Trong bố cục có vị trí nút nằm dưới góc nhân vật, sự kiện mousedown vào nút lưu làm focus đổi từ ô nhập sang nút. Buddy bung ra ngay trong focusin, nên mouseup đi vào ảnh nhân vật; click không đến nút và yêu thích không được lưu.

Trace trước sửa ghi đúng chuỗi `mousedown space-music-save → focusin space-music-save → mouseup buddy-sprite`, favorites = 0. Sau sửa, giữ trạng thái thu gọn qua focus chuyển ra control khác và chỉ bung lại trong document click sau khi control đã xử lý click. Click Buddy vẫn mở bảng bình thường.

Không thêm delay hay sửa form/handler/CSRF. Thay đổi chỉ điều chỉnh thời điểm phục hồi kích thước của nhân vật.

## Kiểm

- 6 kiểm trực tiếp: click thường, giữ chuột 400 ms rồi thả và Enter, trên Chrome/Edge. Cả mouseup và click đều tới `space-music-save`; favorites = 1.
- 158 kiểm hồi quy đạt trên bản nền Buddy chưa có ghi chú. Source fingerprint lấy từ đúng checkout server và khớp commit runtime ở trên.
- Cùng bộ 158 kiểm cũng đạt trên preview tích hợp phần ghi chú đang phát triển. Đây là hai lượt của cùng suite, không cộng thành 316 kiểm độc lập. Các raw fingerprints ghi nhận source tích hợp riêng.
- Cả hai lượt 0 JavaScript page error; YouTube thật mẫu official vào trạng thái đang phát trên cả hai trình duyệt. JS syntax/diff checks đạt.

Giới hạn: SQLite QA riêng, Windows Chrome 157.0.8103.80/Edge 157.0.4341.60, chưa chạy MySQL/Docker/Gemini thật hoặc điện thoại vật lý.

[Trace trước](evidence/an-buddy-focus-2026-11-21/before.json) · [Trace sau và 6 kiểm](evidence/an-buddy-focus-2026-11-21/after.json) · [Ảnh trước](evidence/an-buddy-focus-2026-11-21/before.png) · [Ảnh sau](evidence/an-buddy-focus-2026-11-21/after.png).

[Hồi quy bản nền](evidence/an-buddy-focus-2026-11-21/base-regression/results.json) · [Hồi quy tích hợp ghi chú](evidence/an-buddy-focus-2026-11-21/regression/results.json) · [Summary](evidence/an-buddy-focus-2026-11-21/summary.json) · [Checksums](evidence/an-buddy-focus-2026-11-21/checksums.json).
