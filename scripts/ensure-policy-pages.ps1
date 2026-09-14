# Creates/updates policy pages and assigns them in WordPress & WooCommerce settings.
# Idempotent - safe to run multiple times.
# Usage: .\scripts\ensure-policy-pages.ps1
# (Chunk 1M-alpha)

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
Write-Host "=== Foxfire Peptides - Policy Pages (Chunk 1M-alpha) ===" -ForegroundColor Cyan

$innerScript = Join-Path $Root "scripts\ensure-policy-pages.sh"
$unixScript  = Join-Path $Root "scripts\.ensure-policy-pages.unix.sh"
$scriptContent = [System.IO.File]::ReadAllText($innerScript) -replace "`r`n", "`n"
[System.IO.File]::WriteAllText($unixScript, $scriptContent)

docker compose run -T --rm --entrypoint sh -e "WP_URL=$WP_URL" -v "${unixScript}:/ensure-policy-pages.sh:ro" wpcli /ensure-policy-pages.sh

if ($LASTEXITCODE -ne 0) {
    Write-Host "Policy page setup failed." -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "Privacy Policy:      $WP_URL/privacy-policy/"
Write-Host "Terms and Conditions: $WP_URL/terms-and-conditions/"
Write-Host "Refund and Returns:   $WP_URL/refund-and-returns-policy/"
Write-Host "Shipping Policy:      $WP_URL/shipping-policy/"
Write-Host ""
