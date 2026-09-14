#!/bin/sh
# Creates/updates About Us and Contact Us pages and assigns their custom templates.
# Idempotent — safe to run multiple times.
# (Chunk 1M-beta)
set -e

WP_URL="${WP_URL:-http://localhost:8080}"

ensure_page() {
  title="$1"
  slug="$2"
  template="$3"
  content="$4"

  page_id="$(wp post list --post_type=page --name="${slug}" --field=ID --format=ids 2>/dev/null | tr ' ' '\n' | grep -E '^[0-9]+$' | tail -n1 || true)"

  if [ -z "${page_id}" ]; then
    page_id="$(wp post create --post_type=page --post_title="${title}" --post_name="${slug}" --post_status=publish --post_content="${content}" --porcelain)"
    echo "Created page: ${title} (${page_id})" >&2
  else
    wp post update "${page_id}" --post_title="${title}" --post_status=publish >/dev/null
    echo "Page exists: ${title} (${page_id})" >&2
  fi

  # Assign page template
  if [ -n "${template}" ]; then
    wp post meta update "${page_id}" "_wp_page_template" "${template}" >/dev/null 2>&1 || true
  fi

  echo "${page_id}"
}

echo "=== Foxfire Peptides — About & Contact Pages (Chunk 1M-beta) ==="

# About Us
about_id="$(ensure_page "About Us" "about-us" "page-about.php" "See About Us page template for full content.")"

# Contact Us
contact_id="$(ensure_page "Contact Us" "contact-us" "page-contact.php" "See Contact Us page template for full content.")"

# Check if 'about' or 'contact' alias pages exist or should be ensured
about_short_id="$(wp post list --post_type=page --name="about" --field=ID --format=ids 2>/dev/null | tr ' ' '\n' | grep -E '^[0-9]+$' | tail -n1 || true)"
if [ -n "${about_short_id}" ]; then
  wp post meta update "${about_short_id}" "_wp_page_template" "page-about.php" >/dev/null 2>&1 || true
  echo "Updated template for existing 'about' page (${about_short_id})" >&2
fi

contact_short_id="$(wp post list --post_type=page --name="contact" --field=ID --format=ids 2>/dev/null | tr ' ' '\n' | grep -E '^[0-9]+$' | tail -n1 || true)"
if [ -n "${contact_short_id}" ]; then
  wp post meta update "${contact_short_id}" "_wp_page_template" "page-contact.php" >/dev/null 2>&1 || true
  echo "Updated template for existing 'contact' page (${contact_short_id})" >&2
fi

echo ""
echo "Done. Pages ready:"
echo "  About Us:   ${WP_URL}/about-us/ (ID: ${about_id})"
echo "  Contact Us: ${WP_URL}/contact-us/ (ID: ${contact_id})"
