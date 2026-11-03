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
