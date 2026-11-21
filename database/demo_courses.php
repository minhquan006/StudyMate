<?php
// Original StudyMate teaching material. References and execution notes are in docs/TU_DEMO_COURSES_2026-11-21.md.
return [
    [
        'title' => 'Thực hành HTML và CSS: trang giới thiệu khóa học',
        'category' => 'Lập trình web',
        'cover_key' => 'web',
        'description' => 'Dành cho người mới muốn tự dựng một trang giới thiệu khóa học. Cần biết tạo, lưu tệp và mở trình duyệt. Không cần cài thư viện. Sau 4 bài, bạn sẽ viết cấu trúc HTML có ngữ nghĩa, tạo form có nhãn, áp dụng box model và dựng lưới responsive. Sản phẩm thực hành là một trang có thẻ khóa học và form đăng ký mẫu. Form chỉ minh họa giao diện, chưa lưu dữ liệu. Hoàn thành 4 bài rồi làm quiz 6 câu để tự kiểm tra.',
        'chapters' => [
            [
                'title' => 'Tạo cấu trúc và biểu mẫu',
                'lessons' => [
                    [
                        'title' => 'Từ tệp HTML đến trang có ngữ nghĩa',
                        'body' => <<<'MD'
# Từ tệp HTML đến trang có ngữ nghĩa

## Mục tiêu
Tạo một trang tiếng Việt có tiêu đề, vùng nội dung chính và một thẻ khóa học. Bạn cần trình duyệt và trình soạn thảo văn bản.

## Kiến thức
HTML mô tả cấu trúc nội dung. Phần head chứa thông tin về trang, còn body chứa nội dung hiển thị. Thẻ main đánh dấu nội dung chính và article gom một nội dung có thể đứng độc lập. Dùng h1 cho tiêu đề chính của ví dụ này và h2 cho tên khóa học. Thứ bậc tiêu đề thể hiện cấu trúc nội dung.

## Ví dụ chạy được
Lưu toàn bộ đoạn sau thành index.html với mã hóa UTF-8 rồi mở bằng trình duyệt.

```html
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Khóa học của tôi</title>
</head>
<body>
  <main>
    <h1>Học từng bước cùng StudyMate</h1>
    <article>
      <h2>Thực hành HTML và CSS</h2>
      <p>Tạo một trang giới thiệu khóa học trong bốn bài.</p>
    </article>
  </main>
</body>
</html>
```

## Kết quả và tự kiểm
Tab trình duyệt có tên Khóa học của tôi. Trang hiện một tiêu đề chính, một tên khóa học và một đoạn mô tả. Dấu tiếng Việt hiển thị đúng nhờ charset UTF-8 và cách lưu tệp.

## Bài tập nhỏ
Thêm một article giới thiệu khóa JavaScript với h2 và p riêng. Kết quả cần có một h1 và hai h2. Đổi title của trang rồi kiểm tra tên tab. Thay đổi này không tự đổi h1 trong body.
MD,
                    ],
                    [
                        'title' => 'Form đăng ký có nhãn và kiểm tra đầu vào',
                        'body' => <<<'MD'
# Form đăng ký có nhãn và kiểm tra đầu vào

## Mục tiêu
Thêm trường họ tên, email và nút đăng ký có thể sử dụng bằng bàn phím. Dùng lại index.html ở bài trước.

## Kiến thức
Thuộc tính for của label trỏ đến id của input. Khi bấm vào nhãn, con trỏ chuyển đến ô tương ứng. Thuộc tính name đặt tên trường khi gửi form. Thuộc tính type="email" yêu cầu định dạng email và required yêu cầu có giá trị. Đây là kiểm tra của trình duyệt. Khi có backend, server vẫn phải kiểm tra dữ liệu gửi đến.

## Ví dụ chạy được
Chèn đoạn sau trước thẻ đóng main. Form dùng GET để quan sát dữ liệu mẫu trên URL, vì vậy chỉ nhập dữ liệu giả trong bài tập.

```html
<form method="get">
  <p>
    <label for="full-name">Họ tên</label>
    <input id="full-name" name="full_name" required>
  </p>
  <p>
    <label for="email">Email</label>
    <input id="email" name="email" type="email" required>
  </p>
  <button type="submit">Đăng ký thử</button>
</form>
```

## Kết quả và tự kiểm
Nhấn Tab để lần lượt đến hai ô và nút. Bấm vào nhãn Email phải đưa focus vào ô email. Bỏ trống ô hoặc nhập abc rồi gửi sẽ bị trình duyệt chặn. Với họ tên Demo và email demo@example.com, form tải lại trang kèm tham số trên URL. Ví dụ này chưa tạo tài khoản hay lưu đăng ký.

## Bài tập nhỏ
Thêm trường mục tiêu học tập với id="goal", name="goal" và label có for="goal". Dùng Tab và bấm vào nhãn để kiểm. Giải thích vì sao bỏ required bằng công cụ phát triển không thể là cách bảo vệ dữ liệu trên server.
MD,
                    ],
                ],
            ],
            [
                'title' => 'Trình bày và hoàn thiện trang',
                'lessons' => [
                    [
                        'title' => 'Box model và thẻ khóa học dễ đọc',
                        'body' => <<<'MD'
# Box model và thẻ khóa học dễ đọc

## Mục tiêu
Phân biệt padding, border và margin, sau đó định dạng các article thành thẻ khóa học. Dùng trang HTML đã có hai article.

## Kiến thức
Box model gồm vùng nội dung, padding bên trong đường viền, border và margin bên ngoài. Với box-sizing: border-box, width bao gồm nội dung, padding và border. Với content-box, width chỉ là chiều rộng vùng nội dung. Class cho phép nhiều phần tử dùng chung cách trình bày.

## Ví dụ chạy được
Đặt class="course-card" cho cả hai article. Thêm khối style sau vào head.

```html
<style>
  * { box-sizing: border-box; }
  body { margin: 0; font-family: sans-serif; line-height: 1.6; color: #172033; }
  main { max-width: 900px; margin: 0 auto; padding: 24px; }
  .course-card {
    width: 100%;
    padding: 20px;
    border: 1px solid #cbd5e1;
    margin-bottom: 16px;
    border-radius: 12px;
  }
  .course-card h2 { margin-top: 0; }
  input { max-width: 100%; }
  input:focus-visible, button:focus-visible {
    outline: 3px solid #1d4ed8;
    outline-offset: 3px;
  }
</style>
```

## Kết quả và tự kiểm
Thẻ có khoảng trống 20px bên trong viền và 16px bên dưới. Thu nhỏ cửa sổ và thử Tab để nhìn thấy đường focus. Trong DevTools, xem sơ đồ box model của article để phân biệt padding với margin.

## Bài tập nhỏ
Thử một hộp có width 300px, padding 20px và border 1px mỗi bên. Với border-box, tổng chiều rộng tính cả viền là 300px. Với content-box, tổng là 342px, chưa tính margin. Dùng DevTools để đối chiếu hai kết quả.
MD,
                    ],
                    [
                        'title' => 'Grid responsive và kiểm trang hoàn chỉnh',
                        'body' => <<<'MD'
# Grid responsive và kiểm trang hoàn chỉnh

## Mục tiêu
Hiển thị hai thẻ cùng hàng khi đủ chỗ và một thẻ mỗi hàng trên màn hình hẹp. Tổng hợp trang giới thiệu và form của ba bài trước.

## Kiến thức
CSS Grid chia vùng chứa thành các cột. Thuộc tính gap tạo khoảng cách giữa các ô. Media query áp dụng quy tắc khi điều kiện kích thước thỏa mãn. Cấu hình viewport ở bài đầu giúp thiết bị di động dùng chiều rộng phù hợp cho bố cục responsive.

## Ví dụ chạy được
Bọc riêng hai article vào div class="course-grid". Giữ h1 và form bên ngoài div này. Thêm CSS sau vào cuối khối style. Quy tắc margin-bottom mới thay khoảng cách cũ bằng gap của lưới.

```css
.course-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
}
.course-grid .course-card { margin-bottom: 0; }
@media (max-width: 600px) {
  .course-grid { grid-template-columns: 1fr; }
  main { padding: 16px; }
}
```

## Kết quả và tự kiểm
Ở chiều rộng viewport 900px, hai thẻ nằm cạnh nhau. Ở 390px, chúng xếp thành một cột và form vẫn sử dụng được. Đặt tên khóa học dài để kiểm việc xuống dòng. Dùng bàn phím kiểm focus ở input và nút, sau đó thử gửi form với email sai và email mẫu hợp lệ.

## Bài tập nhỏ
Thêm thẻ thứ ba vào course-grid. Ở 900px, thẻ thứ ba bắt đầu hàng mới. Ở 390px, cả ba xếp dọc. Hoàn thành trang có một h1, ba thẻ và form có nhãn. Tự giải thích chức năng của HTML, CSS và kiểm tra form trước khi làm quiz.
MD,
                    ],
                ],
            ],
        ],
        'quiz' => [
            [
                'prompt' => 'Trong trang mẫu, phần tử nào đánh dấu vùng nội dung chính?',
                'answers' => [
                    'head',
                    'main',
                    'title',
                    'meta',
                ],
                'correct' => 1,
                'explanation' => 'Bài 1 đặt nội dung hiển thị chính trong main. Phần head chứa metadata và title đặt tên tab.',
            ],
            [
                'prompt' => 'Label có for="email" cần liên kết với thuộc tính nào của input?',
                'answers' => [
                    'name="email"',
                    'type="email"',
                    'id="email"',
                    'value="email"',
                ],
                'correct' => 2,
                'explanation' => 'Bài 2 dùng for của label khớp id của input. Thuộc tính name là tên trường khi gửi dữ liệu.',
            ],
            [
                'prompt' => 'Một ô type="email" required bị bỏ trống. Khi gửi form bình thường, điều gì xảy ra?',
                'answers' => [
                    'Trình duyệt chặn và yêu cầu nhập dữ liệu',
                    'Tài khoản tự động được tạo',
                    'Server chắc chắn nhận dữ liệu hợp lệ',
                    'Ô tự điền email của người dùng',
                ],
                'correct' => 0,
                'explanation' => 'required được trình duyệt kiểm khi gửi form. Backend vẫn phải kiểm tra riêng và ví dụ chưa có chức năng tạo tài khoản.',
            ],
            [
                'prompt' => 'Hộp width 300px, padding 20px, border 1px mỗi bên, box-sizing: border-box có tổng chiều rộng cả viền là bao nhiêu?',
                'answers' => [
                    '258px',
                    '340px',
                    '342px',
                    '300px',
                ],
                'correct' => 3,
                'explanation' => 'Bài 3 giải thích border-box tính padding và border trong width, nên tổng là 300px, chưa tính margin.',
            ],
            [
                'prompt' => 'Quy tắc nào tạo một cột cho lưới trên màn hình tối đa 600px?',
                'answers' => [
                    '@media (max-width: 600px) { .course-grid { grid-template-columns: 1fr; } }',
                    '.course-grid { margin-bottom: 600px; }',
                    '.course-grid { display: none; }',
                    '@media (min-width: 900px) { .course-grid { color: red; } }',
                ],
                'correct' => 0,
                'explanation' => 'Bài 4 đặt grid-template-columns: 1fr trong media query max-width: 600px để xếp một cột.',
            ],
            [
                'prompt' => 'Sau khi gửi form GET của bài 2 với dữ liệu mẫu hợp lệ, ví dụ đã làm được việc gì?',
                'answers' => [
                    'Lưu tài khoản vào MySQL',
                    'Tạo enrollment StudyMate',
                    'Tải lại trang kèm tham số trên URL',
                    'Xác minh email có thật',
                ],
                'correct' => 2,
                'explanation' => 'Form HTML minh họa gửi tham số GET. Bài 2 chưa triển khai lưu dữ liệu hay xác minh địa chỉ email.',
            ],
        ],
    ],
    [
        'title' => 'Thực hành JavaScript: bộ theo dõi việc học',
        'category' => 'Lập trình',
        'cover_key' => 'programming',
        'description' => 'Dành cho người đã đọc được HTML cơ bản và muốn thêm tương tác vào trang học. Cần trình duyệt có Console và một tệp HTML. Sau 4 bài, bạn sẽ tính tiến độ bằng hàm, xử lý mảng bài học, cập nhật DOM bằng sự kiện và lưu thử trạng thái trong localStorage. Sản phẩm là nút đánh dấu bài học kèm phần trăm tiến độ. Ví dụ lưu trên trình duyệt để luyện tập. Tiến độ của ứng dụng StudyMate vẫn do server quản lý. Quiz cuối khóa có 6 câu.',
        'chapters' => [
            [
                'title' => 'Tính toán và tổ chức dữ liệu',
                'lessons' => [
                    [
                        'title' => 'Hàm tính phần trăm tiến độ',
                        'body' => <<<'MD'
# Hàm tính phần trăm tiến độ

## Mục tiêu
Viết hàm tính tiến độ và kiểm trường hợp khóa chưa có bài. Mở DevTools của trình duyệt và chọn Console để chạy ví dụ.

## Kiến thức
const tạo binding không gán lại, còn let cho phép gán lại. Hàm nhận tham số và trả kết quả bằng return. Khi tổng số bài bằng 0, xử lý riêng trước phép chia để tránh kết quả không phù hợp. Math.round làm tròn đến số nguyên gần nhất. Ví dụ giả định done và total là số nguyên hợp lệ, done nằm trong khoảng từ 0 đến total.

## Ví dụ chạy được
Nhập toàn bộ đoạn sau vào Console. Hàm không đọc hoặc thay đổi DB StudyMate.

```javascript
function progressPercent(done, total) {
  if (total === 0) return 0;
  return Math.round(done / total * 100);
}
console.log(progressPercent(1, 4));
console.log(progressPercent(0, 0));
console.log(progressPercent(2, 3));
```

## Kết quả và tự kiểm
Ba dòng lần lượt là 25, 0 và 67. Với 2/3, giá trị khoảng 66,67 được làm tròn thành 67. Kiểm riêng tổng bằng 0 trước khi chia giúp hàm trả một số có ý nghĩa cho giao diện.

## Bài tập nhỏ
Chạy thêm progressPercent(4, 4), kết quả cần là 100. Thêm điều kiện từ chối done lớn hơn total bằng cách ném Error. Với dữ liệu từ người dùng, cần kiểm kiểu và miền giá trị trước khi gọi hàm. Quy tắc này sẽ được mở rộng ở bài mảng.
MD,
                    ],
                    [
                        'title' => 'Mảng bài học và trạng thái hoàn thành',
                        'body' => <<<'MD'
# Mảng bài học và trạng thái hoàn thành

## Mục tiêu
Biểu diễn bốn bài học bằng mảng đối tượng và tính số bài hoàn thành mà không làm mất dữ liệu cũ.

## Kiến thức
Mỗi đối tượng lưu id, title và done. Hàm filter tạo mảng mới chỉ gồm phần tử thỏa điều kiện. Hàm map tạo mảng mới bằng cách biến đổi từng phần tử. Cú pháp spread sao chép các thuộc tính của đối tượng trong ví dụ. Đây là bản sao nông. So sánh id để chỉ đánh dấu đúng bài cần thay đổi.

## Ví dụ chạy được
Chạy đoạn sau trong Console. Các biến nằm trong khối riêng nên có thể chạy lại cả khối khi luyện tập.

```javascript
{
  const lessons = [
    { id: 1, title: 'Hàm', done: true },
    { id: 2, title: 'Mảng', done: false },
    { id: 3, title: 'DOM', done: false },
    { id: 4, title: 'Lưu trạng thái', done: false }
  ];
  const updated = lessons.map(lesson =>
    lesson.id === 2 ? { ...lesson, done: true } : lesson
  );
  console.log(updated.filter(lesson => lesson.done).length);
  console.log(lessons[1].done);
}
```

## Kết quả và tự kiểm
Dòng đầu in 2 vì bản updated có hai bài đã hoàn thành. Dòng sau in false vì bài 2 của mảng lessons ban đầu chưa bị sửa. Các đối tượng không được thay đổi có thể vẫn dùng chung tham chiếu. Tránh sửa trực tiếp chúng khi cần giữ bản gốc.

## Bài tập nhỏ
Đổi id cần đánh dấu từ 2 thành 3. Số bài hoàn thành vẫn là 2, nhưng bài có id 3 được đánh dấu. Kết hợp số bài hoàn thành với hàm ở bài 1 để nhận 50%. Thử id 99. Kết quả mảng giữ nguyên vì không có bài khớp.
MD,
                    ],
                ],
            ],
            [
                'title' => 'Tạo tương tác và lưu thử',
                'lessons' => [
                    [
                        'title' => 'DOM, nút bấm và cập nhật tiến độ',
                        'body' => <<<'MD'
# DOM, nút bấm và cập nhật tiến độ

## Mục tiêu
Tạo nút hoàn thành một bài và cập nhật đoạn văn trên trang. Lưu ví dụ thành learning.html rồi mở bằng trình duyệt.

## Kiến thức
DOM là biểu diễn của tài liệu để JavaScript truy cập. Hàm querySelector tìm phần tử đầu tiên khớp selector. Hàm addEventListener đăng ký hàm xử lý sự kiện. Thuộc tính textContent thay nội dung văn bản. Đặt script sau các phần tử giúp chúng đã tồn tại khi mã truy vấn chạy.

## Ví dụ chạy được
Mỗi lần bấm nút tăng done một đơn vị nhưng tối đa bằng total. Trang tự thiết lập tổng số bài là 4.

```html
<!doctype html>
<html lang="vi">
<meta charset="utf-8">
<title>Theo dõi việc học</title>
<h1>Tiến độ học tập</h1>
<p id="progress" aria-live="polite">0%</p>
<button id="complete" type="button">Hoàn thành một bài</button>
<script>
  let done = 0;
  const total = 4;
  const output = document.querySelector('#progress');
  const button = document.querySelector('#complete');
  button.addEventListener('click', () => {
    done = Math.min(done + 1, total);
    output.textContent = Math.round(done / total * 100) + '%';
    button.disabled = done === total;
  });
</script>
</html>
```

## Kết quả và tự kiểm
Bốn lần bấm lần lượt cho 25%, 50%, 75% và 100%. Ở 100%, nút bị vô hiệu hóa. Thử dùng Tab đến nút và nhấn Enter. Thuộc tính aria-live thông báo thay đổi cho công nghệ hỗ trợ. Nội dung mới vẫn đọc được trực tiếp trên trang.

## Bài tập nhỏ
Đổi total thành 5 và dự đoán kết quả sau hai lần bấm là 40%. Tải lại trang để thấy tiến độ trở về 0%. Bài sau sẽ bổ sung lưu thử trên trình duyệt. Ví dụ này chưa gửi yêu cầu hoàn thành bài đến server StudyMate.
MD,
                    ],
                    [
                        'title' => 'Lưu trạng thái và xử lý dữ liệu không hợp lệ',
                        'body' => <<<'MD'
# Lưu trạng thái và xử lý dữ liệu không hợp lệ

## Mục tiêu
Lưu biến done để mở lại trang vẫn thấy tiến độ và xử lý dữ liệu lưu sai. Thay toàn bộ learning.html của bài 3 bằng ví dụ dưới. Mở trang qua HTTP server cục bộ, chẳng hạn chạy php -S 127.0.0.1:8090 trong thư mục bài tập rồi vào http://127.0.0.1:8090/learning.html.

## Kiến thức
localStorage lưu chuỗi theo origin của trình duyệt. Hàm getItem trả null khi chưa có khóa. Number chuyển chuỗi thành số. Cần kiểm số nguyên và miền 0–4 trước khi dùng. Cấu trúc try/catch xử lý lỗi truy cập storage. Dữ liệu thực hành lưu trong trình duyệt, còn tiến độ chính thức của StudyMate do server quản lý.

## Ví dụ chạy được
Khối dưới gồm phần đọc dữ liệu, hiển thị ban đầu và lưu trong sự kiện click. Chỉ dùng khóa riêng studymate-practice-done cho bài tập.

```html
<!doctype html>
<html lang="vi">
<meta charset="utf-8">
<title>Lưu tiến độ thực hành</title>
<h1>Tiến độ học tập</h1>
<p id="progress" aria-live="polite">0%</p>
<button id="complete" type="button">Hoàn thành một bài</button>
<script>
  const total = 4;
  let done = 0;
  try {
    const saved = Number(localStorage.getItem('studymate-practice-done'));
    if (Number.isInteger(saved) && saved >= 0 && saved <= total) done = saved;
  } catch {
    done = 0;
  }
  const output = document.querySelector('#progress');
  const button = document.querySelector('#complete');
  output.textContent = Math.round(done / total * 100) + '%';
  button.disabled = done === total;
  button.addEventListener('click', () => {
    done = Math.min(done + 1, total);
    output.textContent = Math.round(done / total * 100) + '%';
    button.disabled = done === total;
    try {
      localStorage.setItem('studymate-practice-done', String(done));
    } catch {
      console.warn('Trình duyệt chưa lưu được tiến độ thực hành.');
    }
  });
</script>
</html>
```

## Kết quả và tự kiểm
Hoàn thành hai bài rồi tải lại trang trên cùng origin, tiến độ là 50%. Trong DevTools, đặt giá trị khóa thành abc hoặc 9 rồi tải lại. Trang phải trở về 0%. Khi storage bị chặn, các lần bấm vẫn cập nhật trang hiện tại.

## Bài tập nhỏ
Đặt giá trị 1.5 và kiểm kết quả trở về 0%. Để làm lại từ đầu, chạy localStorage.removeItem('studymate-practice-done') rồi tải lại. Chỉ thao tác khóa thực hành này. Giải thích vì sao sửa localStorage không chứng minh bạn đã hoàn thành bài trên server StudyMate.
MD,
                    ],
                ],
            ],
        ],
        'quiz' => [
            [
                'prompt' => 'Hàm progressPercent ở bài 1 trả gì khi done = 2 và total = 3?',
                'answers' => [
                    '66',
                    '67',
                    '2',
                    '3',
                ],
                'correct' => 1,
                'explanation' => 'Math.round(2 / 3 * 100) làm tròn khoảng 66,67 thành 67.',
            ],
            [
                'prompt' => 'Vì sao hàm ở bài 1 kiểm total === 0 trước khi chia?',
                'answers' => [
                    'Để tạo mảng bài học',
                    'Để thay const bằng let',
                    'Để trả 0 cho khóa chưa có bài và tránh chia cho 0',
                    'Để ghi tiến độ vào DB',
                ],
                'correct' => 2,
                'explanation' => 'Trường hợp tổng bài bằng 0 được xử lý riêng bằng return 0 trước phép chia.',
            ],
            [
                'prompt' => 'Trong bài 2, sau khi map đánh dấu id 2, lessons[1].done của mảng gốc là gì?',
                'answers' => [
                    'false',
                    'true',
                    '2',
                    'undefined',
                ],
                'correct' => 0,
                'explanation' => 'Bài 2 tạo đối tượng mới cho bài id 2 trong updated; đối tượng tại lessons[1] vẫn có done: false.',
            ],
            [
                'prompt' => 'Phương thức nào đăng ký hàm chạy khi người học bấm nút?',
                'answers' => [
                    'getItem',
                    'filter',
                    'Math.round',
                    'addEventListener',
                ],
                'correct' => 3,
                'explanation' => 'Bài 3 dùng button.addEventListener với sự kiện click để cập nhật done và giao diện.',
            ],
            [
                'prompt' => 'Với total = 4, ví dụ DOM hiển thị bao nhiêu sau ba lần bấm từ done = 0?',
                'answers' => [
                    '25%',
                    '50%',
                    '75%',
                    '100%',
                ],
                'correct' => 2,
                'explanation' => 'Ba lần bấm tạo done = 3, tiến độ 3 / 4 * 100 = 75%; lần thứ tư mới đạt 100%.',
            ],
            [
                'prompt' => 'Giá trị lưu là chuỗi "1.5". Mã bài 4 xử lý thế nào khi tải trang?',
                'answers' => [
                    'Chấp nhận vì lớn hơn 0',
                    'Giữ done = 0 vì giá trị không phải số nguyên',
                    'Tự động làm tròn thành 2',
                    'Gửi 1.5 lên DB StudyMate',
                ],
                'correct' => 1,
                'explanation' => 'Number("1.5") là 1.5, không thỏa Number.isInteger nên done giữ giá trị khởi tạo 0.',
            ],
        ],
    ],
    [
        'title' => 'Thực hành SQL: quản lý đăng ký khóa học',
        'category' => 'Cơ sở dữ liệu',
        'cover_key' => 'database',
        'description' => 'Dành cho người mới biết khái niệm bảng, dòng và cột. Cần một DB thực hành trống bằng SQLite hoặc MySQL. Các ví dụ dùng tên bảng riêng lab_ và dữ liệu giả. Sau 4 bài, bạn sẽ tạo khóa chính/khóa ngoại, lọc và sắp xếp SELECT, JOIN hai bảng và tổng hợp số đăng ký bằng COUNT. Sản phẩm là báo cáo khóa học có hoặc chưa có người đăng ký. Làm quiz 6 câu sau 4 bài. Chạy SQL trong DB thực hành riêng theo hướng dẫn, bảo toàn DB demo StudyMate.',
        'chapters' => [
            [
                'title' => 'Tạo dữ liệu và truy vấn cơ bản',
                'lessons' => [
                    [
                        'title' => 'Thiết kế bảng và khóa cho dữ liệu mẫu',
                        'body' => <<<'MD'
# Thiết kế bảng và khóa cho dữ liệu mẫu

## Mục tiêu
Tạo hai bảng khóa học và đăng ký có ràng buộc, sau đó thêm dữ liệu giả. Dùng một database thực hành trống riêng. Chạy một lần theo thứ tự. Chạy lại INSERT cùng id sẽ vi phạm khóa chính.

## Kiến thức
Khóa chính định danh duy nhất một dòng và có giá trị khác NULL. Khóa ngoại bảo đảm mã khóa học trong đăng ký tham chiếu đến khóa học có thật. UNIQUE(email, course_id) ngăn cùng email đăng ký trùng một khóa. Trong ví dụ, PRIMARY KEY được khai báo kèm NOT NULL để thể hiện rõ ràng yêu cầu trên cả hai driver.

Với SQLite, chạy PRAGMA foreign_keys = ON trước đoạn SQL để bật kiểm khóa ngoại trên kết nối. Với MySQL, dùng engine InnoDB mặc định để thực thi khóa ngoại.

## Ví dụ chạy được
Tên bảng lab_ phân biệt bài thực hành với bảng của ứng dụng. Trong MySQL có thể tạo DB riêng qua công cụ quản trị. Trong SQLite mở một tệp DB mới.

```sql
CREATE TABLE lab_courses (
  id INTEGER NOT NULL PRIMARY KEY,
  title VARCHAR(100) NOT NULL,
  status VARCHAR(20) NOT NULL
);
CREATE TABLE lab_enrollments (
  id INTEGER NOT NULL PRIMARY KEY,
  email VARCHAR(100) NOT NULL,
  course_id INTEGER NOT NULL,
  UNIQUE (email, course_id),
  FOREIGN KEY (course_id) REFERENCES lab_courses(id)
);
INSERT INTO lab_courses (id, title, status) VALUES
  (1, 'HTML', 'published'),
  (2, 'JavaScript', 'published'),
  (3, 'SQL', 'draft');
INSERT INTO lab_enrollments (id, email, course_id) VALUES
  (1, 'an@example.com', 1),
  (2, 'tu@example.com', 1),
  (3, 'an@example.com', 2);
```

## Kết quả và tự kiểm
Mỗi bảng có ba dòng. Tài khoản an@example.com đăng ký được hai khóa khác nhau. Nếu thêm lần đăng ký thứ hai cho email này với course_id 1, ràng buộc UNIQUE sẽ từ chối.

## Bài tập nhỏ
Trong DB thực hành, thử thêm id 4, email demo@example.com, course_id 99 vào lab_enrollments. Khi kiểm khóa ngoại đã bật, thao tác bị từ chối vì chưa có khóa học id 99. Sau đó SELECT bảng đăng ký để xác nhận vẫn có ba dòng.
MD,
                    ],
                    [
                        'title' => 'SELECT, WHERE và thứ tự kết quả',
                        'body' => <<<'MD'
# SELECT, WHERE và thứ tự kết quả

## Mục tiêu
Truy xuất khóa học đã xuất bản và sắp xếp kết quả ổn định. Dùng dữ liệu lab_ đã tạo ở bài 1.

## Kiến thức
SELECT chọn các cột cần lấy. FROM chỉ định bảng. WHERE lọc dòng trước khi trả kết quả. ORDER BY quyết định thứ tự. Khi không có ORDER BY, không dựa vào thứ tự đang quan sát để khẳng định các lần chạy sau sẽ giống nhau. Khi một cột có giá trị trùng, có thể bổ sung id làm tiêu chí thứ hai.

## Ví dụ chạy được
Chạy câu lệnh sau trong DB thực hành.

```sql
SELECT id, title
FROM lab_courses
WHERE status = 'published'
ORDER BY id ASC;
```

## Kết quả và tự kiểm
Có hai dòng, theo thứ tự 1 HTML rồi 2 JavaScript. Khóa SQL có status draft nên bị lọc khỏi kết quả. Câu SELECT này đọc dữ liệu, giữ nguyên cả ba dòng trong bảng.

## Bài tập nhỏ
Đổi điều kiện thành status = 'draft'. Kết quả chỉ có dòng 3 SQL. Thử điều kiện status = 'archived'. Kết quả rỗng là trạng thái hợp lệ vì dữ liệu mẫu chưa có dòng này. Cuối cùng lấy lại hai khóa published nhưng dùng ORDER BY id DESC, JavaScript sẽ đứng trước HTML.

## Liên hệ ứng dụng
Khi viết backend với bộ lọc từ người dùng, truyền giá trị vào prepared statement thay vì nối thẳng dữ liệu nhập vào câu SQL. Bài tập ở đây dùng hằng chuỗi cố định để tập trung vào cách lọc và sắp xếp.
MD,
                    ],
                ],
            ],
            [
                'title' => 'Liên kết và tổng hợp báo cáo',
                'lessons' => [
                    [
                        'title' => 'INNER JOIN để xem người học đăng ký khóa nào',
                        'body' => <<<'MD'
# INNER JOIN để xem người học đăng ký khóa nào

## Mục tiêu
Kết hợp đăng ký với tên khóa học thông qua khóa tham chiếu. Dùng nguyên dữ liệu của bài 1.

## Kiến thức
INNER JOIN lấy các cặp dòng khớp điều kiện ON. Bí danh e và c giúp phân biệt cột của hai bảng. Một khóa học có thể có nhiều đăng ký, nên tên khóa học xuất hiện nhiều lần là đúng với quan hệ một-nhiều. Cần điều kiện e.course_id = c.id để liên kết theo khóa.

## Ví dụ chạy được
Chạy truy vấn sau.

```sql
SELECT e.email, c.title
FROM lab_enrollments AS e
INNER JOIN lab_courses AS c ON c.id = e.course_id
ORDER BY e.id;
```

## Kết quả và tự kiểm
Ba dòng theo thứ tự là an@example.com với HTML, tu@example.com với HTML và an@example.com với JavaScript. Khóa SQL chưa có đăng ký nên không xuất hiện trong kết quả INNER JOIN này.

## Bài tập nhỏ
Thêm WHERE c.id = 1 trước ORDER BY để chỉ lấy đăng ký HTML. Kết quả còn hai dòng của An và Tú. Đổi thành WHERE c.id = 3 cho kết quả rỗng. Giải thích vì sao bảng khóa học có ba dòng mà truy vấn lọc khóa SQL lại không trả dòng nào.

## Lỗi cần nhận biết
Nếu quên điều kiện liên kết và kết hợp mọi dòng của hai bảng, dữ liệu có thể bị nhân lên. Hãy kiểm số dòng và ý nghĩa từng dòng, rồi đối chiếu course_id trong lab_enrollments với id trong lab_courses.
MD,
                    ],
                    [
                        'title' => 'LEFT JOIN và COUNT để báo cáo cả khóa chưa có đăng ký',
                        'body' => <<<'MD'
# LEFT JOIN và COUNT để báo cáo cả khóa chưa có đăng ký

## Mục tiêu
Tạo báo cáo số đăng ký theo khóa, gồm cả khóa SQL chưa có người học. Dùng dữ liệu ban đầu, không thêm đăng ký thành công ngoài ví dụ bài 1.

## Kiến thức
LEFT JOIN giữ mọi dòng bên trái. Khi chưa có đăng ký khớp, các cột phía e là NULL. COUNT(e.id) đếm các id khác NULL nên khóa chưa có đăng ký được 0. COUNT(*) đếm cả dòng được giữ lại bởi LEFT JOIN nên có thể thành 1 trong trường hợp này. GROUP BY gom dữ liệu theo từng khóa.

## Ví dụ chạy được
Chạy truy vấn sau. Liệt kê cả c.id và c.title trong GROUP BY để rõ ràng và tương thích với cấu hình MySQL kiểm GROUP BY nghiêm ngặt.

```sql
SELECT c.id, c.title, COUNT(e.id) AS enrollments
FROM lab_courses AS c
LEFT JOIN lab_enrollments AS e ON e.course_id = c.id
GROUP BY c.id, c.title
ORDER BY c.id;
```

## Kết quả và tự kiểm
Báo cáo có ba dòng lần lượt là 1 HTML 2, 2 JavaScript 1 và 3 SQL 0. Đối chiếu tổng đăng ký 2 + 1 + 0 = 3 với số dòng thực tế trong lab_enrollments.

## Bài tập nhỏ
Thử thay COUNT(e.id) bằng COUNT(*) để thấy SQL cho kết quả 1, rồi khôi phục COUNT(e.id). Thêm HAVING COUNT(e.id) = 0 giữa GROUP BY và ORDER BY để chỉ lấy khóa chưa có đăng ký. Kết quả cần đúng dòng 3 SQL 0. Kết thúc bài thực hành bằng cách giải thích sự khác nhau giữa WHERE lọc dòng và HAVING lọc nhóm.
MD,
                    ],
                ],
            ],
        ],
        'quiz' => [
            [
                'prompt' => 'UNIQUE(email, course_id) trong bảng mẫu bảo đảm điều gì?',
                'answers' => [
                    'Mỗi email chỉ học được một khóa',
                    'Một email không có hai đăng ký cùng course_id',
                    'Mỗi khóa chỉ có một người học',
                    'Mỗi email tự động là khóa chính',
                ],
                'correct' => 1,
                'explanation' => 'Bài 1 đặt UNIQUE trên tổ hợp email và course_id. Cùng email vẫn có thể đăng ký khóa khác.',
            ],
            [
                'prompt' => 'Với dữ liệu ban đầu, SELECT lọc status = published rồi ORDER BY id ASC trả tên theo thứ tự nào?',
                'answers' => [
                    'SQL rồi HTML',
                    'JavaScript rồi HTML',
                    'HTML rồi JavaScript',
                    'HTML, JavaScript, SQL',
                ],
                'correct' => 2,
                'explanation' => 'Bài 2 chỉ giữ id 1 và 2 có status published, rồi sắp id tăng dần.',
            ],
            [
                'prompt' => 'INNER JOIN của bài 3 trên dữ liệu ban đầu trả bao nhiêu dòng?',
                'answers' => [
                    '3 dòng',
                    '2 dòng',
                    '9 dòng',
                    '0 dòng',
                ],
                'correct' => 0,
                'explanation' => 'Có ba đăng ký hợp lệ, mỗi đăng ký khớp một khóa học qua e.course_id = c.id.',
            ],
            [
                'prompt' => 'Báo cáo LEFT JOIN của bài 4 dùng COUNT(e.id) cho khóa SQL kết quả nào?',
                'answers' => [
                    '3',
                    '2',
                    '1',
                    '0',
                ],
                'correct' => 3,
                'explanation' => 'Khóa SQL chưa có đăng ký, e.id là NULL ở dòng giữ lại; COUNT(e.id) chỉ đếm giá trị khác NULL nên được 0.',
            ],
            [
                'prompt' => 'Mệnh đề nào lọc các nhóm có số đăng ký bằng 0 trong bài 4?',
                'answers' => [
                    'WHERE COUNT(e.id) = 0',
                    'HAVING COUNT(e.id) = 0',
                    'ORDER BY COUNT(e.id) = 0',
                    'LIMIT COUNT(e.id) = 0',
                ],
                'correct' => 1,
                'explanation' => 'HAVING lọc kết quả sau GROUP BY; bài 4 dùng HAVING COUNT(e.id) = 0 để lấy khóa chưa có đăng ký.',
            ],
            [
                'prompt' => 'Khi kiểm khóa ngoại đã bật, thêm đăng ký có course_id 99 vào dữ liệu mẫu sẽ thế nào?',
                'answers' => [
                    'Tự tạo khóa học 99',
                    'Tự đổi course_id thành 1',
                    'Bị từ chối vì không có khóa học id 99',
                    'Tự xóa các đăng ký cũ',
                ],
                'correct' => 2,
                'explanation' => 'FOREIGN KEY tham chiếu lab_courses(id); dữ liệu chỉ có id 1, 2, 3 nên course_id 99 không hợp lệ. SQLite cần bật PRAGMA foreign_keys = ON.',
            ],
        ],
    ],
];
