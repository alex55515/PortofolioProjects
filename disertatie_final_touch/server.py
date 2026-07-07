import http.server
import os
import threading
import webbrowser
from urllib.parse import parse_qs, urlparse

from config import BASE_DIR, PORT
from db.database import init_db
from handlers.auth_handler import handle_login, handle_logout, handle_me, handle_register
from handlers.file_handler import handle_download
from handlers.secretariat_handler import (
    handle_grupuri,
    handle_review,
    handle_sec_adeverinte,
    handle_sec_students,
    handle_stats,
)
from handlers.student_handler import (
    handle_delete_adeverinta,
    handle_my_adeverinte,
    handle_upload,
)
from utils.helpers import json_response, read_body


STATIC_FILES = {
    "/": ("static/index.html", "text/html; charset=utf-8"),
    "/index.html": ("static/index.html", "text/html; charset=utf-8"),
    "/static/index.html": ("static/index.html", "text/html; charset=utf-8"),
    "/css/style.css": ("static/css/style.css", "text/css; charset=utf-8"),
    "/static/css/style.css": ("static/css/style.css", "text/css; charset=utf-8"),
    "/js/script.js": ("static/js/script.js", "application/javascript; charset=utf-8"),
    "/static/js/script.js": ("static/js/script.js", "application/javascript; charset=utf-8"),
}


class Handler(http.server.BaseHTTPRequestHandler):
    def log_message(self, fmt, *args):
        pass

    def do_OPTIONS(self):
        self.send_response(204)
        self.send_header("Access-Control-Allow-Origin", "*")
        self.send_header("Access-Control-Allow-Methods", "GET,POST,DELETE,OPTIONS")
        self.send_header("Access-Control-Allow-Headers", "Content-Type,Authorization")
        self.end_headers()

    def do_GET(self):
        parsed = urlparse(self.path)
        path = parsed.path.rstrip("/") or "/"
        qs = parse_qs(parsed.query)

        if path in STATIC_FILES:
            relative_path, content_type = STATIC_FILES[path]
            return self._serve_file(os.path.join(BASE_DIR, relative_path), content_type)

        if path == "/api/me":
            return handle_me(self, self.headers)
        if path == "/api/adeverinte/my":
            return handle_my_adeverinte(self, self.headers, qs)
        if path == "/api/sec/students":
            return handle_sec_students(self, self.headers, qs)
        if path == "/api/sec/adeverinte":
            return handle_sec_adeverinte(self, self.headers, qs)
        if path.startswith("/api/sec/students/") and path.endswith("/adeverinte"):
            parts = path.split("/")
            try:
                student_id = int(parts[4])
            except (IndexError, ValueError):
                return json_response(self, {"error": "ID invalid"}, 400)
            return handle_sec_adeverinte(self, self.headers, qs, student_id)
        if path.startswith("/api/download/"):
            return self._handle_download_with_optional_query_token(path, qs)
        if path == "/api/sec/grupuri":
            return handle_grupuri(self, self.headers)
        if path == "/api/sec/stats":
            return handle_stats(self, self.headers)

        return json_response(self, {"error": "Not found"}, 404)

    def do_POST(self):
        parsed = urlparse(self.path)
        path = parsed.path.rstrip("/")
        body = read_body(self.rfile, self.headers)

        if path == "/api/login":
            return handle_login(self, body)
        if path == "/api/register":
            return handle_register(self, body)
        if path == "/api/logout":
            return handle_logout(self, self.headers)
        if path == "/api/upload":
            return handle_upload(self, self.headers, body)
        if path.startswith("/api/adeverinte/") and path.endswith("/review"):
            parts = path.split("/")
            try:
                adv_id = int(parts[3])
            except (IndexError, ValueError):
                return json_response(self, {"error": "ID invalid"}, 400)
            return handle_review(self, self.headers, body, adv_id)

        return json_response(self, {"error": "Not found"}, 404)

    def do_DELETE(self):
        parsed = urlparse(self.path)
        path = parsed.path.rstrip("/")

        if path.startswith("/api/adeverinte/"):
            try:
                adv_id = int(path.split("/")[-1])
            except ValueError:
                return json_response(self, {"error": "ID invalid"}, 400)
            return handle_delete_adeverinta(self, self.headers, adv_id)

        return json_response(self, {"error": "Not found"}, 404)

    def _handle_download_with_optional_query_token(self, path, qs):
        try:
            adv_id = int(path.split("/")[-1])
        except ValueError:
            return json_response(self, {"error": "ID invalid"}, 400)

        token_from_query = qs.get("token", [""])[0]
        if not token_from_query:
            return handle_download(self, self.headers, adv_id)

        original_headers = self.headers

        class ForwardedHeaders(dict):
            def get(self, key, default=""):
                if key == "Authorization":
                    return f"Bearer {token_from_query}"
                return original_headers.get(key, default)

        return handle_download(self, ForwardedHeaders(), adv_id)

    def _serve_file(self, filepath, content_type):
        try:
            with open(filepath, "rb") as file_handle:
                data = file_handle.read()
        except FileNotFoundError:
            return json_response(self, {"error": "Not found"}, 404)

        self.send_response(200)
        self.send_header("Content-Type", content_type)
        self.send_header("Content-Length", len(data))
        self.end_headers()
        self.wfile.write(data)


def main():
    init_db()
    server = http.server.HTTPServer(("127.0.0.1", PORT), Handler)
    url = f"http://127.0.0.1:{PORT}"
    print(f"Adeverinte Server pornit la {url}")
    print(f"Daca browserul nu se deschide automat, acceseaza manual: {url}")
    print("Ctrl+C pentru a opri")
    print("")
    print("Conturi demo:")
    print("  student1 / parola123  (student)")
    print("  secretariat1 / admin123  (secretariat)")

    threading.Timer(0.8, lambda: open_browser(url)).start()

    try:
        server.serve_forever()
    except KeyboardInterrupt:
        print("\nOprit.")


def open_browser(url):
    try:
        if os.name == "nt":
            os.startfile(url)
            return
    except Exception:
        pass

    try:
        webbrowser.open(url)
    except Exception:
        pass


if __name__ == "__main__":
    main()
