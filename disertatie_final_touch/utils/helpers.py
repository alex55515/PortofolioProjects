import json
import math

from config import PAGE_SIZE


def read_body(rfile, headers):
    length = int(headers.get("Content-Length", 0))
    return rfile.read(length)


def json_response(handler, data, status=200):
    body = json.dumps(data, ensure_ascii=False, default=str).encode("utf-8")
    handler.send_response(status)
    handler.send_header("Content-Type", "application/json; charset=utf-8")
    handler.send_header("Content-Length", len(body))
    handler.send_header("Access-Control-Allow-Origin", "*")
    handler.end_headers()
    handler.wfile.write(body)


def parse_multipart(body, boundary):
    import re

    result = {}
    boundary_bytes = ("--" + boundary).encode()
    parts = body.split(boundary_bytes)

    for part in parts[1:]:
        if part in (b"--\r\n", b"--"):
            continue

        separator = b"\r\n\r\n"
        index = part.find(separator)
        if index == -1:
            continue

        header_raw = part[:index].decode("utf-8", errors="ignore")
        data = part[index + 4:]

        if data.endswith(b"\r\n--"):
            data = data[:-4]
        elif data.endswith(b"\r\n"):
            data = data[:-2]

        content_disposition = re.search(r'name="([^"]+)"', header_raw)
        filename = re.search(r'filename="([^"]*)"', header_raw)
        if content_disposition:
            result[content_disposition.group(1)] = (
                filename.group(1) if filename else None,
                data,
            )

    return result


def get_requested_page(qs):
    raw_page = qs.get("page", ["1"])[0].strip()
    try:
        return max(1, int(raw_page))
    except ValueError:
        return 1


def get_pagination(total_items, requested_page, page_size=PAGE_SIZE):
    total_pages = max(1, math.ceil(total_items / page_size)) if total_items else 1
    page = min(max(1, requested_page), total_pages)
    offset = (page - 1) * page_size

    return (
        {
            "page": page,
            "page_size": page_size,
            "total_items": total_items,
            "total_pages": total_pages,
            "has_prev": page > 1,
            "has_next": page < total_pages,
        },
        offset,
    )
