#!/bin/sh
# Runs inside the wpcli container - batch store configuration (Chunk 1C)
set -e

WP_URL="${WP_URL:-http://localhost:8080}"

wp() {
  command wp "$@" --url="${WP_URL}"
}

ensure_page() {
  title="$1"
  slug="$2"
  option_key="$3"
  content="${4:-}"
  page_id="$(wp post list --post_type=page --name="${slug}" --field=ID --format=ids 2>/dev/null | tr ' ' '\n' | grep -E '^[0-9]+$' | tail -n1 || true)"
  if [ -z "${page_id}" ]; then
    if [ -n "${content}" ]; then
      page_id="$(wp post create --post_type=page --post_title="${title}" --post_name="${slug}" --post_status=publish --post_content="${content}" --porcelain)"
    else
      page_id="$(wp post create --post_type=page --post_title="${title}" --post_name="${slug}" --post_status=publish --porcelain)"
    fi
    echo "Created page: ${title} (${page_id})"
  else
    if [ -n "${content}" ]; then
      wp post update "${page_id}" --post_content="${content}" >/dev/null
    fi
    echo "Page exists: ${title} (${page_id})"
  fi
  wp option update "${option_key}" "${page_id}" >/dev/null
}

wp plugin is-active woocommerce

echo "Ensuring WooCommerce pages..."
ensure_page "Shop" "shop" "woocommerce_shop_page_id"
ensure_page "Cart" "cart" "woocommerce_cart_page_id" "[woocommerce_cart]"
ensure_page "Checkout" "checkout" "woocommerce_checkout_page_id" "[woocommerce_checkout]"
ensure_page "My Account" "my-account" "woocommerce_myaccount_page_id" "[woocommerce_my_account]"
ensure_page "Terms and Conditions" "terms-and-conditions" "woocommerce_terms_page_id"

echo "Applying store settings..."
wp option update woocommerce_currency USD
wp option update woocommerce_currency_pos left
wp option update woocommerce_price_thousand_sep ","
wp option update woocommerce_price_decimal_sep "."
wp option update woocommerce_price_num_decimals 2
wp option update woocommerce_default_country US
wp option update woocommerce_allowed_countries all
wp option update woocommerce_ship_to_countries ""
wp option update woocommerce_default_customer_address base
wp option update woocommerce_calc_taxes no
wp option update woocommerce_enable_reviews no
wp option update woocommerce_review_rating_required no
wp option update woocommerce_enable_shipping_calc no
wp option update woocommerce_enable_guest_checkout yes
wp option update woocommerce_enable_checkout_login_reminder yes
wp option update woocommerce_enable_signup_and_login_from_checkout yes
wp option update woocommerce_manage_stock yes
wp option update woocommerce_hold_stock_minutes 60
wp option update woocommerce_notify_low_stock_amount 2
wp option update woocommerce_notify_no_stock_amount 0
wp option update woocommerce_hide_out_of_stock_items no
wp option update woocommerce_weight_unit oz
wp option update woocommerce_dimension_unit in
wp option update blogname "Foxfire Peptides"
wp option update woocommerce_onboarding_profile '{"completed":true}'
wp option update show_on_front page
wp option update woocommerce_permalinks '{"product_base":"product","category_base":"product-category","tag_base":"product-tag","attribute_base":"","use_verbose_page_rules":false}'
wp rewrite structure '/%postname%/'
wp rewrite flush --hard

echo "Store configuration complete."
