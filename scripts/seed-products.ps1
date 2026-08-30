# Seeds product categories, ACF fields, and development products (Chunk 1E)
# Usage: .\scripts\seed-products.ps1

$ErrorActionPreference = "Stop"
$Root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
Set-Location $Root

if (Test-Path ".env") {
    Get-Content ".env" | ForEach-Object {
        if ($_ -match '^\s*([^#][^=]+)=(.*)$') {
            [System.Environment]::SetEnvironmentVariable($matches[1].Trim(), $matches[2].Trim(), "Process")
        }
    }
}

$WP_URL = if ($env:WP_URL) { $env:WP_URL } else { "http://localhost:8080" }

Write-Host ""
Write-Host "=== Foxfire Peptides - Product Seed (1E) ===" -ForegroundColor Cyan

Write-Host "Installing Advanced Custom Fields (if needed)..." -ForegroundColor Cyan
$acfCheck = docker compose run --rm wpcli plugin is-active advanced-custom-fields --url="$WP_URL" 2>&1
if ($LASTEXITCODE -ne 0) {
    docker compose run --rm wpcli plugin install advanced-custom-fields --activate --url="$WP_URL"
    if ($LASTEXITCODE -ne 0) {
        Write-Host "ACF install failed." -ForegroundColor Red
        exit 1
    }
}

$seedScript = Join-Path $Root "scripts\seed-products.php"
docker compose run --rm -v "${seedScript}:/seed-products.php:ro" wpcli eval-file /seed-products.php --url="$WP_URL"

if ($LASTEXITCODE -ne 0) {
    Write-Host "Product seed failed." -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "Shop: $WP_URL/shop/"
Write-Host "Admin products: $WP_URL/wp-admin/edit.php?post_type=product"
Write-Host "See docs/PRODUCT_DATA.md for field reference."
Write-Host ""
