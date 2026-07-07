from db.database import get_db
from utils.auth import get_token_from_headers, get_user_from_token
from utils.file_parser import extract_text_from_bytes, parse_date_from_text, parse_times_from_text
from utils.helpers import get_pagination, get_requested_page, json_response, parse_multipart


ALLOWED_UPLOADS = {"pdf", "xlsx", "xls", "txt", "png", "jpg", "jpeg", "doc", "docx"}


def handle_upload(handler, headers, body):
    user = get_user_from_token(get_token_from_headers(headers))
    if not user or user["role"] != "student":
        return json_response(handler, {"error": "Neautorizat"}, 401)

    content_type = headers.get("Content-Type", "")
    boundary = None
    for item in content_type.split(";"):
        item = item.strip()
        if item.startswith("boundary="):
            boundary = item[9:].strip('"')
            break

    if not boundary:
        return json_response(handler, {"error": "Multipart boundary lipsa"}, 400)

    fields = parse_multipart(body, boundary)
    if "file" not in fields:
        return json_response(handler, {"error": "Fisier lipsa"}, 400)

    filename, file_bytes = fields["file"]
    if not filename:
        filename = "adeverinta.bin"

    ext = filename.rsplit(".", 1)[-1].lower() if "." in filename else "bin"
    if ext not in ALLOWED_UPLOADS:
        return json_response(handler, {"error": f"Tip fisier nepermis: .{ext}"}, 400)

    extracted_text = extract_text_from_bytes(file_bytes, filename)
    data_prezenta = parse_date_from_text(extracted_text)
    ora_start, ora_end, ore_text = parse_times_from_text(extracted_text)

    with get_db() as conn:
        conn.execute(
            """
            INSERT INTO adeverinte
            (user_id, filename, file_data, file_type, data_prezenta, ora_start, ora_end, ore_text)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            """,
            (
                user["id"],
                filename,
                file_bytes,
                ext,
                data_prezenta,
                ora_start,
                ora_end,
                ore_text,
            ),
        )
        conn.commit()

    return json_response(
        handler,
        {
            "ok": True,
            "data_prezenta": data_prezenta,
            "ore_text": ore_text,
        },
    )


def handle_my_adeverinte(handler, headers, qs):
    user = get_user_from_token(get_token_from_headers(headers))
    if not user or user["role"] != "student":
        return json_response(handler, {"error": "Neautorizat"}, 401)

    requested_page = get_requested_page(qs)

    with get_db() as conn:
        total_items = conn.execute(
            "SELECT COUNT(*) FROM adeverinte WHERE user_id = ?",
            (user["id"],),
        ).fetchone()[0]
        pagination, offset = get_pagination(total_items, requested_page)
        rows = conn.execute(
            """
            SELECT id, filename, file_type, data_prezenta, ora_start, ora_end,
                   ore_text, status, observatii, uploaded_at, reviewed_at
            FROM adeverinte
            WHERE user_id = ?
            ORDER BY uploaded_at DESC
            LIMIT ? OFFSET ?
            """,
            (user["id"], pagination["page_size"], offset),
        ).fetchall()

    return json_response(
        handler,
        {
            "items": [dict(row) for row in rows],
            "pagination": pagination,
        },
    )


def handle_delete_adeverinta(handler, headers, adv_id):
    user = get_user_from_token(get_token_from_headers(headers))
    if not user or user["role"] != "student":
        return json_response(handler, {"error": "Neautorizat"}, 401)

    with get_db() as conn:
        row = conn.execute(
            "SELECT user_id, status FROM adeverinte WHERE id = ?",
            (adv_id,),
        ).fetchone()

        if not row:
            return json_response(handler, {"error": "Negasit"}, 404)
        if row["user_id"] != user["id"]:
            return json_response(handler, {"error": "Acces interzis"}, 403)
        if row["status"] != "in_asteptare":
            return json_response(
                handler,
                {"error": "Nu se poate sterge o adeverinta deja procesata"},
                400,
            )

        conn.execute("DELETE FROM adeverinte WHERE id = ?", (adv_id,))
        conn.commit()

    return json_response(handler, {"ok": True})
