#!/usr/bin/env bash
# Foxfire Peptides - WooCommerce store configuration (Chunk 1C)
# Usage: ./scripts/configure-store.sh

set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

if [ ! -f .env ]; then
  echo ".env not found. Run ./scripts/setup.sh first."
  exit 1
fi

set -a
# shellcheck disable=SC1091
source .env
set +a

WP_URL="${WP_URL:-http://localhost:8080}"

echo ""
echo "=== Foxfire Peptides - Store Configuration (1C) ==="

UNIX_SCRIPT="${ROOT}/scripts/.store-config-inner.unix.sh"
tr -d '\r' < "${ROOT}/scripts/store-config-inner.sh" > "${UNIX_SCRIPT}"

docker compose run --rm --entrypoint sh -e "WP_URL=${WP_URL}" -v "${UNIX_SCRIPT}:/store-config-inner.sh:ro" wpcli /store-config-inner.sh

echo ""
echo "Shop:     ${WP_URL}/shop/"
echo "Cart:     ${WP_URL}/cart/"
echo "Checkout: ${WP_URL}/checkout/"
echo "Account:  ${WP_URL}/my-account/"
echo ""
echo "See docs/STORE_CONFIG.md for settings reference."
echo ""
