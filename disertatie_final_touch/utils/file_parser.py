import re
import zipfile
from datetime import date
from io import BytesIO
from xml.etree import ElementTree


DATE_PATTERNS = [
    r"(?<!\d)(\d{1,2})[.\-/](\d{1,2})[.\-/](\d{4})(?!\d)",
    r"(?<!\d)(\d{4})[.\-/](\d{1,2})[.\-/](\d{1,2})(?!\d)",
]
TIME_PATTERN = r"(?<!\d)(\d{1,2})[:\.](\d{2})(?!\d)"
WORD_NS = {"w": "http://schemas.openxmlformats.org/wordprocessingml/2006/main"}


def extract_text_from_bytes(file_bytes, filename):
    ext = filename.rsplit(".", 1)[-1].lower() if "." in filename else ""

    if ext in ("xlsx", "xls"):
        return _extract_excel_text(file_bytes)
    if ext == "pdf":
        return _extract_pdf_text(file_bytes)
    if ext == "docx":
        return _extract_docx_text(file_bytes)
    if ext == "doc":
        return _extract_plain_text(file_bytes)

    return _extract_plain_text(file_bytes)


def parse_date_from_text(text):
    for pattern in DATE_PATTERNS:
        match = re.search(pattern, text)
        if not match:
            continue

        parts = match.groups()
        if len(parts[0]) == 4:
            year, month, day = int(parts[0]), int(parts[1]), int(parts[2])
        else:
            day, month, year = int(parts[0]), int(parts[1]), int(parts[2])

        try:
            return date(year, month, day).strftime("%d.%m.%Y")
        except ValueError:
            continue

    return None


def parse_times_from_text(text):
    times = []
    for match in re.finditer(TIME_PATTERN, text):
        hour, minute = int(match.group(1)), int(match.group(2))
        if 0 <= hour <= 23 and 0 <= minute <= 59:
            times.append(f"{hour:02d}:{minute:02d}")

    unique_times = sorted(set(times))
    if len(unique_times) >= 2:
        return unique_times[0], unique_times[-1], f"{unique_times[0]} - {unique_times[-1]}"
    if len(unique_times) == 1:
        return unique_times[0], unique_times[0], unique_times[0]

    return None, None, None


def _extract_excel_text(file_bytes):
    try:
        import openpyxl
    except ImportError:
        return ""

    try:
        workbook = openpyxl.load_workbook(BytesIO(file_bytes), data_only=True)
    except Exception:
        return ""

    lines = []
    for worksheet in workbook.worksheets:
        for row in worksheet.iter_rows(values_only=True):
            parts = [str(cell).strip() for cell in row if cell is not None and str(cell).strip()]
            if parts:
                lines.append(" ".join(parts))

    return "\n".join(lines)


def _extract_pdf_text(file_bytes):
    try:
        import PyPDF2
    except ImportError:
        return ""

    try:
        reader = PyPDF2.PdfReader(BytesIO(file_bytes))
    except Exception:
        return ""

    text_parts = []
    for page in reader.pages:
        try:
            text_parts.append(page.extract_text() or "")
        except Exception:
            continue

    return "\n".join(text_parts)


def _extract_docx_text(file_bytes):
    try:
        with zipfile.ZipFile(BytesIO(file_bytes)) as archive:
            names = set(archive.namelist())
            xml_files = [
                "word/document.xml",
                "word/header1.xml",
                "word/header2.xml",
                "word/header3.xml",
                "word/footer1.xml",
                "word/footer2.xml",
                "word/footer3.xml",
                "word/footnotes.xml",
            ]

            paragraphs = []
            for name in xml_files:
                if name not in names:
                    continue

                root = ElementTree.fromstring(archive.read(name))
                for paragraph in root.findall(".//w:p", WORD_NS):
                    pieces = [
                        node.text
                        for node in paragraph.findall(".//w:t", WORD_NS)
                        if node.text
                    ]
                    if pieces:
                        paragraphs.append("".join(pieces))

            return "\n".join(paragraphs)
    except Exception:
        return ""


def _extract_plain_text(file_bytes):
    try:
        return file_bytes.decode("utf-8", errors="ignore")
    except Exception:
        return ""
