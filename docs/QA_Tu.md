\# Báo cáo kiểm thử nền tảng — Phạm Văn Tú



Ngày kiểm tra: 12/10/2026.



\## Môi trường

\- Windows, Docker Desktop dùng Linux/WSL2.

\- Docker 29.8.0; Docker Compose v5.5.1.

\- Image PHP: php:8.3-apache; MySQL: mysql:8.4.

\- Địa chỉ: http://localhost:8080

\- Bản kiểm tra: StudyMate\_PHP\_Integrated.zip, chạy riêng ngoài repo.

\- Compose project: studymate-test.



\## Kết quả kiểm tra thủ công

| Nội dung | Kết quả |

|---|---|

| Build và khởi động Docker Compose | Đạt; app Up, DB healthy |

| Đăng nhập Student, Instructor, Admin | Đạt |

| Đánh dấu hoàn thành bài học | Tiến độ cập nhật |

| Quiz lần đầu | 0/5 câu, 0%, chưa đạt |

| Làm lại quiz | 5/5 câu, 100%, đạt |

| Dashboard sau hoàn thành | Hiển thị 1 khóa hoàn thành |

| Khởi động lại container | App và DB chạy lại |

| Dữ liệu sau restart | Giữ trạng thái hoàn thành và kết quả 100% |

| Trang Instructor | Mở được danh sách khóa học |

| Trang Admin | Mở được người dùng, khóa học và danh mục |



\## Giới hạn

\- Đây là kiểm tra thủ công bản Integrated, chưa phải nghiệm thu bản main.

\- Chưa chạy tests/run.php trên MySQL.

\- Chưa kiểm thử đầy đủ phân quyền, CSRF, request đồng thời và thao tác quản trị.

\- AI đang tắt; chưa kiểm thử Gemini thật.

\- Cần kiểm tra lại từ bản clone sạch sau khi merge đủ ba phần.

