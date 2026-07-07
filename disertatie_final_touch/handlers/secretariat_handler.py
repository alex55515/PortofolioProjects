import json

from db.database import get_db
from utils.auth import get_token_from_headers, get_user_from_token
from utils.helpers import get_pagination, get_requested_page, json_response


def handle_sec_students(handler, headers, qs):
    user = get_user_from_token(get_token_from_headers(headers))
    if not user or user["role"] != "secretariat":
        return json_response(handler, {"error": "Neautorizat"}, 401)

    search = qs.get("q", [""])[0].strip().lower()
    grupa = qs.get("grupa", [""])[0].strip().lower()
    requested_page = get_requested_page(qs)

    with get_db() as conn:
        rows = conn.execute(
            """
            SELECT u.id, u.username, u.nume, u.grupa,
                   COUNT(a.id) AS total_adv,
                   SUM(CASE WHEN a.status = 'in_asteptare' THEN 1 ELSE 0 END) AS pending,
                   SUM(CASE WHEN a.status = 'confirmata' THEN 1 ELSE 0 END) AS confirmed,
                   SUM(CASE WHEN a.status = 'respinsa' THEN 1 ELSE 0 END) AS rejected
            FROM users u
            LEFT JOIN adeverinte a ON a.user_id = u.id
            WHERE u.role = 'student'
            GROUP BY u.id
            ORDER BY u.nume
            """
        ).fetchall()

    result = []
    for row in rows:
        item = dict(row)
        if search:
            haystack = f"{item['nume']} {item['username']}".lower()
            if search not in haystack:
                continue
        if grupa and (item["grupa"] or "").lower() != grupa:
            continue
        result.append(item)

    pagination, offset = get_pagination(len(result), requested_page)
    paged_result = result[offset:offset + pagination["page_size"]]

    return json_response(
        handler,
        {
            "items": paged_result,
            "pagination": pagination,
        },
    )


def handle_sec_adeverinte(handler, headers, qs, student_id=None):
    user = get_user_from_token(get_token_from_headers(headers))
    if not user or user["role"] != "secretariat":
        return json_response(handler, {"error": "Neautorizat"}, 401)

    status_filter = qs.get("status", [""])[0].strip()
    search = qs.get("q", [""])[0].strip().lower()
    requested_page = get_requested_page(qs)

    where_parts = ["1 = 1"]
    params = []

    if student_id is not None:
        where_parts.append("a.user_id = ?")
        params.append(student_id)

    if status_filter:
        where_parts.append("a.status = ?")
        params.append(status_filter)

    if search:
        where_parts.append(
            """
            (
                LOWER(u.nume) LIKE ?
                OR LOWER(u.username) LIKE ?
                OR LOWER(COALESCE(u.grupa, '')) LIKE ?
                OR LOWER(a.filename) LIKE ?
            )
            """
        )
        like_value = f"%{search}%"
        params.extend([like_value, like_value, like_value, like_value])

    where_clause = " AND ".join(where_parts)

    with get_db() as conn:
        total_items = conn.execute(
            f"""
            SELECT COUNT(*)
            FROM adeverinte a
            JOIN users u ON u.id = a.user_id
            WHERE {where_clause}
            """,
            params,
        ).fetchone()[0]

        pagination, offset = get_pagination(total_items, requested_page)
        rows = conn.execute(
            f"""
            SELECT a.id, a.filename, a.file_type, a.data_prezenta,
                   a.ora_start, a.ora_end, a.ore_text,
                   a.status, a.observatii, a.uploaded_at, a.reviewed_at,
                   u.nume AS student_nume, u.username, u.grupa,
                   ru.nume AS reviewed_by_nume
            FROM adeverinte a
            JOIN users u ON u.id = a.user_id
            LEFT JOIN users ru ON ru.id = a.reviewed_by
            WHERE {where_clause}
            ORDER BY a.uploaded_at DESC
            LIMIT ? OFFSET ?
            """,
            params + [pagination["page_size"], offset],
        ).fetchall()

    return json_response(
        handler,
        {
            "items": [dict(row) for row in rows],
            "pagination": pagination,
        },
    )


def handle_review(handler, headers, body, adv_id):
    user = get_user_from_token(get_token_from_headers(headers))
    if not user or user["role"] != "secretariat":
        return json_response(handler, {"error": "Neautorizat"}, 401)

    try:
        data = json.loads(body)
    except Exception:
        return json_response(handler, {"error": "JSON invalid"}, 400)

    status = data.get("status")
    if status not in ("confirmata", "respinsa"):
        return json_response(handler, {"error": "Status invalid"}, 400)

    observatii = data.get("observatii", "").strip()

    with get_db() as conn:
        row = conn.execute("SELECT id FROM adeverinte WHERE id = ?", (adv_id,)).fetchone()
        if not row:
            return json_response(handler, {"error": "Negasit"}, 404)

        conn.execute(
            """
            UPDATE adeverinte
            SET status = ?, observatii = ?, reviewed_at = datetime('now'), reviewed_by = ?
            WHERE id = ?
            """,
            (status, observatii, user["id"], adv_id),
        )
        conn.commit()

    return json_response(handler, {"ok": True})


def handle_grupuri(handler, headers):
    user = get_user_from_token(get_token_from_headers(headers))
    if not user or user["role"] != "secretariat":
        return json_response(handler, {"error": "Neautorizat"}, 401)

    with get_db() as conn:
        rows = conn.execute(
            """
            SELECT DISTINCT grupa
            FROM users
            WHERE role = 'student' AND grupa IS NOT NULL
            ORDER BY grupa
            """
        ).fetchall()

    return json_response(handler, [row["grupa"] for row in rows])


def handle_stats(handler, headers):
    user = get_user_from_token(get_token_from_headers(headers))
    if not user or user["role"] != "secretariat":
        return json_response(handler, {"error": "Neautorizat"}, 401)

    with get_db() as conn:
        total = conn.execute("SELECT COUNT(*) FROM adeverinte").fetchone()[0]
        pending = conn.execute(
            "SELECT COUNT(*) FROM adeverinte WHERE status = 'in_asteptare'"
        ).fetchone()[0]
        confirmed = conn.execute(
            "SELECT COUNT(*) FROM adeverinte WHERE status = 'confirmata'"
        ).fetchone()[0]
        rejected = conn.execute(
            "SELECT COUNT(*) FROM adeverinte WHERE status = 'respinsa'"
        ).fetchone()[0]
        students = conn.execute(
            "SELECT COUNT(*) FROM users WHERE role = 'student'"
        ).fetchone()[0]

    return json_response(
        handler,
        {
            "total": total,
            "pending": pending,
            "confirmed": confirmed,
            "rejected": rejected,
            "students": students,
        },
    )
