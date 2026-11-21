# Cập nhật nhạc nổi, icon âm thanh và nền chuyển động — 21/11/2026

Nhánh `codex/an-study-space-20261121`, dựa trên giao diện PR #20. Đây là bản tiếp theo của [prototype Không gian học tập](AN_STUDY_SPACE_PROTOTYPE_2026-11-21.md). Runtime mới ở `9474b36`; thay đổi chính ở `e30d9af`, sau đó bổ sung nút đổi bên trình phát.

## Hành vi mới

- **Nhạc tiếp tục khi cuộn và khi đổi công cụ.** Trình phát YouTube ở một bảng nổi luôn hiển thị, thay vì nằm trong tab/cuộn của sidebar. Shell được chuyển ra body trước khi tạo iframe; iframe đang phát giữ nguyên trong suốt quá trình cuộn/đổi tab công cụ, không được chuyển parent hoặc tạo lại.
- Bảng nổi có Phát/Tạm dừng, đóng và nút **↔** chuyển trái/phải để tránh che nội dung hoặc thanh âm lượng. Video vẫn hiển thị ít nhất 200 × 200 px; không có lớp phủ lên vùng player. Khi chiều cao viewport nhỏ, các nút bổ sung thu lại và điều khiển native của YouTube vẫn có sẵn.
- Chuyển sang tab trình duyệt khác vẫn tạm dừng YouTube. Đóng không gian hoặc đóng trình phát dừng video. Chuyển bài qua navigation PHP cần bật lại âm thanh; timer và tùy chỉnh vẫn được giữ.
- **Icon riêng cho bốn kênh âm thanh:** mưa, gió, tiếng ồn hồng, tiếng ồn nâu. Các icon SVG là trang trí; checkbox/label native giữ tên truy cập. Có phần trăm âm lượng, màu trạng thái và vạch chuyển động phản ánh kênh đang phát với gain khác 0.
- **Nền chuyển động:** Trong rừng dùng một clip suối rừng HD **1920 × 1080**, khoảng **11,2 giây**, lặp, không có tiếng. Các cảnh khác có chuyển động khung cảnh/ánh sáng nhẹ. Khi đang phát tiếng mưa, có thêm hiệu ứng mưa nhẹ.
- Có nút bật/tắt Chuyển động nền và lưu lựa chọn. Tôn trọng `prefers-reduced-motion`; chế độ tiết kiệm dữ liệu dùng nền ảnh thay cho video. Khi tắt chuyển động/đổi cảnh/đóng không gian, source video được gỡ để ngừng tải/phát; tab ẩn tạm dừng video nền.

Clip HD tối ưu khoảng **1,61 MB**. Bản này dùng HD để giữ dung lượng vừa phải; chưa có tệp video 4K. Nguồn: Anton Atanasov / [Pexels video 19711802](https://www.pexels.com/video/a-stream-flowing-through-a-forest-in-the-sun-19711802/), [Pexels License](https://www.pexels.com/license/). Phần đầu tệp nguồn được trim/re-encode thành WebM VP9 bằng Chrome Canvas/MediaRecorder. [Nguồn media](../public/assets/spaces/SOURCES.md).

Schema, handler, ID, CSRF, chấm điểm và header không đổi trong lượt cập nhật này. Các thay đổi header để dùng official IFrame Player API thuộc prototype trước. Trình phát nổi giữ video hiển thị theo [yêu cầu player](https://developers.google.com/youtube/terms/required-minimum-functionality).

## Dùng thử

1. Tải lại màn hình học, mở Không gian học tập.
2. Tab Không gian → Trong rừng → bật Chuyển động nền để xem video suối.
3. Tab Âm thanh → chọn kênh, kéo âm lượng, bấm Bật âm nền. Icon/vạch chỉ sáng khi kênh có thể nghe được.
4. Tab Nhạc → dán link → Mở nhạc → Phát. Cuộn đọc bài hoặc đổi sang tab Âm thanh: trình phát nổi vẫn giữ nhạc.
5. Bấm ↔ để đổi bên bảng nổi; bấm × trên bảng nổi để dừng nhạc.

Các tùy chỉnh cũ tiếp tục dùng được. Dữ liệu QA ở bản thử cục bộ được giữ khi cập nhật source.

## Bằng chứng mới

| Bộ kiểm | Kết quả | Source |
| --- | --- | --- |
| Focus/media/YouTube thật Chrome + Edge | **158 kiểm tra đạt**, 0 JavaScript page error | `9474b36` |
| Hồi quy UI và luồng học tại 390/768/1366 px | **652 kiểm tra đạt**, 0 JavaScript page error | `e30d9af` |
| PHP syntax | **34 tệp đạt** | `9474b36` |
| Video nền qua cuối clip và lặp lại | Native video 1920 × 1080, 11,219 giây, sau loop tiếp tục phát tại 0 giây | Cùng tệp media ở `9474b36` |

Bộ focus kiểm actual YouTube playing trên cả Chrome/Edge, giữ cùng iframe khi cuộn điện thoại/đổi tab công cụ, dock nằm trong viewport, đổi bên giữ iframe và đóng mới gỡ player. Đồng thời kiểm video HD có thời gian tăng, tắt chuyển động gỡ source, giảm chuyển động dừng video, icon kênh thật/gain/% âm lượng, timer, favorites/URL/storage và native form như bộ prototype.

Bộ hồi quy hoàn tất trước commit bổ sung nút đổi bên; nút đó được kiểm trong bộ focus mới nhất. Các kết quả form **72 ca** và PHP service/offline **112 kiểm tra** của `2814b3d` là bằng chứng lịch sử trong báo cáo prototype, không được ghi thành lượt chạy mới này.

Giới hạn: SQLite QA trên Windows; chưa chạy lại MySQL/Docker hoặc Gemini thật; chưa kiểm Safari/điện thoại vật lý hoặc nghe đánh giá chất lượng trên loa/tai nghe. Clip nền được kiểm giải mã/phát/lặp thật; ảnh PNG chỉ ghi lại một khung hình.

## Ảnh và log

- [Bảng âm thanh mới](evidence/an-study-space-motion-2026-11-21/preview-sounds.png), [trình phát nổi](evidence/an-study-space-motion-2026-11-21/preview-player.png).
- [Focus/media](evidence/an-study-space-motion-2026-11-21/focus/results.json), [hồi quy](evidence/an-study-space-motion-2026-11-21/regression/results.json), [loop video](evidence/an-study-space-motion-2026-11-21/video-loop.json).
- [Source/manifest](evidence/an-study-space-motion-2026-11-21/summary.json), [checksums](evidence/an-study-space-motion-2026-11-21/checksums.json).

Runner tiếp tục là `tests/study-space.cjs` và `tests/an-ui-demo.cjs`; cách chuẩn bị DB/config/biến môi trường ở báo cáo prototype. Các ca media dùng video mẫu official `M7lc1UVf-VE`; không cần API key hoặc đăng nhập Google.
