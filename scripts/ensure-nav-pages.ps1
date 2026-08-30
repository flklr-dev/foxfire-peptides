# Creates stub pages required by Chunk 1D navigation.
# Usage: .\scripts\ensure-nav-pages.ps1

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
Write-Host "=== Foxfire Peptides - Navigation stub pages (1D) ===" -ForegroundColor Cyan

$innerScript = Join-Path $Root "scripts\ensure-nav-pages.sh"
$unixScript = Join-Path $Root "scripts\.ensure-nav-pages.unix.sh"
$scriptContent = [System.IO.File]::ReadAllText($innerScript) -replace "`r`n", "`n"
[System.IO.File]::WriteAllText($unixScript, $scriptContent)

docker compose run --rm --entrypoint sh -e "WP_URL=$WP_URL" -v "${unixScript}:/ensure-nav-pages.sh:ro" wpcli /ensure-nav-pages.sh

if ($LASTEXITCODE -ne 0) {
    Write-Host "Navigation page setup failed." -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "Testing/COA: $WP_URL/testing-coa/"
Write-Host "About:       $WP_URL/about/"
Write-Host "Contact:     $WP_URL/contact/"
Write-Host ""
