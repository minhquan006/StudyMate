# Kiểm thử phần Quân

Yêu cầu: Python 3, Docker Linux. Không cần PHP trên host hoặc key Gemini. Các lệnh phải chạy từ root repository.

```bash
docker build -t studymate-quan-php -f tests/quan/Dockerfile tests/quan
python3 tests/quan/run.py --driver sqlite
python3 tests/quan/run.py --driver mysql
```

Image kiểm thử dùng PHP 8.3 CLI với PDO MySQL và pcntl. PDO SQLite đã có trong image gốc. Nếu Buildx không có thư mục cấu hình ghi được, đặt `DOCKER_CONFIG` tới một thư mục riêng ghi được hoặc dùng `DOCKER_BUILDKIT=0` với Docker còn hỗ trợ legacy builder. Không tắt TLS/checksum.

Runner sao chép source vào thư mục tạm, bỏ qua `.git`, `config.php`, `.env`, storage và cache Python. Nó tạo cấu hình/DB riêng, sinh mật khẩu DB tạm, dùng network/container tên ngẫu nhiên và chỉ bind HTTP vào loopback. Khối finally xóa container, anonymous volume và network của chính lượt chạy; không dùng `docker system prune`, không chạm DB phát triển. Khi runner bị SIGKILL/mất máy, kiểm tra container có prefix `studymate-quan-` trước khi dọn phần còn lại.

Không chạy `tests/run.php` trực tiếp trên dữ liệu cần giữ. `tests/quan/ai.php` chỉ được gọi trong runner; transport Gemini được mô phỏng bằng callable chỉ server PHP có thể cung cấp, không lấy từ POST/config endpoint.

## Kiểm tra trình duyệt tùy chọn

Cần Node, package `playwright` trong `NODE_PATH` và executable Chrome/Chromium thực tế. Không cần Python Playwright:

```bash
NODE_PATH=/path/to/node_modules python3 tests/quan/run.py --driver mysql --browser --browser-executable /path/to/chrome --artifacts /tmp/studymate-quan-evidence
```

Kiểm tra Instructor, roster, quiz editor và ba tab Admin tại 390/768/1366 px, không overflow toàn trang, điều hướng phân trang giữ tìm kiếm. Screenshot là ảnh viewport/full page tự động, **không có thanh địa chỉ**, không thay bằng chứng giảng viên yêu cầu browser toolbar.

## Phạm vi

- PHP lint; 14 service checks hiện có trên DB đã chọn.
- HTTP CSRF thiếu/sai cho 12 action Instructor/Admin/AI; role/ownership, ID chéo khóa, thay đổi cấu trúc bị khóa và tính nguyên tử khi từ chối.
- Biên độ dài quiz, dữ liệu scalar/array sai, chỉ số đáp án sai; chỉnh trình bày ở published/archived chưa enrollment.
- Hai phiên bị thu hồi sau khóa/mở, Admin không tự khóa Admin, phân trang/filter/bounded inputs, roster giữ progress/best/completion.
- Luồng publish → enroll → complete → quiz 100% → AI disabled503 → archive → giữ quyền học; dữ liệu hoàn thành giữ sau restart.
- AI: ask/summarize giả lập, phân quyền, input/quota phút/ngày, provider error/timeout/malformed/safety, không trả thought parts, 12 tiến trình riêng tranh 5 lượt chung.

AI mocks **không** chứng minh model tồn tại, key dùng được, TLS/network thật, chất lượng phản hồi hoặc khả năng chống prompt injection của Gemini. Cần chạy checklist live trong `docs/QUAN_ACCEPTANCE.md` trước khi ký nghiệm thu FR10. Bộ này không thay kiểm thử tải hoặc chứng minh mọi transaction đồng thời của toàn ứng dụng.

## Docker Compose/Apache chính thức

Chạy `python3 tests/quan/compose.py` từ checkout. Script build Dockerfile thật trong bản sao tạm, sinh credentials riêng, kiểm health, login3 role, 14 service checks, binding model và persistence sau restart; cuối cùng chỉ xóa project/volume nó tạo. Image Docker cần mạng lúc chưa cache. Không dùng lệnh này với DB phát triển.

Runner Compose giữ Docker context, plugin và `DOCKER_CONFIG` của môi trường gọi để chạy được trên Docker Desktop. Với môi trường cấu hình chỉ đọc, tự truyền `DOCKER_CONFIG` riêng đã cấu hình đúng endpoint/plugin; script không tự thay nó bằng thư mục rỗng.

Kiểm thêm app restart khi DB đang dừng rồi khởi động DB lại; phải tự phục hồi HTTP và giữ nguyên số quiz attempts. Dockerfile chờ kết nối MySQL trước setup, không retry/reset dữ liệu để che lỗi.

## Gemini trực tiếp bằng CLI (công cụ bổ sung)

```bash
python3 tests/quan/live_ai.py --allow-live --model gemini-3.5-flash-lite --artifacts /tmp/studymate-live-ai
```

Cần image kiểm thử đã build, Docker có mạng tới Google và key trong `config.php` riêng; có thể chọn file khác bằng `--config /duong/dan/config.php`. Runner chỉ mount file này read-only để đọc key, không dùng kết nối DB của nó, không in key, không thay cấu hình local hoặc bật billing. Bản sao source/SQLite/seed riêng bị xóa khi kết thúc.

Có tối đa sáu request sinh nội dung thật: ask, summarize, câu hỏi thiếu dữ liệu, injection có kiểm soát, học viên cũ ở archived và một key giả trong fixture riêng. Hai ca sai role/chưa enroll chỉ dùng spy transport, phải bị chặn trước provider. File `gemini-live.json` lưu expected/actual và câu trả lời để reviewer đánh giá chất lượng; exit code khác 0 nếu trạng thái/call count không đạt, không tự ký FR10 dù tất cả request thành công.

Chọn model theo tài khoản, không tự fallback sang model khác. Ngày 30/10 metadata của `gemini-2.5-flash-lite` trả 200 nhưng generateContent trả 404 với key local; cần xem kết quả live, không chỉ kiểm model metadata. Ngày 01/11 profile `gemini-3.5-flash-lite` đã có bằng chứng HTTP/MySQL/Chrome thật; `.env.example` chọn profile này, còn fallback PHP và config riêng giữ nguyên. CLI này không thay luồng HTTP/UI ở bộ kiểm tích hợp bên dưới.

## PDF và gói review

`python3 tools/build_review_pdfs.py --output /tmp/studymate-delivery` cần python-docx, reportlab và font NotoSans/DejaVuSans Unicode, tạo PDF CSDL và báo cáo/checklist. Đây không phải PDF chụp thanh địa chỉ thật. `python3 tools/package_release.py --output /tmp/studymate-delivery` tạo ZIP mã nguồn, manifest SHA256 từng tệp và checksum archive; không chứa config.php local, .env hoặc DB/session.


## Bộ kiểm tích hợp ngày 01/11/2026

Runner browser mới dùng Node/Playwright thay Python Playwright. Website vẫn chỉ dùng PHP/PDO/MySQL. Cài Playwright ở thư mục công cụ riêng hoặc dùng runtime đã có, đặt NODE_PATH và executable Chrome thực tế; không đưa dependency vào source web.

```bash
python3 tests/quan/verify.py --suite all --browser-executable /path/to/chrome --node-module-path /path/to/node_modules --artifacts /tmp/studymate-quan-checks
# HTTP/MySQL/UI với key trong config.php riêng, tối đa 5 mẫu nội dung và 1 key giả:
python3 tests/quan/verify.py --suite live --allow-live --model gemini-3.5-flash-lite --browser-executable /path/to/chrome --node-module-path /path/to/node_modules --artifacts /tmp/studymate-quan-live
# Chỉ lỗi/quyền/quota app, không dùng key thật để sinh nội dung:
python3 tests/quan/verify.py --suite live --allow-live --live-errors-only --model gemini-3.5-flash-lite --browser-executable /path/to/chrome --node-module-path /path/to/node_modules --artifacts /tmp/studymate-quan-live-errors
```

`--suite all` là offline, gồm SQLite/MySQL/Compose; live phải bật riêng. Công cụ lưu từng lượt/log/fingerprint và không xóa log failed khi kiểm lại. Live lấy key read-only, chỉ đưa key vào fixture ngoài build context/public; tự dọn project và DB test. Không dùng kết quả live SQLite trước đây thay cho HTTP/MySQL/UI mới; xem báo cáo tích hợp để biết các lượt dừng và giới hạn.

PDF builder dùng Python có python-docx/reportlab và font Unicode NotoSans hoặc DejaVuSans; `tools/build_review_pdfs.py` là entrypoint tương thích. Việc chạy website không cần các thư viện tạo tài liệu này.
