# Foxfire Peptides - WooCommerce store configuration (Chunk 1C)
# Usage: .\scripts\configure-store.ps1

$ErrorActionPreference = "Continue"
$Root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
Set-Location $Root

if (-not (Test-Path ".env")) {
    Write-Host ".env not found. Run .\scripts\setup.ps1 first." -ForegroundColor Red
    exit 1
}

Get-Content ".env" | ForEach-Object {
    if ($_ -match '^\s*([^#][^=]+)=(.*)$') {
        [System.Environment]::SetEnvironmentVariable($matches[1].Trim(), $matches[2].Trim(), "Process")
    }
}

$WP_URL = if ($env:WP_URL) { $env:WP_URL } else { "http://localhost:8080" }

Write-Host ""
Write-Host "=== Foxfire Peptides - Store Configuration (1C) ===" -ForegroundColor Cyan

$innerScript = Join-Path $Root "scripts\store-config-inner.sh"
$unixScript = Join-Path $Root "scripts\.store-config-inner.unix.sh"
$scriptContent = [System.IO.File]::ReadAllText($innerScript) -replace "`r`n", "`n"
[System.IO.File]::WriteAllText($unixScript, $scriptContent)

docker compose run --rm --entrypoint sh -e "WP_URL=$WP_URL" -v "${unixScript}:/store-config-inner.sh:ro" wpcli /store-config-inner.sh

if ($LASTEXITCODE -ne 0) {
    Write-Host "Store configuration failed." -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "Shop:     $WP_URL/shop/"
Write-Host "Cart:     $WP_URL/cart/"
Write-Host "Checkout: $WP_URL/checkout/"
Write-Host "Account:  $WP_URL/my-account/"
Write-Host ""
Write-Host "See docs/STORE_CONFIG.md for settings reference."
Write-Host ""
