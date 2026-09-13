#!/bin/sh
# Creates stub pages required by Chunk 1D navigation.
set -e

WP_URL="${WP_URL:-http://localhost:8080}"

wp() {
  command wp "$@" --url="${WP_URL}"
}

ensure_stub_page() {
  title="$1"
  slug="$2"
  content="$3"
  page_id="$(wp post list --post_type=page --name="${slug}" --field=ID --format=ids 2>/dev/null | tr ' ' '\n' | grep -E '^[0-9]+$' | tail -n1 || true)"

  if [ -z "${page_id}" ]; then
    page_id="$(wp post create --post_type=page --post_title="${title}" --post_name="${slug}" --post_status=publish --post_content="${content}" --porcelain)"
    echo "Created page: ${title} (${page_id})"
  else
    wp post update "${page_id}" --post_status=publish --post_content="${content}" >/dev/null
    echo "Page exists: ${title} (${page_id})"
  fi
}

echo "Ensuring navigation stub pages (Chunk 1D / 1H)..."

ensure_stub_page "Home" "home" "<p>[PLACEHOLDER] Homepage content rendered via front-page.php template.</p>"
home_id="$(wp post list --post_type=page --name="home" --field=ID --format=ids 2>/dev/null | tr ' ' '\n' | grep -E '^[0-9]+$' | tail -n1 || true)"
if [ -n "${home_id}" ]; then
  wp option update show_on_front page >/dev/null
  wp option update page_on_front "${home_id}" >/dev/null
  echo "Front page set to Home (${home_id})"
fi

ensure_stub_page "Testing / COA" "testing-coa" "<p>[PLACEHOLDER] Client testing and Certificate of Analysis content will appear here.</p>"
ensure_stub_page "About" "about" "<p>[PLACEHOLDER] Client brand story and about content will appear here.</p>"
ensure_stub_page "Contact" "contact" "<p>[PLACEHOLDER] Client contact information and form will appear here (Chunk 1M).</p>"
ensure_stub_page "Frequently Asked Questions" "faq" ""
faq_id="$(wp post list --post_type=page --name="faq" --field=ID --format=ids 2>/dev/null | tr ' ' '\n' | grep -E '^[0-9]+$' | tail -n1 || true)"
if [ -n "${faq_id}" ]; then
  wp post meta update "${faq_id}" _wp_page_template page-faq.php >/dev/null
  echo "FAQ template assigned (${faq_id})"
fi

echo "Navigation stub pages and front page ready."
