from db.database import get_db
from utils.auth import get_token_from_headers, get_user_from_token
from utils.helpers import json_response


EXTENSION_TO_MIME = {
    "pdf": "application/pdf",
    "xlsx": "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    "xls": "application/vnd.ms-excel",
    "txt": "text/plain; charset=utf-8",
    "png": "image/png",
    "jpg": "image/jpeg",
    "jpeg": "image/jpeg",
    "doc": "application/msword",
    "docx": "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
}


def handle_download(handler, headers, adv_id):
    user = get_user_from_token(get_token_from_headers(headers))
    if not user:
        return json_response(handler, {"error": "Neautorizat"}, 401)

    with get_db() as conn:
        if user["role"] == "student":
            row = conn.execute(
                """
                SELECT filename, file_data, file_type
                FROM adeverinte
                WHERE id = ? AND user_id = ?
                """,
                (adv_id, user["id"]),
            ).fetchone()
        else:
            row = conn.execute(
                """
                SELECT filename, file_data, file_type
                FROM adeverinte
                WHERE id = ?
                """,
                (adv_id,),
            ).fetchone()

    if not row:
        return json_response(handler, {"error": "Negasit"}, 404)

    data = bytes(row["file_data"])
    mime = EXTENSION_TO_MIME.get(row["file_type"], "application/octet-stream")

    handler.send_response(200)
    handler.send_header("Content-Type", mime)
    handler.send_header("Content-Disposition", f'inline; filename="{row["filename"]}"')
    handler.send_header("Content-Length", len(data))
    handler.end_headers()
    handler.wfile.write(data)
