# Foxfire Peptides - local WordPress setup (Chunk 1A)
# Usage: .\scripts\setup.ps1

$ErrorActionPreference = "Stop"
$Root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
Set-Location $Root

Write-Host ""
Write-Host "=== Foxfire Peptides - Local Setup ===" -ForegroundColor Cyan

# 1. Ensure .env exists
if (-not (Test-Path ".env")) {
    Copy-Item ".env.example" ".env"
    Write-Host "Created .env from .env.example" -ForegroundColor Yellow
}

# Load env vars for script use
Get-Content ".env" | ForEach-Object {
    if ($_ -match '^\s*([^#][^=]+)=(.*)$') {
        [System.Environment]::SetEnvironmentVariable($matches[1].Trim(), $matches[2].Trim(), "Process")
    }
}

$WP_URL = if ($env:WP_URL) { $env:WP_URL } else { "http://localhost:8080" }

# 2. Ensure wp-content directories exist
$dirs = @("wp-content/themes", "wp-content/plugins", "wp-content/mu-plugins", "wp-content/uploads")
foreach ($dir in $dirs) {
    if (-not (Test-Path $dir)) {
        New-Item -ItemType Directory -Path $dir -Force | Out-Null
    }
}

# 3. Start containers
Write-Host ""
Write-Host "Starting Docker containers..." -ForegroundColor Cyan
docker compose up -d
if ($LASTEXITCODE -ne 0) { throw "docker compose up failed" }

# 4. Wait for WordPress to be reachable
Write-Host "Waiting for WordPress at $WP_URL ..." -ForegroundColor Cyan
$ready = $false
for ($attempt = 0; $attempt -lt 30; $attempt++) {
    try {
        $response = Invoke-WebRequest -Uri $WP_URL -UseBasicParsing -TimeoutSec 5
        if ($response.StatusCode -eq 200) {
            $ready = $true
            break
        }
    } catch {
        # still starting
    }
    Start-Sleep -Seconds 3
}

if (-not $ready) {
    Write-Host "WordPress did not become ready in time. Check: docker compose logs wordpress" -ForegroundColor Red
    exit 1
}
Write-Host "WordPress is up." -ForegroundColor Green

# 5. Check if WordPress is installed via WP-CLI
Write-Host ""
Write-Host "Checking WordPress installation..." -ForegroundColor Cyan
docker compose run --rm wpcli core is-installed --url="$WP_URL" | Out-Null
if ($LASTEXITCODE -ne 0) {
    Write-Host "Installing WordPress..." -ForegroundColor Cyan
    docker compose run --rm wpcli core install `
        --url="$WP_URL" `
        --title="$($env:WP_TITLE)" `
        --admin_user="$($env:WP_ADMIN_USER)" `
        --admin_password="$($env:WP_ADMIN_PASSWORD)" `
        --admin_email="$($env:WP_ADMIN_EMAIL)" `
        --skip-email
    if ($LASTEXITCODE -ne 0) {
        Write-Host "WordPress install failed." -ForegroundColor Red
        exit 1
    }
    Write-Host "WordPress installed." -ForegroundColor Green
} else {
    Write-Host "WordPress already installed - skipping." -ForegroundColor Yellow
}

# 6. Install WooCommerce if not present
Write-Host ""
Write-Host "Checking WooCommerce..." -ForegroundColor Cyan
docker compose run --rm wpcli plugin is-active woocommerce --url="$WP_URL" | Out-Null
if ($LASTEXITCODE -ne 0) {
    Write-Host "Installing WooCommerce..." -ForegroundColor Cyan
    docker compose run --rm wpcli plugin install woocommerce --activate --url="$WP_URL"
    if ($LASTEXITCODE -ne 0) {
        Write-Host "WooCommerce install failed - retrying activation..." -ForegroundColor Yellow
        docker compose run --rm wpcli plugin activate woocommerce --url="$WP_URL"
    }
    Write-Host "WooCommerce installed and activated." -ForegroundColor Green
} else {
    Write-Host "WooCommerce already active - skipping." -ForegroundColor Yellow
}

# 7. Install Advanced Custom Fields if not present
Write-Host ""
Write-Host "Checking Advanced Custom Fields..." -ForegroundColor Cyan
docker compose run --rm wpcli plugin is-active advanced-custom-fields --url="$WP_URL" | Out-Null
if ($LASTEXITCODE -ne 0) {
    Write-Host "Installing Advanced Custom Fields..." -ForegroundColor Cyan
    docker compose run --rm wpcli plugin install advanced-custom-fields --activate --url="$WP_URL"
    if ($LASTEXITCODE -ne 0) {
        Write-Host "Advanced Custom Fields install failed." -ForegroundColor Red
        exit 1
    }
} else {
    Write-Host "Advanced Custom Fields already active - skipping." -ForegroundColor Yellow
}

# 8. Install Storefront parent theme + activate Foxfire child theme
Write-Host ""
Write-Host "Checking themes..." -ForegroundColor Cyan
docker compose run --rm wpcli theme is-installed storefront --url="$WP_URL" | Out-Null
if ($LASTEXITCODE -ne 0) {
    Write-Host "Installing Storefront parent theme..." -ForegroundColor Cyan
    docker compose run --rm wpcli theme install storefront --url="$WP_URL"
}
docker compose run --rm wpcli theme activate foxfire-child --url="$WP_URL"
if ($LASTEXITCODE -ne 0) {
    Write-Host "Failed to activate foxfire-child theme." -ForegroundColor Red
    exit 1
}
Write-Host "Foxfire child theme active." -ForegroundColor Green

# 9. Activate the theme-independent Foxfire Operations plugin (Chunk 1P)
Write-Host ""
Write-Host "Activating Foxfire Operations..." -ForegroundColor Cyan
docker compose run --rm wpcli plugin activate foxfire-operations --url="$WP_URL"
if ($LASTEXITCODE -ne 0) {
    Write-Host "Failed to activate Foxfire Operations." -ForegroundColor Red
    exit 1
}
Write-Host "Foxfire Operations active." -ForegroundColor Green

# 10. Configure WooCommerce store (Chunk 1C)
Write-Host ""
Write-Host "Configuring WooCommerce store..." -ForegroundColor Cyan
& "$Root\scripts\configure-store.ps1"

# 11. Summary
Write-Host ""
Write-Host "=== Setup Complete ===" -ForegroundColor Green
Write-Host "Site:     $WP_URL"
Write-Host "Admin:    $WP_URL/wp-admin"
Write-Host "User:     $($env:WP_ADMIN_USER)"
Write-Host "Password: $($env:WP_ADMIN_PASSWORD)  (change in .env for next fresh install)"
Write-Host ""
Write-Host "Useful commands:"
Write-Host "  docker compose up -d          # start"
Write-Host "  docker compose down           # stop"
Write-Host "  docker compose down -v        # stop + wipe all data"
Write-Host "  docker compose logs -f wordpress"
Write-Host ""
