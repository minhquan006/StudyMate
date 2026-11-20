# Sửa ID theo review PR #17 — 15/11/2026

Nền trước sửa: 83e8b68a19d065790d7bff9b2feb4a043da0d467. Quân nhận góp ý backend của Tú và Approve giao diện của An tại mốc này. Bản sửa chưa được ký review hoặc commit thay Quân.

## Lỗi đã tái hiện

Trên SQLite dùng một lần, category_save với category_id[]=99999 đổi tên danh mục 1; category_id=abc tạo danh mục mới; chapter_delete với chapter_id=5abc xóa chương 5 thuộc khóa nháp của Instructor. Cả ba trả 303 và thay đổi dữ liệu. Nguyên nhân là ép kiểu int trước khi kiểm tra định dạng.

## Thay đổi

postId() kiểm sự hiện diện, kiểu scalar integer/string, chuỗi số thập phân và miền giá trị trước khi trả ID. Mảng, chuỗi lẫn chữ, rỗng, số âm/thập phân/ký hiệu khoa học hoặc tràn số bị từ chối 422.

category_save chỉ tạo mới khi category_id vắng mặt hoàn toàn. Trường có mặt nhưng sai hoặc bằng 0 trả 422. ID dương hợp lệ nhưng không tồn tại vẫn trả 404. chapter_delete không dùng phần đầu số của một chuỗi sai.

Dùng cùng kiểm tra cho các ID Admin/Instructor liên quan để tránh lỗi tương tự. Giữ course_id=0 và lesson_id=0 cho form tạo khóa/bài hiện có; chapter mới vẫn dùng trường chapter_id vắng mặt. Không đổi CSRF, role/ownership, schema, giao diện, CSS, forms.js hoặc xử lý Gemini.

## Kiểm tra sau sửa

Kiểm tự động trên macOS, PHP 8.3.36/container và DB riêng: SQLite 447 checks đạt; MySQL/Chrome 487 checks đạt. Mỗi driver có 50 ca ID sai trả 422 và hai ca ID hợp lệ nhưng không tồn tại trả 404; mỗi lần từ chối so snapshot đủ 12 bảng không đổi. Kiểm cả đường tạo/sửa/xóa hợp lệ và giữ các regression cũ.

Chrome 157: 40 browser assertions, 18 ảnh tại 390/768/1366 nằm trong 487 checks. Compose chính thức build/health/login và hồi quy đạt; hash đủ 12 bảng giữ qua restart/DB khởi động muộn. Không gọi Gemini thật. Không đổi ngày/SHA hoặc nhận các kiểm Windows/Edge của An thành lượt kiểm mới.

Log, ảnh, fingerprint, phạm vi và checksum: [evidence/quan-2026-11-15/README.md](evidence/quan-2026-11-15/README.md). Báo cáo 14/11 được làm rõ phạm vi diff --check; các log và checksum cũ giữ nguyên.

## Review tiếp

Quân tự áp dụng, kiểm diff, tạo commit mới và push lên cùng PR #17. Nhờ Tú kiểm lại ba ca báo lỗi cùng các biên ID liên quan trên SHA mới. An đối chiếu luồng form hợp lệ nếu bị ảnh hưởng. Chờ review backend đạt trước merge; nghiệm thu Gemini thật/cùng SHA cuối và hồ sơ nộp tiếp tục riêng.
