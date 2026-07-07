import json
import sqlite3

from db.database import get_db
from utils.auth import (
    create_session,
    destroy_session,
    get_token_from_headers,
    get_user_from_token,
    hash_pw,
)
from utils.helpers import json_response


def handle_login(handler, body):
    try:
        data = json.loads(body)
    except Exception:
        return json_response(handler, {"error": "JSON invalid"}, 400)

    username = data.get("username", "").strip()
    password = data.get("password", "").strip()

    with get_db() as conn:
        user = conn.execute(
            "SELECT * FROM users WHERE username = ? AND password = ?",
            (username, hash_pw(password)),
        ).fetchone()

    if not user:
        return json_response(handler, {"error": "Credentiale invalide"}, 401)

    token = create_session(user["id"])
    return json_response(
        handler,
        {
            "token": token,
            "role": user["role"],
            "nume": user["nume"],
            "id": user["id"],
        },
    )


def handle_register(handler, body):
    try:
        data = json.loads(body)
    except Exception:
        return json_response(handler, {"error": "JSON invalid"}, 400)

    username = data.get("username", "").strip()
    password = data.get("password", "").strip()
    nume = data.get("nume", "").strip()
    grupa = data.get("grupa", "").strip()

    if not username or not password or not nume:
        return json_response(handler, {"error": "Campuri obligatorii lipsa"}, 400)

    if len(password) < 6:
        return json_response(handler, {"error": "Parola trebuie sa aiba minim 6 caractere"}, 400)

    try:
        with get_db() as conn:
            conn.execute(
                "INSERT INTO users (username, password, role, nume, grupa) VALUES (?, ?, ?, ?, ?)",
                (username, hash_pw(password), "student", nume, grupa or None),
            )
            conn.commit()
    except sqlite3.IntegrityError:
        return json_response(handler, {"error": "Username-ul exista deja"}, 409)

    return json_response(handler, {"ok": True})


def handle_me(handler, headers):
    user = get_user_from_token(get_token_from_headers(headers))
    if not user:
        return json_response(handler, {"error": "Neautentificat"}, 401)

    return json_response(
        handler,
        {key: user[key] for key in ("id", "username", "role", "nume", "grupa")},
    )


def handle_logout(handler, headers):
    destroy_session(get_token_from_headers(headers))
    return json_response(handler, {"ok": True})
