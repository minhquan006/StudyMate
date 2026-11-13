#!/usr/bin/env python3
"""Build database/acceptance PDFs and update the existing SRS Word appendix."""
import argparse
from pathlib import Path
from xml.sax.saxutils import escape
from docx import Document
from docx.shared import Pt, RGBColor
from docx.enum.style import WD_STYLE_TYPE
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from reportlab.lib import colors
from reportlab.lib.enums import TA_LEFT
from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import ParagraphStyle, getSampleStyleSheet
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, PageBreak, Image

ROOT = Path(__file__).resolve().parents[1]
RESULTS = [
    ('SQLite HTTP và nghiệp vụ', '270 kiểm tra đạt'),
    ('MySQL và Chrome', '310 kiểm tra đạt; 18 màn hình ở 390/768/1366'),
    ('Hồi quy Tú và An trên Compose', '8 phân trang; 58 UTF-8; 8 model; 24 offline đạt'),
    ('Docker và bảo toàn dữ liệu', 'Build/health/login đạt; hash đủ 12 bảng giữ qua restart/DB chậm'),
    ('Gemini HTTP MySQL UI', 'Hỏi/tóm tắt trong Chrome; thiếu dữ liệu, injection và archived có phản hồi'),
    ('Lỗi và quyền qua HTTP', '403 sai quyền; 429 quota app; key giả 503; học/quiz tiếp tục hoạt động'),
]
FR = [
    ('FR01', 'Tài khoản', 'Đăng nhập ba role, thu hồi nhiều phiên', 'Tú xác nhận trên SHA cuối'),
    ('FR02', 'Quyền và CSRF', 'Owner/role/ID chéo, 12 action, DB không đổi', 'Tú review phần Quân'),
    ('FR03', 'Catalog', 'Phân trang, tìm UTF-8, filter', 'An kiểm trực quan'),
    ('FR04', 'Enrollment', 'Luồng enroll và unique; bằng chứng race của Tú', 'Tú chốt hồi quy cần thiết'),
    ('FR05', 'Learning', 'Lưu/giữ tiến độ, archived, restart', 'An chạy luồng bản cuối'),
    ('FR06', 'Quiz', 'Validation, chấm 100%, UUID và atomic rejection', 'Tú review/race theo thay đổi'),
    ('FR07', 'Instructor', 'CRUD/publish, khóa cấu trúc, ownership', 'Tú handler, An HTML'),
    ('FR08', 'Admin', 'Category/state, CSRF, thu hồi phiên', 'Tú handler, An HTML'),
    ('FR09', 'Dashboard', 'Tổng toàn tập, aggregate, phân trang', 'An/Tú đối chiếu giao diện'),
    ('FR10', 'Gemini', 'HTTP/MySQL/Chrome thật và mock tách riêng', 'Tú review chất lượng/mã; nhóm nghiệm thu'),
    ('FR11', 'Responsive', 'Chrome 18 màn hình phần Quân; lịch sử An riêng', 'An chốt keyboard/Edge bản cuối'),
    ('FR12', 'Bàn giao', 'README, SRS, schema, Docker và gói checksum', 'Review, SHA, hồ sơ và Submitted'),
]


def update_srs():
    path = ROOT / 'docs/StudyMate_SRS.docx'
    doc = Document(path)
    if 'Heading 1' not in doc.styles:
        heading = doc.styles.add_style('Heading 1', WD_STYLE_TYPE.PARAGRAPH)
        heading.base_style = doc.styles['Normal']
        heading.font.name = 'Noto Sans'; heading.font.size = Pt(16); heading.font.bold = True
        heading.paragraph_format.space_after = Pt(10)
    if 'Table Grid' not in doc.styles:
        doc.styles.add_style('Table Grid', WD_STYLE_TYPE.TABLE)
    marker = 'Cập nhật tích hợp ngày 01 11 2026'
    if any(p.text == marker for p in doc.paragraphs):
        raise ValueError('SRS appendix already exists; inspect before replacing it')
    for paragraph in doc.paragraphs:
        text = paragraph.text
        text = text.replace('Baseline yêu cầu 2.2 áp dụng cho bản hoàn tất ngày 01/11/2026.',
                            'Mốc 01/11/2026 là kế hoạch nội bộ; hạn LMS và rubric chính thức cần được xác nhận.')
        text = text.replace('Bản hoàn tất ngày 01/11/2026.', 'Mốc kế hoạch nội bộ 01/11/2026.')
        if text != paragraph.text:
            paragraph.text = text
    title = doc.paragraphs[0]
    title.style = doc.styles['Title']
    for run in title.runs:
        run.font.color.rgb = RGBColor(0, 0, 0)
    ppr = title._p.get_or_add_pPr()
    for borders in list(ppr.findall(qn('w:pBdr'))):
        ppr.remove(borders)
    doc.add_page_break()
    doc.add_heading(marker, level=1)
    doc.add_paragraph('Bản tích hợp dùng source nền PR 15 tại 81dc740, đã nhận sửa nền Tú 1a27ec1. Phần Quân bổ sung validation Instructor/Admin và xử lý lỗi Gemini; giữ truy vấn, phân trang, vùng bảng và startup của Tú/An.')
    table = doc.add_table(rows=1, cols=2)
    table.style = 'Table Grid'
    table.rows[0].cells[0].text = 'Kiểm tra'
    table.rows[0].cells[1].text = 'Kết quả ngày 01/11/2026'
    for label, result in RESULTS:
        cells = table.add_row().cells
        cells[0].text = label; cells[1].text = result
    for cell in table.rows[0].cells:
        for run in cell.paragraphs[0].runs: run.bold = True
    for row in table.rows:
        for cell in row.cells:
            for p in cell.paragraphs:
                p.paragraph_format.space_after = Pt(4)
                for run in p.runs: run.font.size = Pt(9)
    doc.add_paragraph('Profile model đã kiểm là gemini-3.5-flash-lite. File env mẫu chọn model này; config riêng được giữ nguyên. Provider timeout/quota thật chưa được cố tình gây lỗi. Báo cáo lượt dừng, kiểm lại và fingerprint nằm trong QUAN_INTEGRATION_2026-11-01.md.')
    doc.add_paragraph('Bản local chưa commit, chưa có peer review hoặc merge. Tú review handlers/query/AI, An review HTML. Cả ba nghiệm thu trên cùng SHA trước khi chốt hồ sơ; hạn khoảng 10/11 hiện là dự kiến, không phải xác nhận hạn LMS.')
    doc.save(path)


def build_pdfs(output, font_dir=None):
    candidates = ([Path(font_dir)] if font_dir else []) + [
        Path.home()/'.cache/codex-runtimes/codex-primary-runtime/dependencies/native/libreoffice-headless/libreoffice/LibreOfficeDev.app/Contents/Resources/fonts/truetype',
        Path('/usr/share/fonts/truetype/dejavu'),
    ]
    for folder in candidates:
        for family in ['NotoSans', 'DejaVuSans']:
            regular = folder / (family + '-Regular.ttf')
            if not regular.exists(): regular = folder / (family + '.ttf')
            bold = folder / (family + '-Bold.ttf')
            if regular.exists() and bold.exists(): break
        else: continue
        break
    else: raise ValueError('Supply --font-dir containing NotoSans or DejaVuSans Unicode fonts')
    pdfmetrics.registerFont(TTFont('StudyText', str(regular)))
    pdfmetrics.registerFont(TTFont('StudyBold', str(bold)))
    styles = getSampleStyleSheet()
    styles.add(ParagraphStyle(name='StudyTitle', fontName='StudyBold', fontSize=20, leading=26, spaceAfter=16, textColor=colors.HexColor('#172033')))
    styles.add(ParagraphStyle(name='StudyH', fontName='StudyBold', fontSize=13, leading=18, spaceBefore=12, spaceAfter=7))
    styles.add(ParagraphStyle(name='StudyBody', fontName='StudyText', fontSize=10, leading=15, spaceAfter=8))
    styles.add(ParagraphStyle(name='StudyCell', fontName='StudyText', fontSize=8.5, leading=12))

    def p(text, style='StudyBody'): return Paragraph(escape(str(text)), styles[style])
    def table(rows, widths):
        result = Table([[p(cell, 'StudyCell') for cell in row] for row in rows], colWidths=widths, repeatRows=1, hAlign='LEFT')
        result.setStyle(TableStyle([
            ('BACKGROUND', (0, 0), (-1, 0), colors.HexColor('#edf0f5')),
            ('VALIGN', (0, 0), (-1, -1), 'TOP'),
            ('GRID', (0, 0), (-1, -1), .4, colors.HexColor('#c8ced8')),
            ('LEFTPADDING', (0, 0), (-1, -1), 7), ('RIGHTPADDING', (0, 0), (-1, -1), 7),
            ('TOPPADDING', (0, 0), (-1, -1), 6), ('BOTTOMPADDING', (0, 0), (-1, -1), 6),
        ]))
        return result

    width = A4[0] - 88
    def save(name, story):
        doc = SimpleDocTemplate(str(output / name), pagesize=A4, leftMargin=44, rightMargin=44, topMargin=42, bottomMargin=42,
                                title='StudyMate', author='StudyMate project team')
        def footer(canvas, document):
            canvas.setFont('StudyText', 8); canvas.setFillColor(colors.HexColor('#647086'))
            canvas.drawRightString(A4[0]-44, 24, str(document.page))
        doc.build(story, onFirstPage=footer, onLaterPages=footer)

    rows = [
        ('users', 'Email unique; role, is_active, auth_version; giữ lịch sử tài khoản'),
        ('categories', 'Tên unique; không xóa khi course đang tham chiếu'),
        ('courses', 'Instructor và category FK; draft/published/archived'),
        ('chapters', 'course_id FK; position; khóa cấu trúc sau enrollment'),
        ('lessons', 'chapter_id FK; body, video ID và position'),
        ('enrollments', 'Unique user/course; last_lesson_id và completed_at nullable'),
        ('lesson_progress', 'Unique user/lesson; hoàn thành không bị nhân đôi'),
        ('quizzes', 'course_id unique; mỗi course tối đa một quiz'),
        ('questions', 'quiz_id FK; prompt, explanation và position'),
        ('answers', 'question_id FK; bốn lựa chọn, một đáp án đúng'),
        ('quiz_attempts', 'Unique user/request_id; hash payload, điểm do server tính'),
        ('quiz_answers', 'Unique attempt/question; answer_id nullable cho câu bỏ trống'),
    ]
    story = [p('Thiết kế cơ sở dữ liệu StudyMate', 'StudyTitle'),
             p('Nguyễn Minh Quân 52400227 · Lê Nguyễn Vạn An 52400258 · Phạm Văn Tú 52400252'),
             p('PHP 8.3 và PDO; MySQL 8.4 InnoDB utf8mb4 là đích nghiệm thu. SQLite phục vụ local/test. Schema thực thi ở database/schema.php; setup chỉ seed khi chưa có người dùng.'),
             table([('Bảng', 'Quan hệ và toàn vẹn'), *rows], [125, width-125]),
             Spacer(1, 10), p('FK giữ tham chiếu; service kiểm thêm answer thuộc question và lesson thuộc course. Dữ liệu kiểm thử được cô lập; không reset DB phát triển.')]
    for heading, image, note in [
        ('Quan hệ nội dung khóa học', 'erd-noi-dung.png', 'Course có chapter/lesson và một quiz khi publish. Chỉ sửa cấu trúc ở draft chưa enrollment; dữ liệu lịch sử dùng ràng buộc RESTRICT.'),
        ('Quan hệ học tập và kết quả', 'erd-hoc-tap.png', 'Enrollment, progress và request UUID có unique constraint. Ghi attempt, mọi quiz_answers và trạng thái hoàn thành trong transaction; complete một lần vẫn giữ khi làm lại điểm thấp.'),
    ]:
        story += [PageBreak(), p(heading, 'StudyTitle')]
        image_path = ROOT / 'docs/images/srs' / image
        img = Image(str(image_path)); scale = min(width/img.imageWidth, 535/img.imageHeight)
        img.drawWidth = img.imageWidth*scale; img.drawHeight = img.imageHeight*scale
        story += [img, Spacer(1, 15), p(note), p('Ảnh sơ đồ lấy từ SRS của nhóm. Số bảng nghiệp vụ là 12; bảng lặp trên hai sơ đồ là cùng một bảng.')]
    save('StudyMate_Database_Design_REVIEW.pdf', story)

    story = [p('Nghiệm thu phần Quân StudyMate', 'StudyTitle'),
             p('Cập nhật 01/11/2026. Source nền 81dc740 của An, nhận nền Tú 1a27ec1. Phần Quân đã được ghép và kiểm thử; review chéo và nghiệm thu nhóm thực hiện trước khi chốt SHA.'),
             p('Thay đổi cần review', 'StudyH'),
             p('Validation quiz/chapter và trạng thái Admin; chuẩn hóa tham số phân trang dùng lại truy vấn Tú; xử lý timeout/JSON/safety/thought và redirect trong Gemini. Giữ vùng bảng, tìm UTF-8 và startup của hai thành viên.'),
             table([('Nhóm kiểm tra', 'Kết quả'), *RESULTS], [160, width-160]),
             Spacer(1, 12), p('Các số thuộc phạm vi từng suite, có phần lặp service/AI; không cộng thành tổng ca nghiệp vụ độc lập. Các lượt dừng và kiểm lại giữ nguyên trong báo cáo tích hợp. Provider timeout/quota thật chưa được cố tình gây lỗi.'),
             p('Model nghiệm thu: gemini-3.5-flash-lite. Key riêng không nằm trong source, ảnh, log hoặc gói ZIP. Billing không được thay đổi trong lượt kiểm.')]
    story += [PageBreak(), p('Đối chiếu FR01 đến FR12', 'StudyTitle'),
              table([('Mã', 'Chức năng', 'Bằng chứng', 'Bước chốt'), *FR], [40, 83, 202, width-325])]
    story += [PageBreak(), p('Review và nghiệm thu chung', 'StudyTitle'),
              table([('Người', 'Phần cần xác nhận'),
                     ('Tú', 'Instructor/Admin handlers; quyền, CSRF, query, Gemini transport/decoder; dữ liệu và runtime'),
                     ('An', 'Instructor/Admin HTML; responsive, focus, bàn phím, bộ ảnh và cài độc lập'),
                     ('Quân', 'Đối chiếu góp ý, sửa lỗi, tổng hợp bằng chứng và hồ sơ; không tự duyệt phần mình')], [70, width-70]),
              p('Kịch bản trên cùng một SHA', 'StudyH')]
    for step in [
        '1. Quân tạo draft, chapter, lesson, quiz và publish; kiểm quyền/owner và khóa cấu trúc.',
        '2. An đăng ký Student, tìm khóa, enroll, hoàn thành bài, nộp quiz và xem kết quả.',
        '3. Student hỏi/tóm tắt Gemini thật; kiểm nội dung thiếu dữ liệu và hành vi lỗi an toàn.',
        '4. Quân dùng Admin khóa/mở tài khoản; phiên cũ vẫn bị thu hồi, sai quyền bị chặn.',
        '5. Tú đối chiếu MySQL, thử thao tác lặp, restart và restore vào DB riêng.',
        '6. Ghi expected/actual và SHA; sửa lỗi rồi kiểm lại phần bị ảnh hưởng trước khi chốt.',
    ]: story.append(p(step))
    story += [p('Chốt hồ sơ', 'StudyH'), p('Xác nhận rubric, hạn và kênh nộp; khoảng 10/11 hiện là dự kiến. Hoàn thiện ảnh có thanh địa chỉ thật, PDF giao diện và phần trình bày của ba thành viên theo yêu cầu đã chốt. Tạo lại ZIP/manifest trên SHA cuối và kiểm trạng thái Submitted.')]
    save('StudyMate_Quan_Acceptance_REVIEW.pdf', story)


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--output', type=Path, required=True)
    parser.add_argument('--font-dir', type=Path)
    parser.add_argument('--update-srs-docx', action='store_true')
    args = parser.parse_args()
    args.output.mkdir(parents=True, exist_ok=True)
    if args.update_srs_docx: update_srs()
    build_pdfs(args.output.resolve(), args.font_dir)
    print('Created two review PDFs' + (' and updated SRS Word appendix.' if args.update_srs_docx else '.'))


if __name__ == '__main__': main()
