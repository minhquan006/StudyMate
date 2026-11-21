# Sửa nút đóng Góc tập trung bị header che trên tablet

Phản hồi của Quân: [comment PR #26](https://github.com/minhquan006/StudyMate/pull/26#issuecomment-7063587910), kiểm tại `c050b352f6a2f5053cb5f5c0a2e72b0942d3d1ab`, nền #22 `7f37492`.

## Lỗi đã tái hiện

Đăng nhập Student, mở bài trong khóa đã tham gia, bật Góc tập trung, đặt viewport 834×900 hoặc 991×900 rồi cuộn trang xuống 650px. Quy tắc tablet ghi đè `top:20px` và `max-height:calc(100vh - 40px)` đưa nút đóng lên y=27–71 trong khi header #22 ở y=16–86. `elementFromPoint` trúng header; click bằng tọa độ giữa nút mở menu chính, không đóng Góc tập trung.

Đã tái hiện trên Windows Chrome 157.0.8103.80 và Edge 157.0.4341.60: 4 tổ hợp trình duyệt/kích thước, 9 xác nhận tái hiện, không lỗi JavaScript. Đây là bằng chứng của lỗi cũ, không phải kết quả đạt của bản sửa.

## Thay đổi

Chỉ bỏ hai ghi đè sticky trong breakpoint `max-width:991px` của `public/assets/study-space.css`. Sidebar tablet kế thừa `top:100px` và `max-height:calc(100vh - 120px)` đã có ở quy tắc cơ sở. Với viewport cao 900px, sidebar ở y=100–880, nút đóng ở y=107–151, dưới header 21px.

Giữ nguyên quy tắc `position:static`/không giới hạn chiều cao của điện thoại dưới 768px và bố cục desktop từ 992px. Không sửa CSS/header của #22, JavaScript tiện ích, ghi chú, quiz, handler, CSRF hoặc DB.

## Kiểm hồi quy mới

`tests/study-space-sticky.cjs` kiểm bằng `elementFromPoint` và **click chuột vào tọa độ thật**, không dùng auto-scroll/auto-wait của `locator.click` để né vùng header che. Kiểm nút đóng thực sự đóng tiện ích, menu chính vẫn đóng, focus trả về nút bật, sidebar/chiều cao còn dùng được và không tràn ngang.

- Chrome và Edge trên Windows; SQLite QA riêng đã có dữ liệu học, không dùng DB demo.
- Việt/Anh × sáng/tối × rộng 390/767/768/834/991/992/1366, cao 900px: **56 tổ hợp, 393 xác nhận đạt**, không lỗi JavaScript.
- Kiểm tablet/desktop sau khi cuộn 650px; điện thoại kiểm khi nút đóng được cuộn vào vùng nhìn thấy.
- Đã đối chiếu ảnh trước/sau ở 834px và 991px. Node syntax và `git diff --check` đạt.

Đây là bộ hồi quy riêng cho sửa sticky. Các số kiểm tích hợp/ghi chú/Buddy/MySQL của lượt trước vẫn thuộc SHA tương ứng, không được tính là chạy lại tại head mới. Lượt này không chạy macOS Chrome 158, MySQL/Compose, Gemini thật hoặc điện thoại thật.

## Bằng chứng

[Kết quả, hash nguồn và checksum](evidence/an-utilities-sticky-2026-11-21/).

| Viewport | Trước (`c050b35`) | Sau sửa |
| --- | --- | --- |
| 834×900 | [Ảnh trước](evidence/an-utilities-sticky-2026-11-21/before-834.png) | [Ảnh sau](evidence/an-utilities-sticky-2026-11-21/after-834.png) |
| 991×900 | [Ảnh trước](evidence/an-utilities-sticky-2026-11-21/before-991.png) | [Ảnh sau](evidence/an-utilities-sticky-2026-11-21/after-991.png) |

Chạy lại trên app QA loopback có dữ liệu seed, không chạy setup/reset DB demo:

```powershell
$env:QA_ALLOW_WRITES='1'
$env:QA_PLAYWRIGHT_MODULE='<duong-dan-playwright-hoac-playwright-core>'
$env:QA_BASE_URL='http://127.0.0.1:<cong-QA>'
$env:QA_BROWSERS='[{"name":"chrome","path":"C:/Program Files/Google/Chrome/Application/chrome.exe"},{"name":"edge","path":"C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe"}]'
$env:QA_OUTPUT='storage/sticky-qa'
$env:QA_EXPECT_BROKEN='0'
node tests/study-space-sticky.cjs
```

Để tái hiện lỗi trên bản sao `c050b35`, dùng cùng runner với `QA_EXPECT_BROKEN=1`; `QA_CSS_PATH` có thể chỉ tệp CSS của bản sao cũ để ghi đúng hash. Không đổi base PR #26 trước khi #22 được review/merge; sau đó đối chiếu lại diff trên main mới theo đề nghị của Quân.
