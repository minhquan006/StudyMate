# StudyMate Project Specification và kế hoạch triển khai

**Phiên bản:** 2.0 · **Ngày:** 11/10/2026 · **Leader:** Nguyễn Minh Quân 52400227  
**Thành viên:** Lê Nguyễn Vạn An 52400258; Phạm Văn Tú 52400252  
**Đầu vào đã xác nhận:** React + Laravel + MySQL; 15–20 giờ/người/tuần; hoàn thành trong bốn tuần.

Tài liệu này quy định đường triển khai, hợp đồng dữ liệu/API, người chịu trách nhiệm và điều kiện bàn giao. SRS 2.0 quy định hành vi sản phẩm. Đây là baseline để bắt tay làm, không phải danh sách các phương án đang cân nhắc. Không bổ sung tính năng ngoài baseline trong bốn tuần.

## 1. Các quyết định đã chốt

| Mã | Quyết định | Lý do và hệ quả |
|---|---|---|
| D01 | React SPA + Laravel REST API + MySQL, một repository | Ba người có thể làm song song; giữ hợp đồng API chung. |
| D02 | Sanctum SPA session cookie; không JWT | Một website cùng origin; không tự quản refresh token hoặc lưu bearer token trong browser. |
| D03 | Student tự đăng ký; Instructor/Admin tạo bằng seed | Không có quy trình xin quyền hoặc đổi role trong MVP. |
| D04 | Khóa học miễn phí, mỗi tài khoản một role | Không làm checkout, payment hoặc đa role. |
| D05 | Course → Chapter → Lesson; một quiz cuối course | Bỏ quiz ở lesson/chapter để giảm trạng thái và bảng quan hệ. |
| D06 | Một quiz có 5–20 câu, bốn lựa chọn/câu, một đáp án đúng | Chấm trên server; được làm lại; đạt từ 70%; dùng điểm tốt nhất. |
| D07 | Hoàn thành tất cả lesson rồi mới mở quiz | Hoàn tất nội dung và đạt quiz mới hoàn thành course. |
| D08 | Cấu trúc nội dung và đề bất biến sau enrollment đầu tiên | Không phải triển khai versioning đề, recalculation hoặc snapshot toàn bộ đề. |
| D09 | Markdown không raw HTML, video bằng YouTube ID; không upload | Giảm storage và xử lý file; văn bản luôn đủ để học. |
| D10 | AI chỉ đọc văn bản lesson hiện tại, yêu cầu độc lập | Không RAG, không vector DB, không lưu lịch sử AI trong DB. |
| D11 | Docker Compose cùng origin là đường chạy/bàn giao chuẩn | Không phụ thuộc hosting mua mới; có thể dùng cùng cấu hình trên server sẵn có. |
| D12 | Tạm loại certificate/review/wishlist/notification/gamification | Không xuất hiện trong menu, bảng dữ liệu hoặc luồng demo bắt buộc. |
| D13 | Quân quản trị nội dung + Admin + AI; Tú nền tảng + học + quiz; An Student UI | Giảm nghẽn backend và tránh hai người cùng viết một module. |

Mục tiêu là bản học tập nhỏ, chạy thật và giải thích được. Không cam kết loại bỏ mọi rủi ro; các trường hợp chưa được hỗ trợ đã được đưa ra ngoài phạm vi trong SRS. Quy định chấm/nộp bài riêng của giảng viên phải được Quân ghi nhận ở D1; FinalProject chỉ là tài liệu tham khảo, không tự động trở thành rubric của StudyMate.

## 2. Công nghệ và cấu trúc

### 2.1 Bộ công nghệ

| Thành phần | Chốt |
|---|---|
| Frontend | React 19, JavaScript ES modules, Vite 8, React Router 7 ở declarative mode |
| UI | Bootstrap 5.3 + CSS riêng; dùng một bộ spacing/color/type chung; không thêm UI framework thứ hai |
| HTTP và state | Axios instance duy nhất; AuthContext cho phiên; useState/useEffect cho màn hình; không Redux |
| Markdown | react-markdown với raw HTML tắt; chỉ cho link http/https; không dangerouslySetInnerHTML |
| Backend | Laravel 13, PHP 8.3, Composer 2; Sanctum 4; Eloquent, FormRequest, Policy, API Resources |
| DB | MySQL 8.4, InnoDB, utf8mb4; migrations + seed là nguồn cấu trúc chuẩn |
| Runtime JS | Node 22, tối thiểu 22.12; npm; package-lock.json |
| AI | OpenAI Responses API, model `gpt-4.1-mini`; Laravel HTTP client; không SDK/agent framework bổ sung |
| Kiểm thử | Laravel Feature Tests bằng PHPUnit, MySQL test riêng; smoke test UI thủ công có checklist; một Postman collection |
| Chạy ứng dụng | Docker Compose: nginx web, php-fpm app, mysql db; frontend build vào image web |

D1 Tú tạo bộ khung và khóa phiên bản patch thực tế trong composer.lock, package-lock.json và image tag/digest đã build thử. Cả nhóm dùng đúng lockfile; không dùng dependency/image `latest`, không tự nâng major trong dự án. Các major ở bảng trên là baseline, không phải yêu cầu chọn lại. Thay patch bảo mật phải qua PR và smoke test.

Tài liệu kỹ thuật chính thức để đối chiếu: [Laravel releases](https://laravel.com/framework/docs/releases), [Sanctum](https://laravel.com/framework/docs/sanctum), [Vite guide](https://vite.dev/guide/), [GPT-4.1 mini](https://developers.openai.com/api/docs/models/gpt-4.1-mini). Đã đối chiếu ngày 11/10/2026. Laravel 13 yêu cầu PHP từ 8.3; cấu hình Node nêu trên đáp ứng yêu cầu Vite. Phiên bản cài cụ thể và khả năng cấp quyền model được kiểm tra ở D1–D2.

### 2.2 Repository và quyền sở hữu file

```text
StudyMate/
  frontend/
    src/
      app/                 # An: router, AuthContext, route guards
      components/          # An: component dùng chung
      layouts/             # An: public/student/management layout
      features/
        auth/              # An
        catalog/           # An
        learning/          # An
        quiz/              # An
        student/           # An
        instructor/        # Quân
        admin/             # Quân
        ai/                # Quân
      services/api.js       # An: Axios và lỗi chuẩn
      assets/              # An: ảnh có quyền sử dụng
  backend/
    app/Http/Controllers/  # tách Public, Student, Instructor, Admin, Ai
    app/Http/Requests/
    app/Http/Resources/
    app/Models/
    app/Policies/
    app/Services/          # LearningService, QuizService, AiTutorService
    routes/api.php         # Tú điều phối; file route con theo module
    routes/api_student.php
    routes/api_instructor.php
    routes/api_admin.php
    routes/api_ai.php
    database/migrations/
    database/seeders/
    tests/Feature/
  infrastructure/          # Tú: Dockerfile, nginx, health checks
  docs/                    # Quân: SRS, specification, test/demo evidence
  tests/postman/           # Quân điều phối; mỗi người cập nhật API của mình
  compose.yaml             # Tú
  README.md                # Tú hướng dẫn chạy, Quân điều phối bản nộp
  .env.example
  .gitignore
```

Không tạo `/admin`, `/instructor`, `/ai` như ứng dụng riêng ở root. Models và migrations do Tú điều phối; Quân đề xuất thay đổi qua PR, không tự tạo bảng trùng. Quân dùng component/layout của An; thay component chung phải được An review. Mỗi người được sửa file cần thiết qua PR, ownership là trách nhiệm review chứ không phải cấm phối hợp.

### 2.3 Cách chạy và xác thực

- Production/demo Compose dùng một origin: `/` phục vụ React; `/api/v1/*` và `/sanctum/*` chuyển vào Laravel. Refresh route frontend trả index.html; API lỗi không được trả index.html.
- Dev: Vite proxy `/api` và `/sanctum` vào Laravel. Dùng thống nhất hostname `localhost`; không trộn localhost và 127.0.0.1. Stateful domains, session domain và port đọc từ env.
- Axios bật `withCredentials` và hỗ trợ XSRF. Trước register/login gọi `GET /sanctum/csrf-cookie`; sau đó gọi auth endpoint. Laravel bật middleware stateful API của Sanctum. Xác minh POST thực sự kiểm tra CSRF.
- Route thay đổi trạng thái đều là POST/PUT/PATCH/DELETE; không dùng GET để enrollment hoặc mark complete. Tất cả API cần phiên dùng `auth:sanctum`, active middleware rồi Policy.
- Cookie phiên HttpOnly, SameSite=Lax; Secure khi HTTPS. Cookie XSRF được JS đọc theo cơ chế framework; không nhầm nó với cookie phiên. Môi trường localhost HTTP có cấu hình riêng.
- UI guard chỉ giúp điều hướng. Policy trên server kiểm tra role, ownership, enrollment. Đăng xuất/401/403 tài khoản khóa xóa AuthContext và dữ liệu riêng khỏi UI.
- Không ghép URL/port trực tiếp trong component. `.env.example` không chứa key thật.

## 3. Hợp đồng dữ liệu

### 3.1 Quy ước chung

Tất cả bảng nghiệp vụ có bigint `id`, timestamps UTC. API trả ISO 8601; UI hiển thị múi giờ Asia/Ho_Chi_Minh. Dùng `*_id` nhất quán. Không dùng soft delete trong MVP. Bảng framework gồm sessions/cache/cache_locks/migrations; không thêm jobs nếu không chạy queue.

| Bảng | Trường nghiệp vụ bắt buộc và nullable | Ràng buộc |
|---|---|---|
| users | name, email, password, role, is_active | email lowercase unique; role enum student/instructor/admin |
| categories | name | unique name; 2–80 ký tự |
| courses | instructor_id, category_id, title, description, cover_key, status | FK users/categories; status draft/published/archived; index status + created_at |
| chapters | course_id, title, position | FK course; position > 0 |
| lessons | chapter_id, title, body, youtube_video_id nullable, position | FK chapter; body 50–12.000 ký tự; video ID 11 ký tự nếu có |
| enrollments | user_id, course_id, last_lesson_id nullable, completed_at nullable | unique(user_id, course_id); last_lesson phải thuộc course |
| lesson_progress | user_id, lesson_id, completed_at | unique(user_id, lesson_id); server kiểm tra enrollment |
| quizzes | course_id | unique course_id; không lưu cấu hình timer/pass threshold vì giá trị đã cố định |
| questions | quiz_id, prompt, explanation nullable, position | tối đa 20 câu/quiz qua validation; order position rồi id |
| answers | question_id, text, is_correct, position | đúng bốn câu trả lời và một đáp án đúng qua validation |
| quiz_attempts | user_id, quiz_id, request_id UUID, submission_hash char(64), correct_count, total_questions, score_percent decimal(5,2), passed, submitted_at | unique(user_id, request_id); index(user_id, quiz_id) |
| quiz_answers | attempt_id, question_id, answer_id nullable, is_correct | unique(attempt_id, question_id); answer thuộc đúng question |

Không tạo students/instructors tables riêng. Không lưu course progress percent hoặc best score dưới dạng cột phải đồng bộ; tính bằng query/service. `enrollments.completed_at` là dấu mốc hoàn thành, chỉ set một lần khi đủ điều kiện. `quiz_answers.is_correct` là kết quả chấm của lần nộp.

Tên/email/mật khẩu theo SRS BR01: tên 2–80 ký tự; email tối đa 254, trim/lowercase; mật khẩu tối thiểu 10 ký tự và tối đa 72 byte UTF-8 (validation byte riêng cho bcrypt). `cover_key` mặc định `default`, allowlist `default`, `web`, `programming`, `database`; assets do An chuẩn bị. Tên và mô tả course, body lesson theo BR06. Không chấp nhận giá trị enum hoặc ID ngoài phạm vi.

### 3.2 Quan hệ và chính sách xóa

- User 1–n courses qua instructor_id; User n–n Course qua enrollments.
- Course 1–n Chapter; Chapter 1–n Lesson; Course 1–1 Quiz; Quiz 1–n Question; Question 1–n Answer.
- User 1–n QuizAttempt; QuizAttempt 1–n QuizAnswer; User n–n Lesson qua lesson_progress.
- FK user/category và các FK từ lịch sử: restrict. `last_lesson_id`: nullable, restrict deletion khi được tham chiếu. Không endpoint xóa user/enrollment/progress.
- Course → chapter → lesson và course → quiz → question → answer dùng cascade để xóa draft; service chặn xóa course trừ draft chưa enrollment/attempt. Quiz nháp được thay nguyên bộ trong transaction; không giữ ID câu hỏi cũ.
- Sửa cấu trúc chỉ khi draft và chưa enrollment; publish/enroll/sửa cấu trúc khóa cùng hàng course trong transaction để tránh race.
- Hai request hoàn thành cùng lesson vẫn cho kết quả một progress. Hai request nộp cùng UUID bị unique constraint chặn trùng; khi gặp conflict phải đọc attempt đã commit và trả đúng hợp đồng.
- Mọi query kết quả riêng phải bắt đầu từ user/owner đã xác thực, không lấy record tùy ý rồi tin user_id từ body.

## 4. Hợp đồng API baseline

### 4.1 Quy ước response và lỗi

Prefix `/api/v1`, JSON snake_case. Axios đặt `Accept: application/json`. ID là số; request_id là UUID; boolean JSON thật. Input chứa trường ngoài allowlist bị bỏ qua, riêng role/status/score hoặc owner trái quyền bị validation từ chối theo request. Không mass assign dữ liệu client.

Response thành công một đối tượng: `{"data": {...}}`. Danh sách: `{"data": [...], "meta": {"page":1,"per_page":12,"total":24,"last_page":2}}`. Không bọc thêm `success` ở module khác. POST tạo mới 201; đọc/sửa 200; xóa/logout 204 không body. Enrollment idempotent trả 200 nếu đã tồn tại; quiz retry trả 200, lần nộp mới 201.

Lỗi: `{"message":"Thông báo tiếng Việt", "code":"ENROLLMENT_REQUIRED", "errors":{}}`. Validation 422 có errors theo trường; middleware/exception handler chuẩn hóa lỗi, không trả HTML hoặc stack trace. Code tối thiểu:

| HTTP | code | Tình huống |
|---|---|---|
| 401 | UNAUTHENTICATED / INVALID_CREDENTIALS | Không có phiên; login sai |
| 403 | FORBIDDEN / ACCOUNT_DISABLED / ENROLLMENT_REQUIRED / LESSONS_INCOMPLETE | Sai role, khóa tài khoản, chưa enroll, chưa đủ lesson |
| 404 | NOT_FOUND | Không tồn tại hoặc tài nguyên riêng không thuộc người gọi |
| 409 | COURSE_LOCKED / COURSE_NOT_OPEN / REQUEST_ID_CONFLICT / CATEGORY_IN_USE | Vi phạm vòng đời, không mở tuyển, UUID khác payload, category đang dùng |
| 419 | CSRF_EXPIRED | Token/phiên hết hạn; UI yêu cầu đăng nhập lại, không tự nộp lại quiz |
| 422 | VALIDATION_FAILED | Input hoặc điều kiện publish sai |
| 429 | RATE_LIMITED / AI_QUOTA_EXCEEDED | Quá số lượt; trả Retry-After khi xác định được |
| 503/504 | AI_UNAVAILABLE / AI_TIMEOUT | AI không sẵn sàng hoặc hết thời gian chờ |
| 500 | INTERNAL_ERROR | Thông báo chung; log request ID để tra cứu |

Collection dùng `page`, `per_page` (mặc định 12, tối đa 50), sort mới nhất theo created_at rồi id giảm dần. Chapters/lessons/questions dùng position rồi id tăng dần. Catalog `q` tìm chuỗi trong title, không phân biệt hoa/thường, trim, tối đa 100 ký tự; `%` và `_` được escape để là ký tự tìm kiếm bình thường. `category_id` chỉ một category. Không có sort/filter tùy biến khác.

### 4.2 Tài khoản và API công khai do Tú sở hữu

| Method và path | Input chính | Response và quyền |
|---|---|---|
| GET /health | Không | `{data:{status:"ok"}}`; kiểm tra app/DB, lỗi DB trả 503, không lộ cấu hình |
| POST /auth/register | name,email,password,password_confirmation | User tối thiểu; tạo Student và login; tái tạo session |
| POST /auth/login | email,password | User tối thiểu; active; sai thông tin trả chung 401 |
| POST /auth/logout | Không | 204; phiên hiện tại bị hủy |
| GET /auth/me | Không | id,name,email,role,is_active; yêu cầu login |
| GET /categories | page,per_page | id,name; public |
| GET /courses | q,category_id,page,per_page | CourseCard của published |
| GET /courses/{course} | Không | CourseDetail published; nếu không published trả 404 ở public endpoint |

`GET /sanctum/csrf-cookie` không có prefix /api/v1 và theo response framework 204. Rate limit login 5/phút theo email+IP, register 3/phút/IP. Login/register có CSRF nhưng không đòi đã login.

### 4.3 API Student do Tú sở hữu

Tất cả yêu cầu role student và active; service xác định user từ session.

| Method và path | Input chính | Response và quy tắc |
|---|---|---|
| POST /courses/{course}/enrollments | Body rỗng | Enrollment, 201/200; mới chỉ published |
| GET /student/courses | page,per_page | EnrollmentSummary gồm CourseCard, progress, best score |
| GET /student/courses/{course} | Không | Metadata + outline và tiến độ của khóa đã enroll, kể cả archived |
| GET /student/lessons/{lesson} | Không | LessonContent; không cập nhật last lesson ngầm bằng GET |
| PUT /student/courses/{course}/last-lesson | lesson_id | EnrollmentSummary; lesson phải thuộc course |
| PUT /student/lessons/{lesson}/completion | Body rỗng | EnrollmentSummary; idempotent |
| GET /student/courses/{course}/quiz | Không | QuizForStudent; đủ tất cả lesson; không đáp án đúng/explanation |
| POST /student/quizzes/{quiz}/attempts | request_id, answers:[{question_id,answer_id}] | AttemptResult; câu bỏ trống được phép thiếu hoặc null |
| GET /student/quizzes/{quiz}/attempts | page,per_page | Lịch sử của mình; không trường nội dung đáp án ở list |
| GET /student/attempts/{attempt} | Không | AttemptResult đầy đủ của mình |
| GET /student/dashboard | Không | enrolled_count, completed_count; danh sách khóa dùng /student/courses |

Chuẩn hóa câu trả lời theo toàn bộ question của quiz, câu bỏ trống là null, sort theo question_id; hash SHA-256 trên quiz_id + dữ liệu chuẩn hóa. Request_id không đưa vào hash. AttemptResult trả attempt_id, request_id, quiz_id, correct_count, total_questions, score_percent, passed, submitted_at, answers gồm selected_answer_id, correct_answer_id, explanation và enrollment summary.

### 4.4 API Instructor do Quân sở hữu

Tất cả endpoint yêu cầu role instructor; khóa phải do người gọi sở hữu. Payload không nhận instructor_id. Admin không dùng endpoint này.

| Method và path | Input chính | Response và quy tắc |
|---|---|---|
| GET /instructor/dashboard | Không | course_count, enrollment_count |
| GET /instructor/courses | page,per_page | Khóa của mình ở mọi trạng thái |
| POST /instructor/courses | title,description,category_id,cover_key | CourseDetail draft |
| GET /instructor/courses/{course} | Không | Metadata, structure_locked, outline và cấu hình đề |
| PATCH /instructor/courses/{course} | title,description,category_id,cover_key | Metadata; category/cover thay được; không nhận status/owner |
| DELETE /instructor/courses/{course} | Không | 204; chỉ draft chưa enrollment |
| POST /instructor/courses/{course}/publish | Không | CourseDetail; validation publish; draft/archived → published |
| POST /instructor/courses/{course}/unpublish | Không | published → draft; chỉ chưa enrollment |
| POST /instructor/courses/{course}/archive | Không | published → archived; lặp archived trả hiện trạng; draft trả 409 |
| POST /instructor/courses/{course}/chapters | title,position | Chapter; cấu trúc còn mở |
| PATCH /instructor/chapters/{chapter} | title,position | Title sửa được; position chỉ khi cấu trúc mở |
| DELETE /instructor/chapters/{chapter} | Không | 204; cấu trúc mở; xóa lessons nháp đi kèm |
| POST /instructor/chapters/{chapter}/lessons | title,body,youtube_video_id,position | LessonContent; cấu trúc mở |
| GET /instructor/lessons/{lesson} | Không | LessonContent để edit/preview |
| PATCH /instructor/lessons/{lesson} | title,body,youtube_video_id,position | Nội dung được sửa; position chỉ khi cấu trúc mở |
| DELETE /instructor/lessons/{lesson} | Không | 204; cấu trúc mở |
| GET /instructor/courses/{course}/quiz | Không | Đề đầy đủ kể cả is_correct; null nếu draft chưa có quiz |
| PUT /instructor/courses/{course}/quiz | questions:[{prompt,explanation,answers:[{text,is_correct}]}] | Thay toàn bộ đề trong transaction; thứ tự mảng thành position |
| GET /instructor/courses/{course}/students | page,per_page | user id/name, progress, best_score_percent, completed_at |
| GET /instructor/courses/{course}/students/{student}/attempts | page,per_page | Điểm/lần nộp của Student trong đúng khóa; không endpoint xem đáp án cá nhân |

Publish yêu cầu đủ chapter/lesson/quiz hợp lệ như SRS. Instructor không cần endpoint xóa quiz riêng: draft sửa bằng PUT, xóa course sẽ xóa quiz. Không frontend editor câu hỏi theo mỗi endpoint nhỏ. Chỉnh đề ở một form và lưu một lần. Quân cung cấp quiz builder; Tú chỉ sở hữu API người học và chấm bài.

### 4.5 API Admin và AI do Quân sở hữu

| Method và path | Input chính | Response và quyền |
|---|---|---|
| GET /admin/dashboard | Không | user_count, course_count, published_course_count |
| GET /admin/users | page,per_page,q | id,name,email,role,is_active; q tìm name/email |
| PATCH /admin/users/{user}/status | is_active | Không thao tác với Admin; khi khóa xóa mọi sessions của user; trả User |
| GET /admin/courses | page,per_page | Khóa mọi trạng thái, instructor name, status |
| GET /admin/courses/{course} | Không | Metadata/outline để preview; không progress học viên |
| GET /admin/lessons/{lesson} | Không | LessonContent preview, không ghi progress |
| GET /admin/courses/{course}/quiz | Không | Đề preview đầy đủ; không sửa/nộp |
| POST /admin/courses/{course}/archive | Không | published → archived; lặp trả hiện trạng; draft 409 |
| POST /admin/categories | name | Category; unique name |
| PATCH /admin/categories/{category} | name | Category |
| DELETE /admin/categories/{category} | Không | 204 nếu chưa được dùng, không thì 409 |
| POST /ai/lesson-help | lesson_id,mode:ask hoặc summarize,question | Student đã enroll; ask cần question, summarize không cần |

Admin danh sách categories dùng GET /categories. AI trả `{data:{lesson_id,lesson_title,mode,answer}}`. Rate/quota theo SRS; không gửi stream. Backend dùng model/key từ env, trường `store:false` khi gọi Responses API, không bật công cụ web/file. AiTutorService lấy body của lesson, dùng instruction tách khỏi input người dùng. Quân cung cấp một component nhận lessonId/lessonTitle; An chỉ nhúng component đó vào Learning.

### 4.6 Resource dùng chung

| Resource | Trường |
|---|---|
| User | id,name,email,role,is_active; không password/remember token |
| CourseCard | id,title,description,cover_key,category:{id,name},instructor:{id,name},status |
| CourseDetail | CourseCard + chapters:[{id,title,position,lessons:[{id,title,position}]}], total_lessons; public không body |
| LessonContent | id,course_id,chapter_id,title,body,youtube_video_id,position |
| EnrollmentSummary | id,course_id,enrolled_at (mapped created_at),completed_at,last_lesson_id,completed_lessons,total_lessons,lesson_progress_percent,best_score_percent nullable |
| QuizForStudent | id,course_id,questions:[{id,prompt,answers:[{id,text}]}] |

Danh sách Student course thêm `course:CourseCard` vào EnrollmentSummary. Student course detail trả `{course:CourseDetail,enrollment:EnrollmentSummary,completed_lesson_ids:[]}`. Instructor course detail thêm `structure_locked` và `has_quiz`; quiz chi tiết đọc endpoint quiz. Trường hiển thị chưa có giá trị dùng null, không thay bằng số giả. Thay response shape là thay contract, bắt buộc cập nhật ví dụ và người dùng API review.

## 5. Màn hình và luồng giao diện

| Route frontend | Người phụ trách | Nội dung |
|---|---|---|
| / và /courses | An | Home gọn, catalog dùng cùng CourseCard, tìm/lọc/phân trang |
| /courses/:id | An | Chi tiết published, outline; khách login rồi quay lại; Student enroll |
| /login, /register | An | Validation, lỗi API, CSRF; login redirect theo role |
| /student | An | Dashboard và My Courses chung một trang |
| /student/courses/:id/learn | An | Outline, bài, hoàn thành, Continue; query lessonId; panel AI của Quân |
| /student/courses/:id/quiz | An | Quiz một trang, xác nhận nộp, cảnh báo mất lựa chọn |
| /student/attempts/:id | An | Kết quả, giải thích, làm lại; lịch sử tích hợp cùng khu vực quiz |
| /profile | An | Chỉ xem tên/email/role, nút logout; không edit |
| /instructor | Quân | Dashboard và list khóa chung |
| /instructor/courses/new và /instructor/courses/:id/edit | Quân | Một editor dùng chung create/edit, tab thông tin/nội dung/quiz/học viên |
| /admin | Quân | Dashboard với tabs users/courses/categories; preview trong màn hình quản lý |
| /* | An | 404; xử lý 403 và hết phiên bằng component chung |

Không xây riêng Results/Progress/Reports/Certificates/Wishlist. Không dựng Learning UI thứ hai cho preview: Quân dùng component đọc lesson của An với `readOnly=true`, không có completion/AI/submit. An phải bàn giao LessonViewer component trước D8.

Layout: public navbar; student sidebar thu gọn; management sidebar dùng chung. Bootstrap modal xác nhận xóa/archive/khóa tài khoản và nộp quiz. Mọi thao tác lưu có loading và khóa nút tránh nhấn lặp; đây chỉ là UX, backend vẫn chống trùng. Không animation phức tạp. Màu, font, button và spacing do An chốt D2, cả nhóm dùng cùng token CSS.

## 6. Phân công và quyền quyết định

| Phần việc | Owner | Reviewer bắt buộc | Handoff |
|---|---|---|---|
| DB, model, migration, seed nền tảng | Tú | Quân | Schema và seed cho mọi module D3 |
| Compose, routes tổng, auth, middleware/Policy nền | Tú | Quân | Auth contract + môi trường chạy D4 |
| Public API, Student learning/progress/dashboard | Tú | An | API thật D8–D12 |
| Quiz lấy đề/nộp/kết quả/idempotency | Tú | Quân | Quiz service và tests D15 |
| Instructor course/chapter/lesson/quiz-builder API | Quân | Tú | CRUD D8; publish và editor D12 |
| Admin API/UI | Quân | Tú | D16 |
| AI API/component/quota | Quân | Tú | Probe D2, component tích hợp D17 |
| Design system, Axios, route/auth shell | An | Quân | D3–D4 |
| Student catalog/learning/quiz/result/dashboard | An | Tú | Từng luồng trong tuần 2–3 |
| Responsive/layout/lesson viewer chung | An | Quân | Viewer D8; responsive nghiệm thu D23 |
| Integration test, báo cáo và demo | Quân điều phối | Cả ba | Owner tự test; test chéo mỗi tuần |
| Quyết định scope và release | Quân | Ý kiến người chịu tác động | Có log quyết định trong issue/PR |

Quân không phải người viết mọi test hoặc sửa mọi lỗi. Bug thuộc module nào thì owner module sửa. An kiểm tra frontend request và lỗi hiển thị; Tú kiểm tra dữ liệu/phiên; Quân kiểm tra quản trị/AI và toàn luồng. Khi owner bị kẹt quá một buổi, báo issue với lỗi tái hiện, kết quả đã thử và người cần hỗ trợ; không tự đổi contract để né lỗi.

## 7. Kế hoạch bốn tuần và ngân sách thời gian

### 7.1 Lịch nội bộ

Ngày bắt đầu kế hoạch **12/10/2026**, D1; D28 là **08/11/2026**. Đây là lịch nội bộ đề xuất để dùng ngay, không khẳng định hạn nộp môn học. Nếu ngày bắt đầu thực tế khác, Quân dịch toàn bộ mốc cùng nhau một lần; thứ tự phụ thuộc giữ nguyên.

Năng lực bảo thủ: 3 người × 15 giờ × 4 tuần = **180 giờ**. Phần việc đã phân bổ 13 giờ/người/tuần = **156 giờ**, để **24 giờ dự phòng** cho lỗi/tích hợp. Giờ dự phòng không dùng để thêm tính năng. Thời gian review, họp ngắn và chuẩn bị demo đã nằm trong 13 giờ; bảng là ước lượng lập kế hoạch, không phải bảo đảm tốc độ lập trình.

| Tuần | Tú — 13 giờ | An — 13 giờ | Quân — 13 giờ | Gate |
|---|---|---|---|---|
| W1 12–18/10 | Khung/Compose 4h; schema/seed 4h; auth/policy 4h; review 1h | UI kit/router/Axios 4h; auth 3h; catalog/detail 4h; review 2h | Issues/contract 3h; AI probe 1h; Instructor shell + course CRUD 7h; review 2h | G1: ba role login, catalog API thật, draft lưu DB, Compose chạy |
| W2 19–25/10 | Public/enrollment 3h; learning/progress 4h; quiz nền 4h; review/test 2h | Catalog tích hợp 2h; Learning/viewer/progress 6h; Quiz UI 3h; review 2h | Chapter/lesson/quiz editor + API 8h; publish/archive 2h; test/review 3h | G2: tạo course có đề, publish, enroll, học và progress bền vững |
| W3 26/10–01/11 | Quiz/result/idempotency 5h; dashboard/roster queries hỗ trợ 2h; test quyền 3h; tích hợp 3h | Quiz/result thật 4h; dashboard 2h; tích hợp AI 1h; responsive/error 3h; review 3h | Admin 3h; AI/quota 4h; roster UI/API 2h; kiểm thử xuyên luồng 4h | G3: mọi FR có đường chạy; đóng băng tính năng |
| W4 02–08/11 | Test DB/concurrency 4h; clean setup/README 3h; sửa lỗi 3h; demo 3h | Responsive/test chéo 4h; sửa UI 4h; bằng chứng/demo 5h | Test tích hợp/AI lỗi 3h; sửa lỗi 3h; đối chiếu tài liệu 2h; video/demo/release 5h | G4: bản nộp đã được clone sạch và diễn tập |

### 7.2 Các mốc nhỏ và phụ thuộc

| Mốc | Đầu ra | Phụ thuộc |
|---|---|---|
| D1–D2 | Quân tạo board; Tú khóa runtime và DB; An chốt UI; Quân gọi AI thật bằng env | Key do Quân cấu hình riêng; không gửi trong chat/repo |
| D3–D4 | Schema + seed + API examples + auth hoàn chỉnh; component chung | Contract mục 4; cả nhóm review một lần |
| D7 | G1 trên main; người thứ hai chạy Compose | Auth + migration + public catalog |
| D8 | LessonViewer của An; Quân có chapter/lesson CRUD; Tú có enroll | Shared resource shape |
| D12 | Tạo/publish khóa từ UI; Student học và giữ progress | Đề nháp hợp lệ, enrollment và lesson APIs |
| D14 | G2; review khả năng hoàn thành quiz/AI đúng hạn | Không nhận feature mới |
| D15–D17 | Nộp quiz + Admin + AI tích hợp thật | LearningService và provider probe |
| D21 | G3; tag v0.9.0; feature freeze | Mọi FR có ít nhất một happy path |
| D23–D25 | Kiểm thử quyền/race/responsive, clone sạch, README | Chỉ sửa lỗi/release docs |
| D26–D28 | Diễn tập, video/slide, đối chiếu rubric thực tế, tag v1.0.0 | Không lỗi nghiêm trọng; đóng gói |

Frontend được mock theo chính xác shape mục 4 trong W1; bỏ mock ở đường chạy thật chậm nhất G2. Mocks/test doubles không được trình bày là chức năng đã tích hợp.

### 7.3 Backlog đưa lên GitHub Issues

| ID | Owner | Công việc | Phụ thuộc | Hoàn thành |
|---|---|---|---|---|
| T01 | Tú | Bootstrap runtime, Compose, health | Không | Người thứ hai chạy app và DB |
| T02 | Tú | 12 bảng, indexes, seed | T01 | migrate + seed + FK đúng |
| T03 | Tú | Auth, active, role/policies nền | T02 | FR01/02 tests đạt |
| A01 | An | UI kit, router, Axios, AuthContext | T01 | Login và lỗi dùng component chuẩn |
| Q01 | Quân | Board, contract examples, AI probe | T01 | Lưu quyết định, provider trả thật |
| T04 | Tú | Public catalog + enroll | T02/T03 | Lọc và enroll idempotent |
| A02 | An | Home/catalog/detail/auth | A01/T03/T04 | Luồng khách → login → enroll |
| Q02 | Quân | Course/chapter/lesson CRUD API/UI | T02/T03/A01 | Owner đúng, edit draft chạy thật |
| Q03 | Quân | Quiz builder, publish/archive | Q02 | Đề hợp lệ; khóa cấu trúc đúng |
| T05 | Tú | Learning/progress/last lesson | T04/Q02 | Hoàn thành lặp và refresh đúng |
| A03 | An | LessonViewer + Learning | A02/T05 | Học responsive, preview tái dùng |
| T06 | Tú | Quiz submit/result service | Q03/T05 | Điểm, ownership, retry UUID đúng |
| A04 | An | Quiz/result/history UI | A03/T06 | Nộp, làm lại, xem giải thích |
| T07 | Tú | Student dashboard và CompletionService | T05/T06 | completed_at và best score đúng |
| Q04 | Quân | Admin API/UI và roster Instructor | Q02/T05/T06 | Khóa user, category restrict, roster |
| Q05 | Quân | AiTutorService + quota + component | Q01/T03/T05 | Ask/summarize thật và failure tests |
| A05 | An | Dashboard/profile/AI integration | T07/Q05 | Không hardcode dữ liệu |
| QA01 | Mỗi owner | Test module, collection, PR evidence | Module tương ứng | Test trong PR, reviewer chạy |
| QA02 | Cả nhóm | Test chéo, clean install, demo | Tất cả trên | G4 đạt |

## 8. Git và chống xung đột

- `main` luôn chạy được. Branch theo task: `feat/T05-progress`, `fix/T06-submit-retry`, không dùng branch riêng theo người kéo dài bốn tuần.
- Một issue có một owner và một reviewer, ghi FR liên quan, đầu vào/đầu ra và test. Hai người không tự nhận cùng task.
- PR nhỏ, mục tiêu review trong 24 giờ; owner cập nhật branch từ main mỗi ngày làm việc. Không chờ hoàn tất cả khu vực mới merge.
- PR gồm mục đích, API/DB thay đổi, ảnh UI nếu có và cách kiểm thử. Cần một reviewer khác owner chấp nhận trước merge. Quân không tự duyệt PR của mình.
- Dùng merge commit để giữ commit đóng góp gốc, không force-push main. Commit có nội dung thực, không chia vụn để tạo số lượng. Bằng chứng đóng góp tuân theo quy định môn học mà Quân xác nhận.
- Tú điều phối `routes/api.php`, models, migrations, compose và lockfiles. Quân sửa route con/module của mình. An điều phối router, api.js và components chung.
- Migrations đã merge không sửa lại; thêm migration mới. Không commit SQL dump thay cho migration. Không chạy migrate:fresh trên shared DB. Mỗi người dùng DB local; bộ seed chung tái lập dữ liệu.
- Khi sửa contract/schema: mở issue nêu impact → người dùng API review → cập nhật SRS/spec/examples và code trong cùng PR → chạy test cả producer/consumer. Quân quyết scope, Tú review tính toàn vẹn dữ liệu, An review giao diện chịu ảnh hưởng.
- Cập nhật ngắn mỗi ngày có làm: đã xong / tiếp theo / đang kẹt. Hai buổi tích hợp 20 phút/tuần, không họp để lặp lại quyết định đã ghi.

## 9. Definition of Done và bộ kiểm thử

Một task chỉ chuyển Done khi: code đã merge, UI gọi API thật nếu có UI, dữ liệu đúng sau refresh, authorization ở backend, trạng thái lỗi có phản hồi, tests liên quan đạt, reviewer đã kiểm tra và docs/contract cập nhật. Module chỉ có giao diện hoặc endpoint chưa nối chưa được tính Done.

| Test ID | Kiểm tra | Owner kiểm thử / reviewer | Liên kết |
|---|---|---|---|
| AT01 | Register role luôn Student; email trùng; login/logout; CSRF POST bị chặn khi thiếu token | Tú / Quân | FR01–02 |
| AT02 | Student gọi Instructor API; Instructor A sửa khóa B; đổi attempt ID của người khác | Mỗi backend owner / người còn lại | FR02/07 |
| AT03 | Public response không có body/quiz answers/email học viên | Tú / An | FR02–03 |
| AT04 | Hai enrollment đồng thời chỉ một record; archived chặn enroll mới nhưng người cũ học được | Tú / Quân | FR04 |
| AT05 | Mark complete lặp, last lesson ngoài khóa, refresh tiến độ | Tú / An | FR05 |
| AT06 | Chưa đủ lesson bị chặn quiz; 3/5 sai ngưỡng, 4/5 đạt; client gửi score giả | Tú / Quân | FR06 |
| AT07 | Retry cùng UUID cùng payload một attempt; payload khác 409; option ngoài question bị chặn | Tú / Quân | FR06 |
| AT08 | Làm lại thấp điểm không mất completed_at; best score đúng | Tú / An | FR06/09 |
| AT09 | Publish thiếu đề bị chặn; sửa/xóa cấu trúc sau enroll bị chặn; enrollment đồng thời unpublish an toàn | Quân / Tú | FR07 |
| AT10 | Khóa tài khoản đang login có hiệu lực request kế; không khóa Admin; category đang dùng không xóa | Quân / Tú | FR08 |
| AT11 | AI sai lesson/enrollment bị chặn; key không ở bundle; quota/rate limit/timeout; provider thật theo bài | Quân / Tú | FR10 |
| AT12 | 390/768/1366 px; keyboard; loading/empty/422/401/503; refresh route sâu | An / Quân | FR11 |
| AT13 | Clone sạch + Compose + seed + ba role; không thao tác tay ngoài README | An / Tú | FR12 |
| AT14 | Dashboard so với seed/DB; published/archive đúng catalog; phân trang | An / Tú | FR03/09 |
| AT15 | 30 request warm catalog, p95 <1s trên data 100/500/100; ghi cấu hình máy | Tú / Quân | NFR02 |

Tests server tự động tập trung vào quyền, quan hệ, điểm và transaction; không viết test chỉ lặp lại getters hoặc markup. Dùng MySQL `studymate_test`, không SQLite để bỏ sót khác biệt constraint. Không dùng test database có dữ liệu thật. Với concurrency, chạy hai request thật có barrier/harness hoặc Postman hai session, kiểm DB; không coi hai test tuần tự là bằng chứng đồng thời.

AI integration tests dùng HTTP fake để kiểm lỗi và quota, cộng ít nhất một lần gọi provider thật ask và summarize cho hai lesson. Fake không thay thế bằng chứng tích hợp thật. Nếu key chưa dùng được, đánh dấu FR10 chưa nghiệm thu và Quân xử lý ở W1, không để tới W4.

Gate release: không còn P0 (mất/lộ dữ liệu hoặc bypass quyền) hoặc P1 (chặn luồng chính, chấm sai, không cài được). P2 nhỏ có thể ghi known issues nếu không chặn nghiệm thu. Khi G2 trễ: dùng dự phòng, đơn giản hóa bố cục/ảnh/biểu đồ; không cắt xác thực, quyền, idempotency hoặc điều kiện đạt. Không tự bỏ FR bắt buộc để báo hoàn thành; thay baseline phải có issue và Quân quyết định rõ.

## 10. Setup và vận hành bàn giao

### 10.1 Biến môi trường

| Biến | Mục đích |
|---|---|
| APP_ENV, APP_DEBUG, APP_URL, APP_KEY | Môi trường Laravel; APP_KEY tạo riêng, debug false bản demo |
| DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD | MySQL; tên service db trong Compose |
| SESSION_DRIVER=database, SESSION_LIFETIME=120 | Phiên lưu DB, idle timeout |
| SESSION_DOMAIN, SESSION_SECURE_COOKIE, SANCTUM_STATEFUL_DOMAINS | Cookie/CSRF theo hostname demo |
| CACHE_STORE=database | Rate limit, quota và cache locks; dùng chung mọi request |
| AI_ENABLED, OPENAI_API_KEY, AI_MODEL=gpt-4.1-mini | Backend only; không biến VITE_ chứa secret |
| AI_USER_DAILY_LIMIT=20, AI_GLOBAL_DAILY_LIMIT=100, AI_PER_MINUTE=5 | Quota theo ngày Asia/Ho_Chi_Minh; atomic |
| AI_TIMEOUT_SECONDS=25, AI_MAX_OUTPUT_TOKENS=600 | Ràng buộc gọi provider |
| DEMO_PASSWORD | Mật khẩu seed demo do Quân cấp riêng; không mật khẩu thật |
| WEB_PORT | Port host do Compose đọc; component không hardcode |

README phải nói rõ biến ở root Compose và biến Laravel được map thế nào. Không giữ hai bản env bí mật khác nhau mà không chỉ ra nguồn ưu tiên. `.env.example` chỉ placeholder; không chạy seed demo trên môi trường production có người dùng thật.

### 10.2 Hợp đồng lệnh mà Tú phải hiện thực

Từ clone sạch, README hướng dẫn: tạo env từ example và nhập cấu hình → `docker compose build` → `docker compose up -d` → chạy `php artisan key:generate` trong app nếu chưa có key persistent → `php artisan migrate --force` → `php artisan db:seed --class=DemoSeeder` → kiểm `/api/v1/health` và login. Tú phải bảo đảm key tạo ra được lưu bền trong cấu hình/volume và không đổi khi restart; không chạy key:generate mỗi lần container khởi động. Các lệnh này là hợp đồng bàn giao, chỉ đánh dấu đã chạy sau AT13.

Container app phải có dependencies; image web có React build; nginx chuyển PHP tới app và route React về index.html đúng cách. DB có healthcheck, app chờ DB sẵn sàng, DB dùng named volume. Không mở cổng MySQL ra internet. HTTPS chỉ cần khi triển khai công khai; bản localhost nêu rõ phạm vi demo. Không mua dịch vụ hoặc công bố website nếu chưa có quyết định riêng.

Nếu cần public demo và nhóm đã có server, dùng cùng image/cấu hình + HTTPS reverse proxy; Quân xác nhận hạ tầng ở W1. Đây không phải feature bắt buộc hoặc lý do thay kiến trúc. Không tự thêm nền tảng hosting mới cuối dự án.

### 10.3 Dữ liệu, reset và AI

Seed đúng SRS: hai Student, hai Instructor, một Admin, ba course có chapter/lesson/quiz; dữ liệu mẫu thể hiện cả draft, chưa enroll và đang học. README ghi email demo và cách nhận DEMO_PASSWORD, không dùng thông tin tài khoản cá nhân. Reset destructive bằng migrate:fresh --seed chỉ dành local/test, phải gắn cảnh báo rõ. Trước demo sao lưu MySQL bằng mysqldump và thử restore vào DB khác một lần do Tú phụ trách.

AI: Quân quản lý key/quota; tắt AI_ENABLED khi hết ngân sách hoặc lỗi kéo dài. App vẫn học/quiz được, panel báo tạm không sẵn sàng. Không cache câu trả lời nhạy cảm, không ghi prompt/response toàn văn, không âm thầm chuyển provider hoặc giả đáp án. Các giá trị quota có thể giảm khẩn cấp qua env; tăng quota là quyết định của Quân và ghi log thay đổi.

## 11. Hồ sơ bàn giao và kịch bản demo

Bản nộp gồm source sạch và lịch sử Git theo quy định thực tế; composer/package lockfiles; migrations/seed; compose/nginx/Dockerfiles; README và env example; SRS 2.0; specification; collection API; test report có expected/actual/pass-fail; known issues; slide/video khi môn học yêu cầu. Không đóng gói .env, key, vendor/node_modules hoặc dữ liệu cá nhân.

Kịch bản demo nội bộ mục tiêu 10–12 phút, điều chỉnh theo thời lượng giảng viên:

1. **Quân, 3 phút:** kiến trúc ngắn; Instructor tạo/sửa khóa draft có đủ nội dung/quiz từ seed, publish; chỉ ra giới hạn ownership.
2. **An, 4 phút:** Student đăng ký hoặc login, tìm/enroll, học/đánh dấu, làm quiz, xem kết quả và tiến độ trên desktop/mobile.
3. **Quân, 2 phút:** AI theo bài; Admin khóa Student phụ và archive course; minh họa lỗi AI xử lý đúng nếu cần.
4. **Tú, 2 phút:** giải thích DB/session, nộp lặp không trùng, một API bị chặn sai quyền và cách chạy lại.

Dữ liệu đủ ít để demo trực tiếp; không phải nhập 20 câu hoặc hàng chục lesson. Không hardcode kết quả, không coi screenshot/mock là chức năng hoạt động. Mỗi người phải giải thích được code của mình và hiểu luồng chung.

## 12. Kiểm soát thay đổi và nhật ký quyết định

Trạng thái baseline: **2.0, dùng để triển khai**. Người quyết định scope: Quân. Reviewer kỹ thuật backend: Tú; frontend: An; PR của Quân do Tú hoặc An review theo phần liên quan. Không có quyết định thiết kế đang treo cần nhóm họp lại trước khi code.

Việc vận hành đầu dự án còn phải thực hiện, không phải câu hỏi thiết kế: cấp key AI; tạo repository; cấp quyền thành viên; kiểm yêu cầu nộp bài thực tế; khóa phiên bản patch bằng build; dịch ngày bắt đầu nếu cần. Owner đã giao ở lịch, không yêu cầu các thành viên tự chọn lại stack/quyền/quiz/schema.

Mẫu change request trong GitHub Issue: **Hiện trạng → Lý do → FR/endpoint/table bị ảnh hưởng → Phương án duy nhất đề xuất → Giờ tăng/giảm → Task thay thế → Test → Quyết định Quân → PR**. Bug fix không đổi hợp đồng chỉ cần PR thường. Không đổi DB/API bằng trao đổi miệng mà bỏ tài liệu.

| Phiên bản | Thay đổi |
|---|---|
| 1.0 | Ý tưởng và kế hoạch ban đầu ngày 28/09/2026 |
| 2.0 | Chốt React/Laravel/MySQL; thu phạm vi bốn tuần; đặc tả quyền/vòng đời/quiz/AI; 12 bảng nghiệp vụ; API/ownership/gates/tests; Quân làm leader |
