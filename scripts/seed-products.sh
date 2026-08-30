# Product data model & seed script (Chunk 1E)
# Usage: ./scripts/seed-products.sh

set -e

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

if [ -f .env ]; then
  set -a
  # shellcheck disable=SC1091
  . ./.env
  set +a
fi

WP_URL="${WP_URL:-http://localhost:8080}"

echo ""
echo "=== Foxfire Peptides - Product Seed (1E) ==="

docker compose run --rm wpcli plugin is-active advanced-custom-fields --url="${WP_URL}" 2>/dev/null || \
  docker compose run --rm wpcli plugin install advanced-custom-fields --activate --url="${WP_URL}"

docker compose run --rm \
  -v "${ROOT}/scripts/seed-products.php:/seed-products.php:ro" \
  wpcli eval-file /seed-products.php --url="${WP_URL}"

echo ""
echo "Shop: ${WP_URL}/shop/"
echo "See docs/PRODUCT_DATA.md"
echo ""
