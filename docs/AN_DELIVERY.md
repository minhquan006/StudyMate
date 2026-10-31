# Bàn giao An — giao diện và bằng chứng demo

## Cập nhật sau giải conflict — 01/11/2026

### SHA bàn giao và nguồn mã

- Head PR #15 đã push: **`81dc740916360c4f6229cd1026837939bd44fd10`** (`feat/an-ui-acceptance`).
- Nền mã Tú đã nhận: **`1a27ec1bc939b7b98324dc6f95ec7aaa175f484a`**. Merge commit có hai parent `f8565fb` và `1a27ec1`.
- Phần ghép giữ `searchQuery()`/`maxlength="100"` của Tú cùng `tabindex="0"`, `role="region"`, `aria-label` của An cho ba bảng Admin; README.md/Readme.txt đồng bộ. Hai conflict cũ đã được giải, không còn task giải lại chúng.
- Nhánh Tú sau đó thêm commit **chỉ sửa tài liệu** `24f6e1b9b4d4c60961a59f594b7df8cb0bc11960`. Commit này chưa nằm trong `81dc740`; không gọi `1a27ec1` là tip mới nhất của PR #14.
- Khi bổ sung tài liệu này: main vẫn `861e72c`; #14/#15 còn mở. #15 vẫn base nhánh Tú. Chờ #14 vào main rồi đổi base #15 và kiểm lại diff.
- Head `81dc740` là mốc mã đã bàn giao; commit tài liệu bổ sung sau đó có SHA riêng, không sửa SHA/ngày trong các bằng chứng cũ.

### Bằng chứng được giữ riêng theo từng lượt

| Lượt / người hoặc công cụ thực hiện | Nguồn mã và môi trường | Coverage / giới hạn |
|---|---|---|
| 27/10 — Codex hỗ trợ An, hồ sơ lịch sử bên dưới | UI/diễn tập `6d66a71`; cài độc lập bản Tú `593141f`; Linux Docker/MySQL | 108 lượt Chrome/Edge; giữ SHA, ngày và nguồn gốc. Không chuyển thành kết quả trên `81dc740`. |
| 31/10 — gói Quân gửi An giải conflict PR #15 | Candidate `f8565fb + 1a27ec1` cùng ba file resolved; theo REVIEW.md và verification/result.json trong ZIP | 54 lượt Chrome 156.0.8078.79 trên macOS; các suite/HTTP/startup ghi trong gói. Đây là báo cáo của Quân/Codex, không phải An tự chạy. Không chạy Edge trong lượt này. |
| 31/10 — Codex hỗ trợ An, chạy trong cloud phiên này | Candidate cùng cặp commit và file resolved; Linux PHP 8.3.36, MySQL 8.4.13, **Chromium 154.0.8037.57** | Lint 28 file; service 14; phân trang 8; UTF-8 58 (14 Public + 14 Student + 30 Admin); model 8; AI offline 24; 54 lượt màn hình ở 390/768/1366 (cao 900), 0 audit failures; 3 bảng Admin Tab/focus/cuộn phím ở 390×844; setup lặp/restart giữ hash 12 bảng. Không chạy Chrome/Edge hoặc Gemini thật. |
| Review Tú báo trong comment #15 | Tú ghi nguồn đúng head `81dc740`, Codex chạy cloud/MySQL riêng | Theo comment: UTF-8 58, phân trang 8, service 14, HTTP tìm kiếm 13, startup/bảo toàn dữ liệu 13 và lint. Không browser/Gemini. Đây là kết quả Tú bàn giao, không cộng vào suite do phiên này chạy hoặc đổi thành kiểm tra Windows của Tú. |
| 01/11 — cập nhật mốc tài liệu | Đọc diff/trạng thái và đối chiếu fingerprint | Không chạy lại các suite 30–31/10; không tạo bằng chứng Windows/Approve/CI thay người khác. |

Bằng chứng lượt cloud do Codex hỗ trợ An: [README](evidence/an-post-conflict-2026-10-31/README.md), [báo cáo gốc](evidence/an-post-conflict-2026-10-31/REPORT_VI.md), [audit Chromium](evidence/an-post-conflict-2026-10-31/chromium-audit.json), [đối chiếu nguồn](evidence/an-post-conflict-2026-10-31/commit-source-match.json).

Candidate đã chạy test trước commit. Sau khi An push, đối chiếu **310/310 file tracked** khớp toàn bộ nội dung tại `81dc740`, không có file thêm/bớt/thay đổi. Vì vậy có liên hệ nội dung với head đã push; không ghi thành lượt chạy test mới trên commit. Fingerprint trong image đối chiếu riêng **309 file**, không tính storage/.gitkeep bị .dockerignore loại.

### Phần còn cần An và nhóm hoàn tất

1. Cập nhật mô tả #15 cùng mốc head/nền/nguồn kiểm thử này; nhờ Tú/Quân review và Approve chính thức khi đạt. Comment review đã có, không gọi là chưa từng review; test cloud không phải status CI hoặc Approve.
2. Khi người có trách nhiệm merge #14, fetch main và đổi base #15 sang main trên GitHub; kiểm Files changed còn đúng phần An. Giữ cập nhật tài liệu `24f6e1b` của Tú, xử lý vấn đề mới nếu có. Không tự merge thay nhóm.
3. An kiểm bổ sung trực quan/bàn phím trên máy nhóm ở 390/768/1366, tìm kiếm/phân trang/empty state/tổng số và vùng bảng; ghi browser, phiên bản, hệ điều hành, SHA, expected/actual thật. Chỉ kiểm phần chưa có hoặc bị đổi; không tự nhận cloud là thao tác của An trên Windows.
4. An tự thực hiện lượt cài độc lập chỉ theo README trên SHA nghiệm thu được nhóm chốt, cấu hình/project/DB MySQL mới. Ghi health, login 3 role, setup lặp, restart và đối chiếu dữ liệu. Stack Windows vừa dùng để chạy test không tự thay bằng chứng cài sạch độc lập này.
5. An review HTML Instructor/Admin của Quân trên nền tích hợp, đặc biệt phần local Quân còn bàn giao. Chuẩn bị ảnh có thanh địa chỉ thật, PDF và phần Student trong video theo yêu cầu môn đã xác nhận; video diễn tập không tiếng không phải bản nộp cuối.
6. Sau tích hợp, cả ba dùng cùng SHA/DB nghiệm thu riêng, chạy luồng Instructor → Student → Gemini thật → Admin thu hồi phiên; Tú kiểm dữ liệu/restart/restore. Chốt lại SHA và đầu ra bị thay đổi trước nộp.

**A1–A5 chưa tự đánh dấu Done.** Gemini thật, review chính thức, phần kiểm độc lập do An thực hiện và video/ảnh/hồ sơ cuối vẫn còn điều kiện riêng.

---

## Hồ sơ lịch sử ngày 27/10/2026 — giữ nguyên nguồn và SHA


Ngày thực hiện: **27/10/2026 (UTC+7)**. Có AI hỗ trợ đọc mã, viết kiểm tra,
chạy Docker/browser, ghi hình và soạn báo cáo. Đây không phải xác nhận An đã
trực tiếp trình bày/kiểm tra mọi bước hoặc cả ba người đã nghiệm thu chung.

## Nguồn và trạng thái

- Bản của Tú: **PR #14**, `593141fa2a021d7f1744dc2e7e20fe16c31423ad`,
  nhánh `tu/nghiem-thu-thu-cong`, đang chờ review/merge.
- Phần bổ sung của An: `feat/an-ui-acceptance`, kế thừa trực tiếp bản của Tú.
- SHA chạy lại UI và diễn tập: **`6d66a71b1cc3ef6d8cd985d445d8fcba962a45b0`**.
  Commit bàn giao bằng chứng sau đó chỉ thêm tài liệu/ảnh/video, không đổi ứng dụng.
- Các kết quả dưới đây là lần chạy mới trên Docker/MySQL. Không dùng kết quả
  cũ trong `docs/evidence/final/` hoặc lần thử main cũ thay thế các ca này.

Phân trang và truy vấn tổng hợp thuộc bản của **Tú** được giữ nguyên. An bổ sung
regression/browser checks độc lập, focus bàn phím cho vùng bảng, healthcheck TCP
bằng tài khoản ứng dụng, app healthcheck và sửa README về nhánh/cache Buildx.
Không chuyển `DOCKER_CONFIG` sang thư mục trống để xử lý lỗi cache; giữ auth,
proxy và TLS sẵn có, chỉ đổi `BUILDX_CONFIG` khi cần.

| Mã  | Bàn giao hiện tại                                                                                                     | Trạng thái theo quy tắc của Quân                                                           | Reviewer / hạn                                  |
| --- | --------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------ | ----------------------------------------------- |
| A1  | Kiểm lại Student/Instructor/Admin/roster trên MySQL; 12 dòng, trước/sau, clamp trang, giữ tìm kiếm/tab, số tổng riêng | Đã kiểm tra tự động; **chờ review và merge**, chưa Done                                    | Tú: Public/Student; Quân: phần quản trị — 28/10 |
| A2  | 108 lượt màn hình, 390/768/1366 px; ảnh mới có Instructor/Admin/AI                                                    | Đã kiểm tra; **chờ review**, chụp lại nếu bản cuối thay đổi                                | Quân — 29/10                                    |
| A3  | Chrome/Edge thật, Tab/focus, label, nút >=44 px, empty/error và bảng cuộn                                             | Đã kiểm tra tự động; **chờ review và merge**                                               | Tú/Quân — 30/10                                 |
| A4  | Checkout sạch đúng SHA Tú, cấu hình/volume mới, MySQL, setup lặp, đăng nhập 3 role và restart                         | Đạt trong cloud; **chờ xác nhận độc lập/review của nhóm**, chưa Done                       | Tú — 30/10                                      |
| A5  | Footage thật 1920×1080 không tiếng, 6 mốc diễn tập; shot list bên dưới                                                | **Chưa hoàn thành**: thiếu bản cuối đã nghiệm thu, lời trình bày đủ 3 người và Gemini thật | Quân — 31/10                                    |

Không tự merge hoặc đánh dấu Done: cần người khác review, merge vào main,
ghi SHA chốt và chạy lại kiểm tra liên quan trên SHA đó.

## Kết quả lượt 27/10/2026 (lịch sử)

| Kiểm tra           | Mong đợi                                                                                      | Quan sát thực tế                                                                | Bằng chứng                                                                   |
| ------------------ | --------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------- | ---------------------------------------------------------------------------- |
| PHP lint           | Không lỗi cú pháp                                                                             | Đạt                                                                             | [php-lint.txt](evidence/an-review-2026-10-27/php-lint.txt)                   |
| Dịch vụ MySQL      | Grade, UUID, completed_at, best score, FK/unique, bcrypt đúng                                 | **14/14** checks đạt                                                            | [services-mysql.txt](evidence/an-review-2026-10-27/services-mysql.txt)       |
| Phân trang MySQL   | Tổng toàn bộ; trang giữa/cuối; xóa mục cuối; empty; p âm/array; giữ q/tab                     | **8/8** checks đạt; fixture rollback                                            | [pagination-mysql.txt](evidence/an-review-2026-10-27/pagination-mysql.txt)   |
| Browser            | HTTP 200, không tràn toàn trang, input có nhãn, nút >=44 px, focus hiển thị, bảng cuộn có Tab | **108 lượt màn hình**, không lỗi được phát hiện                                 | [browser-audit.json](evidence/an-review-2026-10-27/browser-audit.json)       |
| Cài sạch bản Tú    | Không dùng config/DB cũ, setup lại không reset, restart giữ dữ liệu                           | Health OK; login Student/Instructor/Admin; **hash đủ 12 bảng khớp** sau restart | [clean-install-tu.json](evidence/an-review-2026-10-27/clean-install-tu.json) |
| Diễn tập nghiệp vụ | Tạo/publish → đăng ký/enroll → lưu tiến độ → quiz/result → sai quyền → khóa phiên             | Đạt 6 mốc; quiz 100%, sai quyền 403, phiên cũ 401                               | [demo-rehearsal.json](evidence/an-review-2026-10-27/demo-rehearsal.json)     |
| AI chưa cấu hình   | Lỗi rõ, học/quiz tiếp tục được                                                                | POST ask trả503, alert rõ; vẫn làm quiz được                                    | Ảnh `03-ai-unconfigured.png` trong bộ bằng chứng                             |
| Gemini thật        | Ask và summarize có phản hồi, quota/lỗi thật                                                  | **Chưa chạy**, không có key sử dụng trong môi trường này                        | Cần Quân cấu hình key riêng an toàn và kiểm lại                              |

Browser thực tế: **Google Chrome 156.0.8078.47**, **Microsoft Edge 155.0.4283.62**
trên Linux. Gói tải qua HTTPS chính thức, SHA256 khớp package index chính thức
trước khi sử dụng. Các chiều rộng dùng viewport browser, không phải thiết bị
vật lý; không khẳng định Safari hoặc hệ điều hành khác đã được thử.

108 là số lượt màn hình (18 route × 3 kích thước × 2 browser), mỗi lượt có nhiều
assertion; không cộng kết quả cũ hoặc gọi tất cả là 108 ca nghiệp vụ độc lập.
Fixture dài/nhiều dòng là dữ liệu tổng hợp trong `studymate_ui_test`, không phải
người học thật. Các bộ test có ghi dữ liệu nên chỉ chạy trên project dùng một lần.

## Cài độc lập và vấn đề README

Bản cài độc lập ở checkout riêng, đúng `593141f`, không có config.php, `.env`
hoặc DB cũ. Tạo hai mật khẩu mới từ `.env.example`, dùng project
`studymate-an-tu593141f` với volume mới và port mặc định 8080; build theo README.
Không dùng SQLite, không chép DB hoặc credential từ BookFlow hay stack khác.

Đã chạy health, setup lần hai, login cả ba role, snapshot đủ 12 bảng,
`docker compose restart`, health retry và snapshot lần nữa. So sánh hash khớp.
Không dùng bản đã chạy service tests làm DB sạch cho kiểm thử quiz gate.

README của Tú còn chỉ nhánh `codex/tu-final-acceptance` thay vì nhánh PR #14
hiện tại; phần An sửa lại. Hướng dẫn cache Buildx cũ thay `DOCKER_CONFIG` sẽ làm
mất cấu hình auth/proxy sẵn có; phần An chuyển sang `BUILDX_CONFIG`.

Cloud có giới hạn ổ 32 GB và Docker vfs; đã gặp hết dung lượng khi chạy nhiều
stack/image đồng thời. Chỉ dọn stack/cache thử nghiệm do phiên này tạo; giữ
BookFlow và dữ liệu có trước. Đây là giới hạn cloud, không ghi thành lỗi ứng dụng.

## Chạy lại kiểm tra của An

Cần Docker/Compose; Node chỉ dùng cho browser runner, không phải runtime website.
Dùng checkout riêng, `.env` test riêng, chọn tên project chưa dùng. Mỗi vòng nghiệm
thu sạch dùng volume mới; không xóa volume thật để làm test pass.

```bash
WEB_PORT=8083 docker compose -f compose.yaml -f compose.qa.yaml -p studymate-an-new up --build -d --wait
docker compose -f compose.yaml -f compose.qa.yaml -p studymate-an-new exec -T app php tests/run.php
docker compose -f compose.yaml -f compose.qa.yaml -p studymate-an-new exec -T app php tests/an-pagination.php
docker compose -f compose.yaml -f compose.qa.yaml -p studymate-an-new exec -T app php tests/ui-fixture.php
```

Override QA chọn database có hậu tố `_test` và tắt AI; fixture chỉ chấp nhận DB
`_test`, không tự biến DB thật thành test. Service test hoàn thành khóa JavaScript
để màn hình quiz/result có dữ liệu; đây **không phải** dữ liệu sạch cho suite
HTTP quiz gate của Tú.

Browser runner dùng Playwright 1.64.1 bên ngoài source, ví dụ:

```bash
npm install --prefix /tmp/studymate-qa-runner playwright@1.64.1
export NODE_PATH=/tmp/studymate-qa-runner/node_modules
export QA_BASE_URL=http://127.0.0.1:8083
export QA_CHROME=/path/to/actual/chrome
export QA_EDGE=/path/to/actual/msedge
export QA_OUTPUT=/tmp/studymate-new-evidence
# Đặt QA_DEMO_PASSWORD local khớp seed, không commit hoặc đưa vào log.
node tests/ui-audit.mjs
# Footage cần FFmpeg helper của Playwright:
/tmp/studymate-qa-runner/node_modules/.bin/playwright install ffmpeg
node tests/demo-flow.mjs
```

Audit giả định fixture mới (14 enrollment Student, 16 khóa Instructor); nếu đã
chạy diễn tập tạo thêm khóa, đặt `QA_INSTRUCTOR_TOTAL` theo số thật đã xác nhận
trong DB. Không đổi giá trị để che lỗi tổng số. Muốn có footage riêng, đổi
`QA_OUTPUT` trước khi chạy demo. Script demo tạo khóa/user tổng hợp mới, publish,
quiz và khóa user đó; không chạy trên dữ liệu thật. File giới hạn login/register
có thể ảnh hưởng các lần lặp sát nhau, cần chờ cửa sổ quota thay vì bỏ kiểm tra.

## Video và nghiệm thu chung còn lại

[Footage 1080p không tiếng](evidence/an-review-2026-10-27/demo-rehearsal-1080p-silent.mp4)
là bản diễn tập tự động, **chưa phải video nộp bài**. Metadata xác nhận kích thước
[1920×1080](evidence/an-review-2026-10-27/video-metadata.json). Ảnh là viewport thật,
không có thanh địa chỉ browser; nếu bộ nộp yêu cầu thanh địa chỉ, cần chụp lại.

Shot list gợi ý cho buổi ghi hình thật (thời lượng do nhóm chốt theo đề chính thức):

1. Cả ba giới thiệu, hiện SHA nghiệm thu đã merge và cấu hình Docker/MySQL riêng.
2. Quân trình bày tạo khóa → chương/bài/quiz → publish; giữ đủ thời gian đọc form.
3. An đăng ký Student → tìm khóa → enroll → học/lưu tiến độ → quiz → result.
4. Quân bật Gemini an toàn; An hỏi và tóm tắt thật. Che key, không bật billing.
5. Tú đối chiếu dữ liệu, thử gửi lặp, restart và chứng minh dữ liệu giữ nguyên.
6. Quân khóa tài khoản; An mở lại phiên cũ để thấy401, thử một truy cập sai quyền403.
7. Cả ba ghi đạt/không đạt, SHA, bằng chứng và lỗi còn lại; mỗi người có phần nói.

Chốt cùng **một SHA cuối** sau review/merge, chạy lại các ca trên đó, rồi chụp lại
ảnh/quay video cuối. Không dùng footage diễn tập, AI-offline hoặc bằng chứng từ
SHA cũ để thay cho ca Gemini thật hay phần trình bày của thành viên.
