#!/bin/sh
# Creates/updates policy pages and assigns them in WordPress & WooCommerce settings.
# Idempotent — safe to run multiple times.
# (Chunk 1M-alpha)
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

echo "=== Foxfire Peptides — Policy Pages (Chunk 1M-alpha) ==="

# Privacy Policy
pp_id="$(ensure_page "Privacy Policy" "privacy-policy" "page-privacy-policy.php" "<p>[DRAFT] See Privacy Policy page template for full content.</p>")"

# Terms & Conditions
tc_id="$(ensure_page "Terms & Conditions" "terms-and-conditions" "page-terms-and-conditions.php" "<p>[DRAFT] See Terms and Conditions page template for full content.</p>")"

# Refund & Returns
rr_id="$(ensure_page "Refund & Returns Policy" "refund-and-returns-policy" "page-refund-and-returns.php" "<p>[DRAFT] See Refund and Returns Policy page template for full content.</p>")"

# Shipping Policy
sp_id="$(ensure_page "Shipping Policy" "shipping-policy" "page-shipping-policy.php" "<p>[DRAFT] See Shipping Policy page template for full content.</p>")"

echo ""
echo "Assigning WordPress Privacy Policy page..."
wp option update wp_page_for_privacy_policy "${pp_id}" >/dev/null
echo "  WordPress privacy page set to ID: ${pp_id}"

echo ""
echo "Assigning WooCommerce Terms page..."
wp option update woocommerce_terms_page_id "${tc_id}" >/dev/null
echo "  WooCommerce terms page set to ID: ${tc_id}"

echo ""
echo "Done. Policy pages:"
echo "  Privacy Policy:      ${WP_URL}/privacy-policy/"
echo "  Terms & Conditions:  ${WP_URL}/terms-and-conditions/"
echo "  Refund & Returns:    ${WP_URL}/refund-and-returns-policy/"
echo "  Shipping Policy:     ${WP_URL}/shipping-policy/"
