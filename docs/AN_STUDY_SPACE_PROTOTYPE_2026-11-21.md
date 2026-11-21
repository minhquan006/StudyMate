> Báo cáo bản thử đầu. Cập nhật hiện tại: [Nhạc nổi, icon âm thanh và nền chuyển động](AN_STUDY_SPACE_MOTION_2026-11-21.md).

# Bản thử Không gian học tập — 21/11/2026

Nhánh `codex/an-study-space-20261121`, dựa trên giao diện của PR #20 tại `56e58f8`. Đây là phần thử riêng để An và nhóm xem trước. Runtime `2814b3d`; `3a476c6` chỉ chuẩn hóa fixture của runner. Các commit bằng chứng sau đó giữ cùng runtime.

## Dùng bản thử

1. Chạy dự án theo README trên nhánh này, đăng nhập Student và mở một khóa đã tham gia.
2. Trong màn hình học, bấm **Không gian học tập**.
3. Chọn **Bên cửa sổ / Trong rừng / Bàn học** ở tab Không gian.
4. Chọn **Tập trung 25 phút / Nghỉ ngắn 5 phút / Đồng hồ bấm giờ**, bấm Bắt đầu, Tạm dừng hoặc Đặt lại.
5. Ở tab **Âm thanh**, chọn các kênh, chỉnh từng âm lượng và bấm Bật âm nền. Có âm lượng chung và nút dừng toàn bộ âm thanh.
6. Ở tab **Nhạc**, dán link HTTPS của một video YouTube; có thể đặt tên gợi nhớ, lưu tối đa năm video yêu thích. Bấm Mở nhạc, sau đó Phát.
7. Bấm **Giao diện thường** hoặc nút đóng để trở lại bố cục bài học. Đóng không gian tạm dừng đồng hồ và dừng media.

Đồng hồ dùng thời gian đã trôi qua, giữ trạng thái khi tải lại/chuyển bài và dừng ở 00:00 khi đếm ngược xong. Âm thanh không tự bật lại sau navigation. Tiến độ học tiếp tục được ghi qua nút Hoàn thành bài, quiz theo quy tắc hiện có; thời gian tập trung chỉ là công cụ cá nhân, không tính điểm hoặc tự hoàn thành bài.

Tùy chỉnh, link yêu thích và trạng thái đồng hồ lưu trong localStorage theo ID tài khoản trên trình duyệt hiện tại. Chưa đồng bộ giữa thiết bị. Dữ liệu lưu sai hoặc bị chặn có xử lý phục hồi; bài học vẫn dùng khi JavaScript tắt, phần công cụ được ẩn.

## Phạm vi mã và tích hợp

- Thêm `app/study_space_view.php`, `public/assets/study-space.js` và CSS riêng. Chỉ tải công cụ tại màn hình học; schema/seed/ID và handler nghiệp vụ giữ nguyên.
- Giữ các form POST của lesson/AI/quiz, CSRF, request ID và `forms.js`. Mục lục được đóng/mở trong chế độ không gian; liên kết Danh sách bài học mở được mục lục.
- Tên yêu thích hiển thị bằng `textContent`. Link chỉ nhận HTTPS và đúng host/path/ID video YouTube; không fetch URL tùy ý ở server, không tải hay tách âm thanh YouTube.
- YouTube dùng [IFrame Player API](https://developers.google.com/youtube/iframe_api_reference), host `youtube-nocookie.com`, trình phát tối thiểu 200 × 200. Trình phát được đóng khi đổi tab công cụ/đóng không gian; tạm dừng khi video ra ngoài vùng nhìn hoặc tab trình duyệt bị ẩn. [Yêu cầu player](https://developers.google.com/youtube/terms/required-minimum-functionality), [Developer Policies](https://developers.google.com/youtube/terms/developer-policies).
- Thay đổi header trong `app/bootstrap.php`: chỉ request trang học cho phép các đường dẫn script chính thức `www.youtube.com/iframe_api`, `www.youtube.com/s/player/`, `s.ytimg.com/yts/jsbin/`, và gửi referrer dạng origin khi qua domain. Các route khác giữ script local và `same-origin`. Các hạn chế form/frame/base và không cho unsafe-eval vẫn được giữ.

Bản này có thay đổi header và công cụ mới, nên cần nhóm review phạm vi trước khi tích hợp bản trình bày. PR #20 tiếp tục là phần giao diện nền riêng.

## Media và hành vi âm thanh

Ba cảnh là ảnh có giấy phép phục vụ local. Tiếng mưa là bản thu public domain của ezwa; gió, tiếng ồn hồng và tiếng ồn nâu được tổng hợp bằng Web Audio, có nhãn phân biệt trong UI. Mỗi kênh có gain riêng, kết hợp âm lượng chung. Không tải âm nền hoặc YouTube trước khi người dùng chủ động bật công cụ tương ứng. Nguồn/giấy phép nằm trong [SOURCES.md](../public/assets/spaces/SOURCES.md).

Video yêu thích được kiểm định dạng khi lưu; khả năng phát thực tế phụ thuộc chủ video, quyền nhúng và mạng. Các lỗi player/mạng có phản hồi trong bảng Nhạc. Nút mở video chỉ nạp trình phát; phát nhạc cần thao tác chủ động.

## Bằng chứng mới

| Bộ kiểm | Kết quả | Mã đã chạy |
| --- | --- | --- |
| Không gian học tập, Chrome/Edge | **132 kiểm tra đạt**, 0 JavaScript page error | Runtime `2814b3d`, harness `3a476c6` |
| UI/luồng học hiện có, 18 màn hình tại 390/768/1366 | **652 kiểm tra đạt**, 0 JavaScript page error | `2814b3d` |
| Form review / hủy rời quiz / form ban đầu | **34 + 16 + 22 = 72 ca đạt** | `2814b3d` |
| Service / pagination / model config / AI offline / UTF-8 ba vai trò | **14 + 8 + 8 + 24 + 58 = 112 kiểm tra đạt** | `2814b3d` |
| PHP syntax | **34 tệp tracked đạt** | `2814b3d` |

Bộ mới kiểm: chọn nền, tab bằng bàn phím, ba viewport, đồng hồ chạy/dừng/reset/hết giờ và giữ thời gian qua POST chuyển bài, audio thật giải mã/phát/dừng/bốn kênh/gain, không autoplay sau reload, URL sai, tên chứa markup, trùng/yêu thích tối đa năm, lưu sau reload, storage lỗi/bị chặn, tách tùy chỉnh giữa Student 1 và Student 2, mục lục và quiz native.

**YouTube thật:** video mẫu chính thức `M7lc1UVf-VE` đã vào trạng thái **Đang phát** trên cả Chrome và Edge, sau thao tác bấm Phát. Đã kiểm tạm dừng khi cuộn video ra ngoài viewport điện thoại và gỡ iframe khi đổi công cụ. Đây là kiểm YouTube, không phải bằng chứng gọi Gemini thật.

**Giới hạn:** SQLite QA trên Windows, chưa chạy MySQL/Docker hoặc Gemini thật. Viewport trình duyệt tự động, chưa kiểm Safari hoặc điện thoại vật lý. Bộ audio quan sát việc giải mã, tín hiệu PCM, phát/dừng và gain; chưa có lượt nghe đánh giá chất lượng trên loa/tai nghe. Tiếng mưa Ogg có thông báo lỗi nếu trình duyệt không hỗ trợ; các âm tổng hợp vẫn dùng được.

Hai vấn đề fixture đã được giữ trong diagnostics: lượt đầu mở quá nhiều phiên đăng nhập khiến rate limiter trả 429; runner cuối dùng lại phiên QA đã xác thực giữa các context. Các lượt lặp sau đó đã đi tới bài cuối nên không còn nút Bài tiếp theo; runner cuối bắt đầu rõ tại lesson 7, kiểm có nút trước khi gửi. Ứng dụng giữ hành vi bài cuối và rate limit hiện có.

## Ảnh và log

- [Bản thử laptop](evidence/an-study-space-2026-11-21/preview-space.png), [bảng âm thanh](evidence/an-study-space-2026-11-21/preview-sounds.png), [điện thoại](evidence/an-study-space-2026-11-21/preview-mobile.png).
- [Nguồn và tóm tắt](evidence/an-study-space-2026-11-21/summary.json), [checksum artifact](evidence/an-study-space-2026-11-21/checksums.json).
- [Focus/YouTube thật](evidence/an-study-space-2026-11-21/focus/results.json), [hồi quy UI](evidence/an-study-space-2026-11-21/regression/results.json), [PHP](evidence/an-study-space-2026-11-21/php-checks.txt).
- [Form review](evidence/an-study-space-2026-11-21/forms/review/results.json), [hủy quiz](evidence/an-study-space-2026-11-21/forms/quiz-exit/results.json), [form ban đầu](evidence/an-study-space-2026-11-21/forms/original/results.json).

Chạy lại `tests/study-space.cjs` với các biến `QA_ALLOW_WRITES=1`, `QA_BASE_URL` loopback, `QA_PLAYWRIGHT_MODULE`, `QA_BROWSERS` và `QA_OUTPUT` như [runner UI](AN_UI_DEMO_2026-11-21.md#chạy-lại-phần-ui). Dùng DB seeded và fixture `tests/run.php` riêng. Bộ mới có thể tạo enrollment Student 2 trên khóa 1; các lượt form phải có DB/quota riêng. Muốn kiểm YouTube thật cần kết nối mạng; các video giả trong ca giới hạn yêu thích chỉ được lưu và không gọi player.
