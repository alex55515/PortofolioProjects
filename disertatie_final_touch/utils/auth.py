import hashlib
import uuid

from db.database import get_db


def hash_pw(password):
    return hashlib.sha256(password.encode()).hexdigest()


def create_session(user_id):
    token = str(uuid.uuid4())
    with get_db() as conn:
        conn.execute("DELETE FROM sessions WHERE user_id = ?", (user_id,))
        conn.execute("INSERT INTO sessions (token, user_id) VALUES (?, ?)", (token, user_id))
        conn.commit()
    return token


def destroy_session(token):
    if not token:
        return

    with get_db() as conn:
        conn.execute("DELETE FROM sessions WHERE token = ?", (token,))
        conn.commit()


def get_user_from_token(token):
    if not token:
        return None

    with get_db() as conn:
        row = conn.execute(
            """
            SELECT u.*
            FROM users u
            JOIN sessions s ON s.user_id = u.id
            WHERE s.token = ?
            """,
            (token,),
        ).fetchone()

    return dict(row) if row else None


def get_token_from_headers(headers):
    auth = headers.get("Authorization", "")
    if auth.startswith("Bearer "):
        return auth[7:]

    cookie = headers.get("Cookie", "")
    for part in cookie.split(";"):
        item = part.strip()
        if item.startswith("token="):
            return item[6:]

    return ""
