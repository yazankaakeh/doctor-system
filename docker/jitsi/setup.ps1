# Automated local Jitsi Meet setup for doctor-system (Windows + Docker Desktop)
# Usage: .\setup.ps1

$ErrorActionPreference = "Stop"

$ScriptDir = $PSScriptRoot
$RepoDir   = Join-Path $ScriptDir "docker-jitsi-meet"
$CfgDir    = Join-Path $env:USERPROFILE ".jitsi-meet-cfg"

function Write-Step([string]$Msg) {
    Write-Host ""
    Write-Host "==> $Msg" -ForegroundColor Cyan
}

function Assert-Command([string]$Name, [string]$InstallHint) {
    if (-not (Get-Command $Name -ErrorAction SilentlyContinue)) {
        Write-Error "'$Name' not found. $InstallHint"
        exit 1
    }
}

# ---- Prereqs ----
Write-Step "Checking prerequisites"
Assert-Command "docker" "Install Docker Desktop: https://www.docker.com/products/docker-desktop/"
Assert-Command "git"    "Install Git for Windows: https://git-scm.com/download/win"

try {
    docker info 2>&1 | Out-Null
} catch {
    Write-Error "Docker daemon is not running. Start Docker Desktop first."
    exit 1
}

# ---- Clone / update docker-jitsi-meet ----
if (Test-Path $RepoDir) {
    Write-Step "Updating existing docker-jitsi-meet checkout"
    Push-Location $RepoDir
    git pull --ff-only
    Pop-Location
} else {
    Write-Step "Cloning docker-jitsi-meet into $RepoDir"
    git clone --depth 1 https://github.com/jitsi/docker-jitsi-meet.git $RepoDir
}

Push-Location $RepoDir

# ---- Build .env from template ----
if (-not (Test-Path ".env")) {
    Write-Step "Creating .env from env.example"
    Copy-Item "env.example" ".env"
}

function New-JitsiPassword {
    -join ((48..57) + (65..90) + (97..122) | Get-Random -Count 64 | ForEach-Object { [char]$_ })
}

function Set-EnvVar([ref]$Text, [string]$Key, [string]$Value) {
    $escaped = [regex]::Escape($Key)
    if ($Text.Value -match "(?m)^#?$escaped=") {
        $Text.Value = [regex]::Replace(
            $Text.Value,
            "(?m)^#?$escaped=.*$",
            "$Key=$Value"
        )
    } else {
        $Text.Value += "`n$Key=$Value"
    }
}

Write-Step "Generating passwords and configuring .env"
$envText = Get-Content ".env" -Raw

# Random secrets
Set-EnvVar ([ref]$envText) "JICOFO_COMPONENT_SECRET" (New-JitsiPassword)
Set-EnvVar ([ref]$envText) "JICOFO_AUTH_PASSWORD"    (New-JitsiPassword)
Set-EnvVar ([ref]$envText) "JVB_AUTH_PASSWORD"       (New-JitsiPassword)
Set-EnvVar ([ref]$envText) "JIGASI_XMPP_PASSWORD"    (New-JitsiPassword)
Set-EnvVar ([ref]$envText) "JIBRI_RECORDER_PASSWORD" (New-JitsiPassword)
Set-EnvVar ([ref]$envText) "JIBRI_XMPP_PASSWORD"     (New-JitsiPassword)

# Local-dev specific
Set-EnvVar ([ref]$envText) "HTTP_PORT"           "8000"
Set-EnvVar ([ref]$envText) "HTTPS_PORT"          "8443"
Set-EnvVar ([ref]$envText) "PUBLIC_URL"          "https://localhost:8443"
Set-EnvVar ([ref]$envText) "DISABLE_HTTPS"       "0"
Set-EnvVar ([ref]$envText) "ENABLE_LETSENCRYPT"  "0"
Set-EnvVar ([ref]$envText) "DOCKER_HOST_ADDRESS" "127.0.0.1"
Set-EnvVar ([ref]$envText) "TZ"                  "Asia/Riyadh"
Set-EnvVar ([ref]$envText) "CONFIG"              $CfgDir.Replace('\', '/')

Set-Content -Path ".env" -Value $envText -NoNewline

# ---- Config volumes ----
Write-Step "Creating config directories at $CfgDir"
$subdirs = @("web", "transcripts", "prosody\config", "prosody\prosody-plugins-custom", "jicofo", "jvb", "jigasi", "jibri")
foreach ($d in $subdirs) {
    $full = Join-Path $CfgDir $d
    if (-not (Test-Path $full)) {
        New-Item -ItemType Directory -Path $full -Force | Out-Null
    }
}

# Tell docker compose where CONFIG lives
$env:CONFIG = $CfgDir

# ---- Start ----
Write-Step "Pulling images and starting Jitsi (this can take a few minutes the first time)"
docker compose pull
docker compose up -d

Pop-Location

# ---- Optional firewall rule for UDP 10000 (JVB media) ----
Write-Step "Ensuring Windows Firewall allows UDP 10000 for JVB"
try {
    $existing = Get-NetFirewallRule -DisplayName "Jitsi JVB UDP 10000" -ErrorAction SilentlyContinue
    if (-not $existing) {
        New-NetFirewallRule -DisplayName "Jitsi JVB UDP 10000" `
            -Direction Inbound -Protocol UDP -LocalPort 10000 -Action Allow | Out-Null
        Write-Host "   Firewall rule added."
    } else {
        Write-Host "   Firewall rule already exists."
    }
} catch {
    Write-Warning "Could not add firewall rule automatically (need admin PowerShell). If video fails to connect, run this manually in an elevated shell:"
    Write-Host '   New-NetFirewallRule -DisplayName "Jitsi JVB UDP 10000" -Direction Inbound -Protocol UDP -LocalPort 10000 -Action Allow'
}

Write-Host ""
Write-Host "========================================================" -ForegroundColor Green
Write-Host "  Jitsi Meet is starting at:  https://localhost:8443" -ForegroundColor Green
Write-Host "========================================================" -ForegroundColor Green
Write-Host ""
Write-Host "Next steps:"
Write-Host "  1. Open https://localhost:8443 and accept the self-signed cert."
Write-Host "  2. Add these lines to C:\laragon\www\laravel\doctor-system\.env:"
Write-Host ""
Write-Host "     VIDEO_PROVIDER=jitsi" -ForegroundColor Yellow
Write-Host "     JITSI_DOMAIN=localhost:8443" -ForegroundColor Yellow
Write-Host "     JITSI_SELF_HOSTED=true" -ForegroundColor Yellow
Write-Host "     JITSI_APP_ID=" -ForegroundColor Yellow
Write-Host "     JITSI_SECRET=" -ForegroundColor Yellow
Write-Host "     VIDEO_LOBBY_ENABLED=false" -ForegroundColor Yellow
Write-Host ""
Write-Host "  3. Run: php artisan config:clear"
Write-Host "  4. Reload the page and click Join Consultation."
Write-Host ""
Write-Host "To stop Jitsi later:  cd $RepoDir; docker compose down"
