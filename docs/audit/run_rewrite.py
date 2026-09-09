#!/usr/bin/env python3
"""Rewrite Elegant posts via REST apply endpoint."""
from __future__ import annotations

import base64
import json
import os
import sys
import time
import urllib.error
import urllib.request
from typing import Any, Dict, List

from elegant_article_gen import build_article, detect_city, detect_service, word_count

BASE = "https://elegantswimmingpools.com"
UA = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"


def auth_header() -> str:
    user = os.environ["WPUSER"]
    pw = os.environ["WPPASS"]
    return "Basic " + base64.b64encode(f"{user}:{pw}".encode()).decode()


def request(method: str, url: str, data: Any = None, timeout: int = 120) -> Any:
    body = None
    headers = {"Authorization": auth_header(), "User-Agent": UA, "Accept": "application/json"}
    if data is not None:
        body = json.dumps(data, ensure_ascii=False).encode("utf-8")
        headers["Content-Type"] = "application/json; charset=utf-8"
    req = urllib.request.Request(url, data=body, headers=headers, method=method)
    try:
        with urllib.request.urlopen(req, timeout=timeout) as r:
            raw = r.read().decode("utf-8")
            return json.loads(raw) if raw else {}
    except urllib.error.HTTPError as e:
        err = e.read().decode("utf-8", "replace")
        raise RuntimeError(f"{method} {url} -> {e.code} {err[:800]}") from e


CACHE = "/tmp/elegant_posts_list.json"
DONE = "/tmp/elegant_rewrite_done.txt"


def list_posts() -> List[dict]:
    if os.path.exists(CACHE) and os.environ.get("REWRITE_REFRESH") != "1":
        with open(CACHE, encoding="utf-8") as f:
            posts = json.load(f)
        print(f"cache {len(posts)} posts", flush=True)
        return posts
    posts: List[dict] = []
    page = 1
    while True:
        data = request(
            "GET",
            f"{BASE}/wp-json/wp/v2/posts?per_page=100&page={page}&status=publish&_fields=id,title,slug,link",
        )
        if not data:
            break
        posts.extend(data)
        if len(data) < 100:
            break
        page += 1
        print(f"listed page {page-1} total {len(posts)}", flush=True)
    with open(CACHE, "w", encoding="utf-8") as f:
        json.dump(posts, f, ensure_ascii=False)
    return posts


def catalog_of(posts: List[dict]) -> List[dict]:
    out = []
    for p in posts:
        title = p["title"]["rendered"] if isinstance(p["title"], dict) else p["title"]
        slug = p.get("slug") or ""
        out.append(
            {
                "id": p["id"],
                "title": title,
                "link": p.get("link") or f"{BASE}/{slug}/",
                "city": detect_city(title, slug),
                "service": detect_service(title, slug),
            }
        )
    return out


def apply_payload(payload: dict) -> dict:
    send = {k: v for k, v in payload.items() if not k.startswith("_")}
    return request("POST", f"{BASE}/wp-json/elegant/v1/apply", send, timeout=180)


def main():
    limit = int(os.environ.get("REWRITE_LIMIT", "0"))
    offset = int(os.environ.get("REWRITE_OFFSET", "0"))
    only = os.environ.get("REWRITE_IDS", "").strip()
    dry = os.environ.get("REWRITE_DRY", "") == "1"

    posts = list_posts()
    cat = catalog_of(posts)
    if only:
        ids = {int(x) for x in only.split(",") if x.strip()}
        posts = [p for p in posts if p["id"] in ids]
    else:
        posts = posts[offset:]
        if limit:
            posts = posts[:limit]

    done = set()
    if os.path.exists(DONE) and not only:
        done = {int(x) for x in open(DONE) if x.strip().isdigit()}
        posts = [p for p in posts if p["id"] not in done]
    print(f"to process: {len(posts)} dry={dry} skipped_done={len(done)}", flush=True)
    ok = fail = 0
    words = []
    for i, p in enumerate(posts, 1):
        try:
            payload = build_article(p, cat)
            w = payload["_debug"]["words"]
            words.append(w)
            if dry:
                print(f"{i}/{len(posts)} DRY id={p['id']} words={w} {payload['_debug']}", flush=True)
                ok += 1
                continue
            res = apply_payload(payload)
            print(
                f"{i}/{len(posts)} OK id={res.get('id')} words={res.get('words')} title={res.get('title','')[:70]}",
                flush=True,
            )
            with open(DONE, "a") as f:
                f.write(str(p["id"]) + "\n")
            ok += 1
        except Exception as e:
            fail += 1
            print(f"{i}/{len(posts)} FAIL id={p['id']} {e}", flush=True)
            time.sleep(1)
        if i % 25 == 0:
            time.sleep(0.4)
    print(json.dumps({"ok": ok, "fail": fail, "min_words": min(words) if words else 0, "max_words": max(words) if words else 0}, ensure_ascii=False))


if __name__ == "__main__":
    sys.exit(main() or 0)
