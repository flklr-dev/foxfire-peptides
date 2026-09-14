"""Repeatable public-page component checks for the Foxfire local/staging site."""

import html as html_module
import os
import re
import sys
from urllib.parse import urljoin
from urllib.request import Request, urlopen

sys.stdout.reconfigure(encoding="utf-8")

BASE_URL = os.environ.get("WP_URL", "http://localhost:8080").rstrip("/") + "/"
failures: list[str] = []


def fetch(path: str) -> tuple[str, str]:
    """Fetch a public route and return its final URL and decoded HTML."""
    url = urljoin(BASE_URL, path.lstrip("/"))
    request = Request(url, headers={"User-Agent": "Foxfire-QA/1.0"})
    with urlopen(request, timeout=15) as response:  # noqa: S310 - target is operator-controlled.
        if response.status != 200:
            raise RuntimeError(f"{url} returned HTTP {response.status}")
        return response.geturl(), response.read().decode("utf-8", errors="replace")


def expect(route: str, label: str, condition: bool) -> None:
    """Record and print a named assertion."""
    print(f"{'PASS' if condition else 'FAIL'}: {label}")
    if not condition:
        failures.append(f"{route}: {label}")


def check_common(route: str, document: str) -> None:
    """Apply structural checks shared by every public page."""
    h1_matches = re.findall(r"<h1\b[^>]*>(.*?)</h1>", document, re.DOTALL | re.IGNORECASE)
    heading = re.sub(r"<[^>]+>", "", h1_matches[0]).strip() if h1_matches else "None"
    print("H1:", html_module.unescape(heading))
    expect(route, "exactly one H1", len(h1_matches) == 1)
    expect(route, "skip link targets main content", 'href="#main-content"' in document)
    expect(route, "main content target exists", 'id="main-content"' in document)
    expect(route, "footer includes FAQ route", 'href="' + urljoin(BASE_URL, "faq/") + '"' in document)


def check_about() -> None:
    route = "/about/"
    final_url, document = fetch(route)
    print(f"=== CHECK: {final_url} ===")
    check_common(route, document)
    images = re.findall(r'<img[^>]+src=["\']([^"\']+)["\']', document, re.IGNORECASE)
    expect(route, "three bento hero images", len([item for item in images if "about-hero" in item]) == 3)
    expect(route, "bento grid rendered", "ff-about-bento-grid" in document)
    expect(route, "Testing/COA link rendered", "/testing-coa/" in document)
    expect(route, "shop CTA rendered", "Explore Products" in document)


def check_contact() -> None:
    route = "/contact/"
    final_url, document = fetch(route)
    print(f"\n=== CHECK: {final_url} ===")
    check_common(route, document)
    checks = {
        "contact form rendered": "ff-contact-form" in document,
        "CSRF nonce rendered": "foxfire_contact_nonce" in document,
        "honeypot field rendered": "ff_website_hp" in document,
        "direct contact panel rendered": "ff-contact-info-card" in document,
        "obsolete Quick Help block absent": "Quick Help" not in document and "ff-quick-links-list" not in document,
        "support email rendered": "support@foxfirepeptides.com" in document,
        "response-time content rendered": "12–24 business hours" in document,
        "support hours rendered": "Mon – Fri, 9 AM – 5 PM EST" in document,
        "trust note rendered": "ff-contact-trust-note" in document,
        "topic selector rendered": "ff_subject" in document,
    }
    for label, condition in checks.items():
        expect(route, label, condition)


def check_faq() -> None:
    route = "/faq/"
    final_url, document = fetch(route)
    print(f"\n=== CHECK: {final_url} ===")
    check_common(route, document)
    expect(route, "FAQ page template rendered", "ff-faq-page" in document)
    expect(route, "five to eight FAQ items rendered", 5 <= document.count('class="ff-home-faq__item"') <= 8)
    expect(route, "COA availability wording is conditional", "only when a document has been attached" in document)
    expect(route, "research-use-only answer rendered", "not for human consumption" in document)


try:
    check_about()
    check_contact()
    check_faq()
except Exception as error:  # Network/HTTP failures must fail the QA run clearly.
    failures.append(str(error))
    print(f"FAIL: {error}")

if failures:
    print(f"\nPublic page verification failed: {len(failures)} issue(s).")
    for failure in failures:
        print(f"- {failure}")
    raise SystemExit(1)

print("\nPublic page verification passed.")
