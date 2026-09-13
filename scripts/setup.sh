#!/usr/bin/env bash
# Foxfire Peptides — local WordPress setup (Chunk 1A)
# Usage: ./scripts/setup.sh

set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo ""
echo "=== Foxfire Peptides — Local Setup ==="

# 1. Ensure .env exists
if [ ! -f .env ]; then
  cp .env.example .env
  echo "Created .env from .env.example"
fi

set -a
# shellcheck disable=SC1091
source .env
set +a

WP_URL="${WP_URL:-http://localhost:8080}"

# 2. Ensure wp-content directories exist
mkdir -p wp-content/themes wp-content/plugins wp-content/mu-plugins wp-content/uploads

# 3. Start containers
echo ""
echo "Starting Docker containers..."
docker compose up -d

# 4. Wait for WordPress
echo "Waiting for WordPress at ${WP_URL} ..."
for i in $(seq 1 30); do
  if curl -sf "${WP_URL}" > /dev/null 2>&1; then
    break
  fi
  sleep 3
done

# 5. Install WordPress if needed
echo ""
echo "Checking WordPress installation..."
if ! docker compose run --rm wpcli core is-installed --url="${WP_URL}" 2>/dev/null; then
  echo "Installing WordPress..."
  docker compose run --rm wpcli core install \
    --url="${WP_URL}" \
    --title="${WP_TITLE}" \
    --admin_user="${WP_ADMIN_USER}" \
    --admin_password="${WP_ADMIN_PASSWORD}" \
    --admin_email="${WP_ADMIN_EMAIL}" \
    --skip-email
  echo "WordPress installed."
else
  echo "WordPress already installed — skipping."
fi

# 6. Install WooCommerce if needed
echo ""
echo "Checking WooCommerce..."
if ! docker compose run --rm wpcli plugin is-active woocommerce --url="${WP_URL}" 2>/dev/null; then
  echo "Installing WooCommerce..."
  docker compose run --rm wpcli plugin install woocommerce --activate --url="${WP_URL}"
  if [ $? -ne 0 ]; then
    echo "WooCommerce install failed - retrying activation..."
    docker compose run --rm wpcli plugin activate woocommerce --url="${WP_URL}"
  fi
  echo "WooCommerce installed and activated."
else
  echo "WooCommerce already active — skipping."
fi

# 7. Install Advanced Custom Fields if needed
echo ""
echo "Checking Advanced Custom Fields..."
if ! docker compose run --rm wpcli plugin is-active advanced-custom-fields --url="${WP_URL}" 2>/dev/null; then
  echo "Installing Advanced Custom Fields..."
  docker compose run --rm wpcli plugin install advanced-custom-fields --activate --url="${WP_URL}"
fi

# 8. Install Storefront parent theme + activate Foxfire child theme
echo ""
echo "Checking themes..."
if ! docker compose run --rm wpcli theme is-installed storefront --url="${WP_URL}" 2>/dev/null; then
  echo "Installing Storefront parent theme..."
  docker compose run --rm wpcli theme install storefront --url="${WP_URL}"
fi
docker compose run --rm wpcli theme activate foxfire-child --url="${WP_URL}"

# 9. Activate the theme-independent Foxfire Operations plugin (Chunk 1P)
echo ""
echo "Activating Foxfire Operations..."
docker compose run --rm wpcli plugin activate foxfire-operations --url="${WP_URL}"

# 10. Configure WooCommerce store (Chunk 1C)
echo ""
echo "Configuring WooCommerce store..."
./scripts/configure-store.sh

# 11. Summary
echo "Site:     ${WP_URL}"
echo "Admin:    ${WP_URL}/wp-admin"
echo "User:     ${WP_ADMIN_USER}"
echo "Password: ${WP_ADMIN_PASSWORD}  (change in .env for next fresh install)"
echo ""
