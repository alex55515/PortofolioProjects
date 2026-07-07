import hashlib
import sqlite3

from config import DB_PATH


SEED_USERS = [
    ("student1", "parola123", "student", "Popescu Ion", "CTI-301"),
    ("student2", "parola123", "student", "Ionescu Maria", "CTI-302"),
    ("student3", "parola123", "student", "Georgescu Andrei", "INFO-201"),
    ("secretariat1", "admin123", "secretariat", "Secretariat A", None),
    ("secretariat2", "admin123", "secretariat", "Secretariat B", None),
]


def get_db():
    conn = sqlite3.connect(DB_PATH)
    conn.row_factory = sqlite3.Row
    conn.execute("PRAGMA journal_mode=WAL")
    return conn


def init_db():
    with get_db() as conn:
        conn.executescript(
            """
            CREATE TABLE IF NOT EXISTS users (
                id       INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT    UNIQUE NOT NULL,
                password TEXT    NOT NULL,
                role     TEXT    NOT NULL CHECK(role IN ('student','secretariat')),
                nume     TEXT    NOT NULL,
                grupa    TEXT,
                created  TEXT    DEFAULT (datetime('now'))
            );

            CREATE TABLE IF NOT EXISTS sessions (
                token    TEXT PRIMARY KEY,
                user_id  INTEGER NOT NULL,
                created  TEXT DEFAULT (datetime('now')),
                FOREIGN KEY(user_id) REFERENCES users(id)
            );

            CREATE TABLE IF NOT EXISTS adeverinte (
                id            INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id       INTEGER NOT NULL,
                filename      TEXT    NOT NULL,
                file_data     BLOB    NOT NULL,
                file_type     TEXT    NOT NULL,
                data_prezenta TEXT,
                ora_start     TEXT,
                ora_end       TEXT,
                ore_text      TEXT,
                status        TEXT    DEFAULT 'in_asteptare'
                                    CHECK(status IN ('in_asteptare','confirmata','respinsa')),
                observatii    TEXT,
                uploaded_at   TEXT    DEFAULT (datetime('now')),
                reviewed_at   TEXT,
                reviewed_by   INTEGER,
                FOREIGN KEY(user_id) REFERENCES users(id),
                FOREIGN KEY(reviewed_by) REFERENCES users(id)
            );
            """
        )

        for username, password, role, nume, grupa in SEED_USERS:
            try:
                conn.execute(
                    "INSERT INTO users (username, password, role, nume, grupa) VALUES (?, ?, ?, ?, ?)",
                    (username, _hash_pw(password), role, nume, grupa),
                )
            except sqlite3.IntegrityError:
                pass

        conn.commit()


def _hash_pw(password):
    return hashlib.sha256(password.encode()).hexdigest()
