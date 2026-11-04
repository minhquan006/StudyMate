# StudyMate Project Specification

**Baseline 2.2 — 12/10/2026 — Leader Nguyễn Minh Quân 52400227.** Thành viên: Lê Nguyễn Vạn An 52400258; Phạm Văn Tú 52400252. Hoàn tất dự án 01/11/2026. Giữ nguyên owner và mức giờ của baseline 2.1. Baseline này thay toàn bộ hướng React/Laravel trước đây theo quyết định của leader: **PHP thuần dựng HTML trên server, PDO, MySQL**.

## 1. Quyết định đã chốt

PHP 8.3+, không React, Vite, Node, Laravel hoặc Blade. HTML/CSS vẫn cần để trình bày giao diện, nhưng dữ liệu và xử lý ở PHP. Bootstrap 5.3 CSS local + CSS riêng; không Bootstrap JS/CDN. Form GET để đọc/tìm kiếm; form POST để đổi dữ liệu. JavaScript không bắt buộc cho các luồng hiện tại. Không dựng hai frontend.

MySQL 8.4 InnoDB utf8mb4 là CSDL bản cuối; SQLite PDO là lựa chọn local/test cho mốc PHP nếu máy chưa có MySQL, không thay nghiệm thu MySQL. Không localStorage, không auth giả hoặc quiz chấm ở client. Website miễn phí, ba role, mỗi user một role. Student tự đăng ký; Instructor/Admin seed. Một quiz cuối course, 5–20 câu, bốn lựa chọn/câu, một đáp án, đạt từ 70%. Cấu trúc/quiz khóa sau enrollment đầu tiên. AI Gemini free tier, không billing hoặc provider trả phí.

Không payment/certificate/reviews/wishlist/notifications/gamification/upload/PWA/realtime/RAG. Profile chỉ xem. Tiến độ là đánh dấu, không chứng nhận xem video. Quiz là luyện tập được làm lại, không kỳ thi chống gian lận.

## 2. Cấu trúc và owner

| File/khu vực | Owner | Reviewer |
|---|---|---|
| app/bootstrap.php, services.php | Tú | Quân |
| app/auth_actions.php, student_actions.php | Tú | Quân |
| database/schema.php, seed.php, tools/setup.php | Tú | Quân |
| Dockerfile, compose.yaml, docker/config.php | Tú | Quân |
| app/views.php public/auth/Student HTML | An | Tú |
| public/assets/app.css, shared view helpers, responsive | An | Quân |
| app/management_views.php Instructor/Admin HTML | Quân | An |
| app/instructor_actions.php, admin_actions.php | Quân | Tú |
| app/ai_service.php, ai_actions.php và panel AI | Quân | Tú |
| public/index.php, app/actions.php dispatch | Tú điều phối; An layout | Quân |
| tests, docs, video và bản nộp | Mỗi owner test phần mình; Quân điều phối | Cả ba |

Ownership là trách nhiệm review. Không sửa file chung âm thầm; PR nhỏ và mô tả contract bị ảnh hưởng. Quân không tự review PR của mình. Tách file theo module để tránh cùng sửa một switch lớn. Front controller chỉ require các handler cần thiết, không chứa logic nghiệp vụ.

## 3. HTTP và điều hướng

Đường chạy: `public/` là document root. URL thật: `/index.php?page=courses&id=1`. Query page chỉ chọn danh sách handler cố định, không include file từ input. Không ghép host/port vào link; helper url() tạo link cùng origin. Không bắt buộc URL mọi trang có đuôi .php riêng: index.php dựng từng trang trên server.

| page GET | Quyền | Nội dung |
|---|---|---|
| home, courses | Public | Home/catalog; q/category/p phân trang 12 |
| detail&id | Public | Published metadata/outline, không body/quiz |
| login, register | Public | Form và validation server |
| profile | Có phiên active | Name/email/role |
| student | Student | My Courses/count/progress/best score |
| learn&id&lesson | Student đã enroll | Lesson viewer/outline/completion/AI |
| quiz&id | Student đã học đủ | Đề không đáp án/explanation, history riêng |
| result&id | Student owner attempt | Điểm, chọn/đáp án đúng và explanation |
| instructor | Instructor | Khóa của mình |
| edit&id&tab | Instructor owner | info/content/quiz/students |
| preview&id&lesson | Instructor owner hoặc Admin | Chỉ đọc, không ghi học tập |
| admin&tab | Admin | users/courses/categories |

Form POST tới index.php, hidden action và csrf. Handler đổi dữ liệu rồi redirect 303 (POST/Redirect/GET). Lỗi trả đúng 401/403/404/409/419/422/429/503/504 và HTML thông báo, không stack trace khi production. `return_page/return_tab` chỉ chọn view cố định, không redirect URL bên ngoài.

Actions đã chốt: register/login/logout; enroll/last_lesson/complete/submit_quiz; course_save/course_state/course_delete/chapter_save/chapter_delete/lesson_save/lesson_delete/quiz_save; user_status/category_save/category_delete; ai. Mọi thay đổi dùng POST; không sửa DB qua GET. Quiz request_id UUID giữ nguyên khi retry cùng lần nộp; một lần làm mới cấp UUID mới.

## 4. Authentication và bảo mật

Native PHP session với HttpOnly, SameSite=Lax, Secure khi HTTPS; session files trong storage/sessions ngoài public. Idle timeout 120 phút; login regenerate ID/CSRF; logout xóa dữ liệu phiên và regenerate ID. User auth_version được lưu trong phiên và kiểm với DB mỗi request; khóa/mở tăng version nên mọi phiên cũ vô hiệu, không cần xóa trực tiếp từng file session.

Student registration bỏ qua role/status từ client; email trim/lowercase, unique, tối đa 254; name 2–80; password >=10 ký tự, <=72 byte UTF-8, xác nhận, bcrypt cost12. Không plaintext/password/key trong logs. Login 5 lần/phút/email+IP; register 3/phút/IP; quota file ngoài public có flock. Đây là cấu hình một server, không phải distributed rate limiting.

CSRF token random32bytes, hidden trên mọi POST, hash_equals server. Authorization kiểm role/owner/enrollment tại handler và view. Wrong owner/private resource 404; wrong role403; không phiên401. Khóa user request kế bị chặn. Admin không khóa bất kỳ Admin. PDO prepared statements, allowlist input; h() escape HTML. Markdown chỉ render tập con heading/paragraph/code, raw HTML hiển thị như text, không chạy script. YouTube ID allowlist định dạng 11 ký tự, không nhận iframe tùy ý.

## 5. Dữ liệu 12 bảng

Schema code: database/schema.php; bootstrap bằng tools/setup.php. Mọi bảng có id/timestamps UTC, FK phù hợp, không soft delete. MySQL id BIGINT UNSIGNED; SQLite INTEGER AUTOINCREMENT chỉ local. Không bảng sessions/cache framework. Không migration tool bên ngoài; sửa schema sau khi nhóm đã có dữ liệu phải bổ sung script migration có version, không sửa âm thầm rồi reset.

| Bảng | Trường chính/ràng buộc |
|---|---|
| users | name,email unique,password bcrypt,role,is_active,auth_version |
| categories | name unique |
| courses | instructor_id/category_id FK; title/description/cover_key/status |
| chapters | course_id,title,position>0 |
| lessons | chapter_id,title,body,youtube_video_id nullable,position>0 |
| enrollments | unique user_id/course_id,last_lesson_id nullable,completed_at nullable |
| lesson_progress | unique user_id/lesson_id,completed_at |
| quizzes | unique course_id |
| questions | quiz_id,prompt,explanation,position |
| answers | question_id,text,is_correct,position |
| quiz_attempts | user_id,quiz_id,request_id,submission_hash,correct_count,total_questions,score_percent,passed,submitted_at; unique user/request_id |
| quiz_answers | attempt_id,question_id,answer_id nullable,is_correct; unique attempt/question |

Course→chapter→lesson và course→quiz→question→answer CASCADE. FK users/categories và tất cả tham chiếu từ lịch sử RESTRICT. last_lesson_id nullable nhưng RESTRICT khi tham chiếu. Index course status/created, attempts user/quiz. FK không tự kiểm answer thuộc question/lesson thuộc course; service kiểm.

## 6. Vòng đời và quyền sửa

Draft→published→archived. Published→draft chỉ chưa enrollment. Archived→published khi đủ điều kiện. Admin chỉ archive published. Archived ẩn catalog, người cũ vẫn học/quiz/AI, không nhận người mới. Publish có chapter, mỗi chapter có lesson, quiz 5–20 hợp lệ. Chỉ xóa draft chưa enrollment/attempt.

| Thao tác | Draft chưa enroll | Published/archived chưa enroll | Sau enrollment |
|---|---|---|---|
| Course metadata và title chapter/lesson | Có | Có | Chỉ sửa trình bày |
| Body/video | Có | Có | Không đổi mục tiêu học |
| Thêm/xóa/position | Có | Không | Không |
| Quiz gồm prompt/answers/explanation | Có | Không | Không |
| Xóa course | Có nếu chưa attempt | Không | Không |

Published chưa enroll unpublish trước sửa cấu trúc; archived chưa enroll publish rồi unpublish. Sau enrollment, sai quiz phải archive và tạo course mới thủ công; không versioning/clone. Rà soát quiz bởi người khác trước publish.

## 7. Luồng học và chấm

Enrollment transaction khóa hàng course (MySQL FOR UPDATE) + unique. Student mở lesson bất kỳ trong khóa; nút chuyển bài POST last_lesson rồi redirect GET learn; GET không tự ghi trạng thái. Complete lặp một progress nhờ kiểm tồn tại và unique. Progress = round(done/total*100,2); không uncomplete.

Quiz chỉ mở/nộp khi đủ lesson. Server kiểm IDs/options và chuẩn hóa câu thiếu=null, sort question_id; hash SHA256 quiz_id+normalized answers. Cùng UUID/hash trả attempt cũ, UUID khác payload409. Server lưu mọi quiz_answers kể cả câu trống trong transaction. Đạt dùng tỷ lệ chưa làm tròn >=0.70; score làm tròn hai chữ số để hiển thị. Không dùng score client.

completionService là nơi duy nhất set enrollment.completed_at. Quiz submit/progress gọi cùng transaction; MySQL khóa enrollment row. Chỉ set khi null và đủ bài/có attempt passed. Làm lại thấp không mất completed_at; best score bằng MAX DB. History owner only, Instructor xem tên/progress/best/complete theo khóa, Admin không điểm cá nhân.

## 8. AI miễn phí

Gemini generateContent qua PHP HTTPS stream, cấu hình `GEMINI_MODEL` (tương thích `AI_MODEL`), mặc định dự phòng `gemini-2.5-flash-lite` chưa được nghiệm thu với key thật; key config.php hoặc env server, không public. Output 600 tokens, timeout 25s, không SDK/history/RAG/tools. Server lấy lesson body từ DB; không nhận lesson text/system prompt từ client, không gửi email/tên/đáp án/điểm.

Quota atomic một host: 5/user/phút, 5/app/phút, 20/user/ngày, 100/app/ngày; ngày Asia/Ho_Chi_Minh. Request bị chặn trước provider không đếm; provider lỗi vẫn đếm. Provider429→429; timeout504; thiếu key/AI disabled/safety/no text/error503. Không retry tự động hoặc provider trả phí. Dùng free tier, ngân sách0 đồng, không bật billing. Nội dung free-tier có thể dùng cải thiện sản phẩm: chỉ gửi bài công khai tự biên soạn, không dữ liệu riêng tư.

Panel hỏi/tóm tắt POST; response chỉ lưu session để render một lần, sau đó xóa; không DB history, không trí nhớ hội thoại. AI chưa cấu hình phải báo lỗi thật, không giả answer. Quân probe key/model/quota và kiểm ask/summarize thật trước khi nghiệm thu FR10. Nguồn: https://ai.google.dev/gemini-api/docs/pricing và /text-generation, kiểm 12/10/2026.

## 9. Phân công dữ liệu mẫu

An: HTML/CSS published chưa enrollment; Tú: JavaScript published có progress mẫu; Quân: CSDL draft hợp lệ. Mỗi course2chapter×3lesson+quiz 5 câu. Quân rà nội dung An, An rà Tú, Tú rà Quân; Tú chuyển dữ liệu thành seed. Mật khẩu demo lấy config/env; setup không ghi đè DB đã có users. Không nút reset phá dữ liệu trên website. Backup trước sửa DB.

## 10. Lịch và giờ giữ nguyên

3 người×15h×3 tuần=135h. Phân bổ 13 giờ/người/tuần = 117 giờ; 18 giờ dự phòng, không thêm feature. Lịch12/10–01/11; chuyển PHP là thay kiến trúc được leader quyết định, không thay owner/giờ. Mốc tối12/10 bổ sung bài nộp frontend PHP; deadline hệ thống00:00 13/10, upload mục tiêu23:00.

| Tuần | Tú13h | An13h | Quân13h | Gate |
|---|---|---|---|---|
|12–18/10|PHP/PDO/schema/seed5h;auth/public/enroll5h;learning2h;review1h|layout/CSS/auth4h;catalog3h;learning4h;review2h|contract/board/probe2h;InstructorCRUD5h;quiz/publish4h;review2h|D7: PHP MySQL login/publish/enroll/learn thật |
|19–25/10|progress/completion3h;quiz/retry5h;dashboard2h;tests3h|learning3h;quiz/result4h;dashboard/profile2h;responsive/test4h|Admin/roster4h;AI4h;tests3h;tích hợp2h|D14: FR01–12 happy paths, freeze |
|26/10–01/11|DB/race/tests4h;setup/backup3h;bug3h;demo3h|responsive4h;bug4h;video/evidence5h|AI/integration3h;bug3h;docs2h;release5h|D21: không P0/P1, đủ hồ sơ |

Handoff D1 runtime/board/AIprobe;D2 schema/layout;D3 auth/metadata;D5 lesson/quiz/seed;D7 G1;D9 quiz/completion/admin;D11 roster/AI;D14 freeze;D15–18 MySQL/concurrency/clone;D19 video;D20 ZIP;D21 hoàn tất. Owner báo kẹt sau một buổi và trễ mốc>1ngày; không tự đổi contract hoặc cắt quyền để báo Done.

## 11. Git và Done

Repo https://github.com/minhquan006/StudyMate . main luôn chạy; task branch ngắn; một owner/reviewer khác người viết; PR mô tả schema/forms/UI/test. Không force-push main; merge commit giữ contributions. Không commit config.php,.env,storageDB/sessions/quota,secrets. CSS/framework dependency local có license. Mỗi người ghi đúng đóng góp, hiểu code để vấn đáp; không đẩy code người khác dưới tên mình.

Done: PHP xử lý thật, DB refresh giữ, quyền/CSRF đạt, lỗi hiển thị, tests có expected/actual, đã merge và docs cập nhật. Bản chạy SQLite không tự hoàn thành gate MySQL. AI unavailable không tự hoàn thành FR10.

## 12. Kiểm thử và hạn chế hiện tại

PHP lint và14 service checks trên SQLite đã đạt. Browser PHP: login/progress/quiz 5/5/complete/quizbuilder/publish/categoryrestrict/owner404/CSRF419/AI503 đạt; public pages390/768/1366 không overflow. Đây chưa phải MySQL/concurrency hoặc gọi Gemini thật. Các kiểm tra còn lại giao owner trước G1/G2: MySQL setup/FK/transaction, hai request đồng thời, revocation user multi-session, nội dung/quota AI thật, 100 courses / 500 lessons / 100 users p95, Compose clean build.

Catalog phân trang 12; Student/Instructor/Admin/roster phân trang mặc định 12, tối đa 50, kèm COUNT cùng bộ lọc và truy vấn tổng hợp progress/best để tránh N+1. Markdown hỗ trợ heading/paragraph/code, không full CommonMark; không raw HTML. Checkbox xác nhận được server kiểm thay modal JS. Form tìm kiếm submit thay live search (StudyMate không bắt buộc live search). Không chấp nhận fake auth/progress/quiz bằng localStorage.

## 13. Setup

README là nguồn lệnh duy nhất. Local: config.example.php→config.php; chọn mysql và credentials; php tools/setup.php; php -S localhost:8000 -t public. SQLite local: chọn sqlite rồi setup cùng lệnh. Docker Compose MySQL có config env và seed khi DB trống; Dockerfile/Compose đã có kiểm tra MySQL và image được pin digest trong đợt 27/10; xem FINAL_ACCEPTANCE.md. Không serve project root hoặc mở storage ra web. AI key backend only. Không reset DB tự động mỗi lần start.

## 14. Hồ sơ nộp

source sạch (clone có.git nếu dùng Git evidence), config.example.php,.env.example,schema/seed,README/Readme.txt, SRS 2.2/spec 2.2,collection/testreport/knownissues,video1080p ba người, rubric do thầy cấp, Screenshot.png Insights khi đủ evidence. FolderZIP `52400227_NguyenMinhQuan_52400258_LeNguyenVanAn_52400252_PhamVanTu`.

Bài frontend tuần này: PDF ảnh trang PHP có browser addressbar thật. PDF preview tự động chỉ viewport, không thay screenshottoolbar. Chụp tại /index.php?page=... trên máy; không ghép URL giả. Không tự khẳng định70% rubric. Quân đối chiếu yêu cầu thực và upload trước 23:00.

Không áp32chức năng note lên StudyMate. Quy định chung/rubric chưa xác nhận phải ghi phản hồi thực vào course-rules.md. Hai meaningful commits/người/tuần trong3 tuần là mục tiêu; nếu4 tuần liên tiếp áp dụng, lịch3 tuần không đủ, không tạo lịch sử giả. Không tự nhận điểm online/offline; Compose localhost không đồng nghĩa điểm deployment. Quy định sử dụngAI của môn do Quân xác minh.

## 15. Thay đổi và quyền quyết định

Quân quyết scope; Tú review DB/auth/PDO; An review layout. Baseline 2.2 PHP thay 2.1 React/Laravel. Thay scope/schema/contracts phải có issue: hiện trạng,lý do,FR/action/table,giờ tăng/giảm,task thay thế,test,Quân quyết và PR. Không chốt lại stack/role/quiz/quyền đã ghi; runtime key/quota/rubric là kiểm chứng thực tế, không thiết kế treo.


## Phụ lục bàn giao mã nguồn ngày 12 tháng 10

Tách layout HTML từ public/index.php sang app/layout.php do An phụ trách; public/index.php vẫn do Tú phụ trách. Tách panel AI từ app/views.php sang app/ai_view.php do Quân phụ trách. Tên hàm renderAiPanel và biến layout giữ nguyên luồng xử lý; phạm vi, người phụ trách module và mức giờ không đổi. Xem HUONG_DAN_GITHUB.md trong bộ bàn giao để tích hợp.


## Cập nhật bằng chứng 27/10/2026

Đọc FINAL_ACCEPTANCE.md và evidence/final cho kết quả mới; các đoạn báo cáo SQLite/ảnh 12–17/10 phía trên là lịch sử, không thay bằng chứng MySQL hiện tại. Docker dùng `GEMINI_MODEL`, nhận `AI_MODEL` cũ khi chưa đặt biến mới. `gemini-2.5-flash-lite` chỉ là mặc định dự phòng, không phải cam kết provider còn hỗ trợ. Chưa xác nhận provider thật hoặc toàn bộ quy trình nộp. README là nguồn lệnh setup/backup/restore/test/đóng gói.
