# Imports generated product images and attaches them as featured images.
# Usage: .\scripts\import-product-images.ps1

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
Write-Host "=== Foxfire Peptides - Product Image Import ===" -ForegroundColor Cyan

$script = Join-Path $Root "scripts\import-product-images.php"
docker compose run --rm -v "${script}:/import-product-images.php:ro" wpcli eval-file /import-product-images.php --url="$WP_URL"

if ($LASTEXITCODE -ne 0) {
    Write-Host "Image import failed." -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "Shop: $WP_URL/shop/"
Write-Host ""
