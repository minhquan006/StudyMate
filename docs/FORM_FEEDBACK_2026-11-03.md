# Bổ sung phản hồi form — 03/11/2026

## Nguồn và phạm vi

Thực hiện bởi Codex theo yêu cầu An. Nền đối chiếu là commit `d21f08e95585286df1a77be4815e3d44d120ee5b`, nhánh GitHub `feat/an-ui-acceptance`, khớp HEAD Windows An cung cấp. Source được đọc bằng snapshot HTTPS theo SHA; không fetch/pull, không đổi checkout hoặc lịch sử Git của An. Bản vá 48 file trước đó dựa trên main `861e72c` đã lỗi thời đối với nhánh này, không áp dụng lên d21f08e.

Nhánh d21f08e đã có phân trang/aggregate, tìm UTF-8, cấu hình model AI, quyền Apache, menu 44 px, empty state Admin và truy cập bảng bằng bàn phím. Không đưa lại các phần đó hoặc báo cáo lỗi cũ vào bản vá mới.

## Thay đổi bổ sung

- `app/layout.php`: tải `/assets/forms.js` bằng defer, phù hợp CSP self hiện tại.
- `public/assets/forms.js`: trạng thái chờ POST có role=status/aria-busy; chặn submit event lặp; giữ control enabled để named submitter và CSRF không bị mất; reset pending khi pageshow. Quiz đã thay lựa chọn cảnh báo beforeunload, lần nộp hợp lệ không cảnh báo.
- `public/assets/app.css`: chỉ thêm style chờ xử lý, giữ toàn bộ sửa responsive hiện có.
- Form PHP vẫn hoạt động khi tắt JavaScript. Không đổi endpoint, request_id, query, chấm điểm, schema, model AI hoặc quyền.

## Kiểm chứng trên nền mới

Docker Compose/Dockerfile nguyên bản của d21f08e, project riêng `studymate-an-d21-qa`, port 8085, DB/storage riêng; không dùng override quyền của lượt cloud cũ. PHP 8.3.36, MySQL 8.4.13; app healthy. Source được chạy là snapshot d21f08e cộng ba file bổ sung này.

Chrome 156.0.8078.79 và Edge 156.0.4314.58 chính hãng, headless trên Linux. **11 nhóm mỗi browser, tổng 22 nhóm đạt**, không có JavaScript page error. Kết quả và fingerprint file trong [results.json](evidence/form-feedback-2026-11-03/results.json).

Coverage: required validation không bật pending sai; đăng ký/redirect thật; status và style chờ; submit event thứ hai bị chặn; pageshow reset (event tổng hợp trong harness, không gọi đó là kiểm thử bfcache đầy đủ); sáu POST hoàn thành lesson; named AI mode và CSRF được gửi đúng; AI disabled không mất bài; radio bằng bàn phím; beforeunload khi refresh và giữ lựa chọn khi ở lại; nộp quiz có kết quả thật không bị cảnh báo rời trang; logout chặn dashboard; login khi tắt JavaScript.

Harness chủ động ngăn navigation ở ca quan sát pending, không dùng ca đó để kết luận HTTP concurrency. Không chuyển kết quả 84 nhóm/114 viewport trên nền 861e72c sang nền d21f08e. Không chạy lại toàn bộ nghiệp vụ, hiệu năng hoặc Gemini thật trong lượt bổ sung này. Các báo cáo An/Tú/Quân hiện có được giữ nguyên và vẫn có giới hạn theo SHA ghi trong từng báo cáo.

## Chạy lại

Chỉ dùng DB seed dùng một lần: suite tạo tài khoản QA, enrollment, progress và attempt. Node/Playwright chỉ là công cụ kiểm thử ngoài runtime PHP; có thể cài Playwright 1.57.0 ngoài checkout.

```bash
QA_ALLOW_WRITES=1 \
QA_BASE_URL=http://127.0.0.1:8085 \
QA_PLAYWRIGHT_MODULE=/path/to/node_modules/playwright \
QA_BROWSERS='[{"name":"chrome","path":"/path/to/chrome"},{"name":"edge","path":"/path/to/msedge"}]' \
QA_OUTPUT=/tmp/studymate-form-feedback \
node tests/form-feedback.cjs
```

Không commit/push trong đợt này. An tự review và commit; Quân review trước merge theo quy tắc nhóm.


## Bổ sung theo review Quân — 05/11/2026

### Phạm vi, SHA và nguồn bằng chứng

- Thực hiện: Codex hỗ trợ An, **Linux cloud**, không phải An tự kiểm trên Windows.
- Source ứng dụng chạy: PR #16 head `8e05dfb4898470d9929b12b1e58eb046d9fc1a5b`; các file ứng dụng không đổi trong lượt này. Đối chiếu 29 file app/public/database/docker/tools của container với snapshot PR16: khớp.
- PHP 8.3.36, MySQL 8.4.13; project/DB/storage QA riêng. Không gọi Gemini thật; kiểm request của cả hai nút với AI disabled.
- Google Chrome 156.0.8078.79 và Microsoft Edge 156.0.4314.58 chính hãng, headless; OS Linux 6.18.57.
- Chỉ chạy nhóm `QA_CASE_SET=review`, không chạy lại toàn bộ suite vì đổi base. Không chạy lại bộ 22 nhóm ban đầu hoặc suite của #15.
- Kết quả ban đầu vẫn nằm ở các trường gốc trong `results.json`, giữ nguyên ngày/SHA/22 checks. Lượt mới được thêm riêng trong `supplementalReviews`; không chuyển bằng chứng cũ thành lần chạy mới. `sourceHead` là SHA thực sự được kiểm; `baseCommit` trong runner là nền lịch sử d21f08e.
- Thời gian lượt mới (UTC): 2026-11-05T09:28:40.078Z → 2026-11-05T09:29:13.186Z.
- SHA-256 runner bổ sung: a284a81b624f9b6745f4a0aea5bb15a0c812db2bb539767b955aa4885c664526.

### Kết quả bổ sung

**34 ca đạt: 17 Chrome + 17 Edge; 0 JavaScript page error.** Các ca nhấp đúp đếm POST thực tế, không chỉ dispatch submit event tổng hợp.

| Browser | Ca kiểm tra | Mong đợi | Thực tế |
|---|---|---|---|
| chrome | Instructor: thiếu trường bắt buộc | Dữ liệu sai bị native validation chặn; không POST hoặc pending | POST=0; aria-busy không có; status=0 |
| chrome | Instructor: title ngắn hơn minlength | Dữ liệu sai bị native validation chặn; không POST hoặc pending | POST=0; aria-busy không có; status=0 |
| chrome | Instructor: gửi liên tiếp | Nhấp đúp gửi chỉ tạo một POST và một status | POST thực tế=1; status trong submit đầu=1; số submit event quan sát=2 |
| chrome | Instructor: Back và gửi lại | Quay lại form không kẹt pending và gửi hợp lệ được | aria-busy không có; server trả lại title mới |
| chrome | Instructor: lỗi server và phục hồi | Publish chưa hợp lệ hiện lỗi; form tiếp tục gửi được | Có alert, không kẹt aria-busy; lưu title sau lỗi thành công |
| chrome | Admin: thiếu tên danh mục | Dữ liệu sai bị native validation chặn; không POST hoặc pending | POST=0; aria-busy không có; status=0 |
| chrome | Admin: tên danh mục quá ngắn | Dữ liệu sai bị native validation chặn; không POST hoặc pending | POST=0; aria-busy không có; status=0 |
| chrome | Admin: lỗi trùng tên không kẹt pending | Server báo trùng và form mới không pending | Alert đã tồn tại; aria-busy không có |
| chrome | Admin: gửi liên tiếp sau lỗi | Nhấp đúp gửi chỉ tạo một POST và một status | POST thực tế=1; status trong submit đầu=1; số submit event quan sát=2 |
| chrome | Admin: Back và gửi lại | Quay lại không pending, tạo danh mục mới được | Danh mục Back xuất hiện đúng một lần |
| chrome | AI: nút ask | Gửi đúng mode/action/CSRF; lỗi AI không kẹt form | mode=ask, action=ai, CSRF có; bài vẫn hiện; không pending |
| chrome | AI: nút summarize | Gửi đúng mode/action/CSRF; lỗi AI không kẹt form | mode=summarize, action=ai, CSRF có; bài vẫn hiện; không pending |
| chrome | Quiz: rời bằng liên kết và ở lại | Có cảnh báo; ở lại giữ lựa chọn | beforeunload; radio vẫn checked |
| chrome | Quiz: refresh và ở lại | Refresh cảnh báo; ở lại giữ lựa chọn | beforeunload; radio vẫn checked |
| chrome | Quiz: nộp hợp lệ | Nộp đi tới kết quả, không cảnh báo rời nhầm | URL result; dialog=0 |
| chrome | Không JavaScript: instructor | Login và gửi form quản trị/biên soạn vẫn hoạt động | Đã login và xác minh thay đổi server trả lại |
| chrome | Không JavaScript: admin | Login và gửi form quản trị/biên soạn vẫn hoạt động | Đã login và xác minh thay đổi server trả lại |
| edge | Instructor: thiếu trường bắt buộc | Dữ liệu sai bị native validation chặn; không POST hoặc pending | POST=0; aria-busy không có; status=0 |
| edge | Instructor: title ngắn hơn minlength | Dữ liệu sai bị native validation chặn; không POST hoặc pending | POST=0; aria-busy không có; status=0 |
| edge | Instructor: gửi liên tiếp | Nhấp đúp gửi chỉ tạo một POST và một status | POST thực tế=1; status trong submit đầu=1; số submit event quan sát=2 |
| edge | Instructor: Back và gửi lại | Quay lại form không kẹt pending và gửi hợp lệ được | aria-busy không có; server trả lại title mới |
| edge | Instructor: lỗi server và phục hồi | Publish chưa hợp lệ hiện lỗi; form tiếp tục gửi được | Có alert, không kẹt aria-busy; lưu title sau lỗi thành công |
| edge | Admin: thiếu tên danh mục | Dữ liệu sai bị native validation chặn; không POST hoặc pending | POST=0; aria-busy không có; status=0 |
| edge | Admin: tên danh mục quá ngắn | Dữ liệu sai bị native validation chặn; không POST hoặc pending | POST=0; aria-busy không có; status=0 |
| edge | Admin: lỗi trùng tên không kẹt pending | Server báo trùng và form mới không pending | Alert đã tồn tại; aria-busy không có |
| edge | Admin: gửi liên tiếp sau lỗi | Nhấp đúp gửi chỉ tạo một POST và một status | POST thực tế=1; status trong submit đầu=1; số submit event quan sát=2 |
| edge | Admin: Back và gửi lại | Quay lại không pending, tạo danh mục mới được | Danh mục Back xuất hiện đúng một lần |
| edge | AI: nút ask | Gửi đúng mode/action/CSRF; lỗi AI không kẹt form | mode=ask, action=ai, CSRF có; bài vẫn hiện; không pending |
| edge | AI: nút summarize | Gửi đúng mode/action/CSRF; lỗi AI không kẹt form | mode=summarize, action=ai, CSRF có; bài vẫn hiện; không pending |
| edge | Quiz: rời bằng liên kết và ở lại | Có cảnh báo; ở lại giữ lựa chọn | beforeunload; radio vẫn checked |
| edge | Quiz: refresh và ở lại | Refresh cảnh báo; ở lại giữ lựa chọn | beforeunload; radio vẫn checked |
| edge | Quiz: nộp hợp lệ | Nộp đi tới kết quả, không cảnh báo rời nhầm | URL result; dialog=0 |
| edge | Không JavaScript: instructor | Login và gửi form quản trị/biên soạn vẫn hoạt động | Đã login và xác minh thay đổi server trả lại |
| edge | Không JavaScript: admin | Login và gửi form quản trị/biên soạn vẫn hoạt động | Đã login và xác minh thay đổi server trả lại |

### Giới hạn và cách chạy lại

- Instructor và Admin: kiểm native required/minlength, phục hồi sau lỗi server, nhấp đúp chỉ một POST, quay lại gửi được và form không JavaScript. Ca Admin Back dùng lịch sử GET; không coi browser đòi phát lại POST 422 là lỗi pending. Ca Admin không JavaScript gửi bằng phím Enter; không tuyên bố đã nghiệm thu mọi thao tác chuột/khả năng sử dụng trên Windows.
- Trong giai đoạn viết harness, giữ response bằng route interception khiến Playwright chờ navigation; harness cuối dùng nhấp đúp thật và quan sát submit/request. Các lượt chẩn đoán không được tính là nhóm đạt hoặc bằng chứng lỗi ứng dụng.
- Back của ca mới là điều hướng thật nhưng không phải chứng minh toàn bộ cơ chế BFCache; ca pageshow tổng hợp trong bộ cũ vẫn giữ đúng giới hạn cũ.
- Chưa xác nhận kiểm thử thủ công trên Windows, Gemini thật, video, cài độc lập của An hoặc toàn bộ A1–A5. Quân/Tú vẫn cần review trước merge.

Để chỉ chạy phần bổ sung trên DB QA đã seed dùng một lần, thêm `QA_CASE_SET=review` vào lệnh chạy `tests/form-feedback.cjs` ở trên. Có thể đặt `QA_SOURCE_SHA` theo SHA source thực sự đang phục vụ. Không chạy vào DB demo/production cần giữ.

### Base PR và phần đang chờ

Quan sát GitHub ngày 05/11: #15 head d21f08e, base tu/nghiem-thu-thu-cong; #16 head 8e05dfb, base feat/an-ui-acceptance; main 6e64cba đã nhận #14. Diff dự kiến #15 sang main đã được đọc; không sửa lại conflict hoặc bằng chứng nguồn/SHA cũ. API GitHub của cloud trả 403 Forbidden nên thao tác đổi base chưa thực hiện từ cloud.

Đổi #15 sang main trước, báo Quân kiểm diff/review. **Chỉ sau #15 merge mới đổi base #16 sang main**; giữ nhánh nền feat/an-ui-acceptance cho tới lúc đó. Bản cập nhật này chỉ đổi runner, báo cáo và trường bằng chứng bổ sung trong file JSON đã có của #16, nên PR vẫn gồm sáu file và không đổi mã ứng dụng.


## Sửa lỗi hủy logout từ quiz — 06/11/2026

### Lỗi và thay đổi

Theo review `pullrequestreview-5783216138`, đã tái hiện trên mã ứng dụng đúng head `01245b4f4e2902a1769d26623e1ce50ac6547fcd`: chọn đáp án, logout, hủy beforeunload; logout còn pending/status, lần bấm sau bị chặn, số POST bằng 0. Đây là lỗi thật, không phải kết luận từ bộ ca 22/34 cũ.

Bản sửa chỉ thay `public/assets/forms.js` trong phần ứng dụng:

- Khi quiz có lựa chọn chưa nộp và gửi một form khác (logout), dùng xác nhận đồng bộ **trước khi** thêm form vào pending.
- Hủy: preventDefault và trả về ngay, không pending/status, không gửi POST, giữ đáp án.
- Đồng ý: ghi nhận form đã được xác nhận, sau đó mới khóa pending; beforeunload không hỏi thêm lần nữa cho chính form đó.
- Giữ kiểm tra pending trước xác nhận để ngăn gửi đúp. Không disable control nên named submitter/CSRF được giữ.
- pageshow xóa pending và trạng thái form đã xác nhận; không dùng timer mở khóa vô điều kiện.
- Rời quiz bằng link/refresh vẫn dùng cảnh báo beforeunload; nộp quiz hợp lệ vẫn không cảnh báo nhầm. JavaScript tắt thì native PHP forms vẫn chạy.

### Source và môi trường của lượt mới

- Codex kiểm trên Linux cloud, PHP 8.3.36, MySQL 8.4.13, project/DB/storage QA riêng; không phải An tự thao tác Windows.
- Baseline: source ứng dụng tại `01245b4`. Candidate: `01245b4` **cộng sửa forms.js chưa commit**; fingerprint file trong `bugfixReviews[].fixedCandidate.sourceFilesSha256` xác định đúng nội dung đã chạy, không gán SHA mới khi chưa có commit.
- Chrome 156.0.8078.79 và Edge 156.0.4314.58 bản chính hãng, headless. OS: Linux 6.18.57.
- Thời gian UTC: 2026-11-06T04:07:10.622Z → 2026-11-06T04:07:23.216Z.
- Chỉ chạy `QA_CASE_SET=quiz-exit`, không chạy lại toàn bộ suite/project. Kết quả 22 nhóm gốc và 34 ca bổ sung cũ giữ nguyên các trường/nguồn/SHA; lượt mới nằm riêng trong `bugfixReviews`.
- **16 ca đạt, 8 mỗi browser; 0 JavaScript page error.** Baseline trước sửa tái hiện được lỗi trên Chrome, không tính baseline đó thành ca ứng dụng đạt.

| Browser | Ca kiểm tra | Mong đợi | Thực tế |
|---|---|---|---|
| chrome | Logout rồi ở lại không kẹt pending | Giữ đáp án; pending/status trống; chưa gửi POST | Đáp án còn; aria-busy không có; status=0; logout POST=0 |
| chrome | Logout lại bằng Enter vẫn cho phép hủy | Lần gửi tiếp vẫn hỏi xác nhận, hủy không khóa form | Có confirm lần hai; không pending; logout POST=0 |
| chrome | Refresh sau khi hủy logout vẫn cảnh báo | Không mất bảo vệ quiz sau khi hủy form khác | beforeunload; ở lại giữ đáp án |
| chrome | Liên kết rời quiz sau khi hủy vẫn cảnh báo | Hủy rời bằng liên kết giữ đáp án | beforeunload; ở lại giữ đáp án |
| chrome | Logout sau hủy: xác nhận và nhấp đúp | Một confirm, đúng một POST có CSRF và đăng xuất thật | confirm=1; logout POST=1; CSRF có; Student=401 |
| chrome | Sau hủy logout vẫn nộp quiz hợp lệ | Không cảnh báo nhầm; nhấp đúp tạo một POST quiz và có kết quả | dialog=0; quiz POST=1; trang result có 5 câu |
| chrome | Quiz chưa đổi đáp án không hỏi logout | Không có confirm khi không có lựa chọn chưa nộp | dialog=0; thêm đúng một POST logout |
| chrome | Tắt JavaScript: logout vẫn hoạt động | Native form đăng xuất bằng một POST | logout POST=1; Student=401 |
| edge | Logout rồi ở lại không kẹt pending | Giữ đáp án; pending/status trống; chưa gửi POST | Đáp án còn; aria-busy không có; status=0; logout POST=0 |
| edge | Logout lại bằng Enter vẫn cho phép hủy | Lần gửi tiếp vẫn hỏi xác nhận, hủy không khóa form | Có confirm lần hai; không pending; logout POST=0 |
| edge | Refresh sau khi hủy logout vẫn cảnh báo | Không mất bảo vệ quiz sau khi hủy form khác | beforeunload; ở lại giữ đáp án |
| edge | Liên kết rời quiz sau khi hủy vẫn cảnh báo | Hủy rời bằng liên kết giữ đáp án | beforeunload; ở lại giữ đáp án |
| edge | Logout sau hủy: xác nhận và nhấp đúp | Một confirm, đúng một POST có CSRF và đăng xuất thật | confirm=1; logout POST=1; CSRF có; Student=401 |
| edge | Sau hủy logout vẫn nộp quiz hợp lệ | Không cảnh báo nhầm; nhấp đúp tạo một POST quiz và có kết quả | dialog=0; quiz POST=1; trang result có 5 câu |
| edge | Quiz chưa đổi đáp án không hỏi logout | Không có confirm khi không có lựa chọn chưa nộp | dialog=0; thêm đúng một POST logout |
| edge | Tắt JavaScript: logout vẫn hoạt động | Native form đăng xuất bằng một POST | logout POST=1; Student=401 |

### Chạy lại và bàn giao

Thêm `QA_CASE_SET=quiz-exit` vào lệnh chạy `tests/form-feedback.cjs`; dùng DB seed riêng dùng một lần vì runner tạo tài khoản/tiến độ/attempt. Đặt `QA_SOURCE_SHA` theo nguồn mã thực sự chạy và `QA_SOURCE_LABEL` nếu kiểm candidate chưa commit. Không biến các kết quả cũ thành lượt chạy trên SHA mới.

Bản vá sửa bốn file đã có trong PR16: forms.js, runner, báo cáo này và JSON evidence. Tổng diff của PR16 vẫn sáu file. Không sửa PHP service/action, schema, CSRF hoặc logic quiz/AI. Chưa commit/push từ cloud; An tự commit/push rồi gửi SHA mới để Tú/Quân review lại phần bị ảnh hưởng. Không tự merge hoặc đánh dấu toàn bộ A1–A5 hoàn tất. Kiểm thủ công Windows và các điều kiện nghiệm thu nhóm vẫn ghi riêng.
