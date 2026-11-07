param(
    [switch]$SeedDemo
)

$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path -Parent $PSScriptRoot
Set-Location $projectRoot

if (-not (Test-Path '.env')) {
    Copy-Item -LiteralPath '.env.example' -Destination '.env'
    Write-Host 'File .env dibuat dari .env.example. Ganti password demo sebelum penggunaan nyata.'
}

docker compose build
if ($LASTEXITCODE -ne 0) { throw 'docker compose build gagal.' }

docker compose up -d mysql
if ($LASTEXITCODE -ne 0) { throw 'MySQL gagal dijalankan.' }

if (-not (Select-String -Path '.env' -Pattern '^APP_KEY=.+$' -Quiet)) {
    docker compose run --rm --user root --volume "${PWD}/.env:/var/www/html/.env" app php artisan key:generate --force
    if ($LASTEXITCODE -ne 0) { throw 'Pembuatan APP_KEY gagal.' }
}

docker compose run --rm app php artisan migrate --force
if ($LASTEXITCODE -ne 0) { throw 'Migrasi gagal.' }

if ($SeedDemo) {
    docker compose run --rm app php artisan db:seed --force
    if ($LASTEXITCODE -ne 0) { throw 'Seeder demo gagal.' }
}

docker compose up -d app queue scheduler nginx
if ($LASTEXITCODE -ne 0) { throw 'Stack aplikasi gagal dijalankan.' }

docker compose ps
Write-Host "Aplikasi siap di $((Get-Content '.env' | Select-String '^APP_URL=').Line.Split('=', 2)[1])"
