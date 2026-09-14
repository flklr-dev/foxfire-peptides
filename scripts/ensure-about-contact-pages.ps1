# Creates/updates About Us and Contact Us pages and assigns their custom templates.
# Idempotent - safe to run multiple times.
# Usage: .\scripts\ensure-about-contact-pages.ps1
# (Chunk 1M-beta)

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
Write-Host "=== Foxfire Peptides - About & Contact Pages (Chunk 1M-beta) ===" -ForegroundColor Cyan

$innerScript = Join-Path $Root "scripts\ensure-about-contact-pages.sh"
$unixScript  = Join-Path $Root "scripts\.ensure-about-contact-pages.unix.sh"
$scriptContent = [System.IO.File]::ReadAllText($innerScript) -replace "`r`n", "`n"
[System.IO.File]::WriteAllText($unixScript, $scriptContent)

docker compose run -T --rm --entrypoint sh -e "WP_URL=$WP_URL" -v "${unixScript}:/ensure-about-contact-pages.sh:ro" wpcli /ensure-about-contact-pages.sh

if ($LASTEXITCODE -ne 0) {
    Write-Host "About & Contact page setup failed." -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "About Us:    $WP_URL/about-us/" -ForegroundColor Green
Write-Host "Contact Us:  $WP_URL/contact-us/" -ForegroundColor Green
Write-Host ""
