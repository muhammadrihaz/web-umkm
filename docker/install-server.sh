#!/usr/bin/env bash

set -Eeuo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ENV_FILE="${PROJECT_DIR}/.env"
DEFAULT_PORT="8085"

info() {
    printf '\n\033[1;34m%s\033[0m\n' "$1"
}

fail() {
    printf '\n\033[1;31mERROR: %s\033[0m\n' "$1" >&2
    exit 1
}

set_env() {
    local key="$1"
    local value="$2"
    local escaped="$value"

    escaped="${escaped//\\/\\\\}"
    escaped="${escaped//&/\\&}"
    escaped="${escaped//|/\\|}"

    if grep -q "^${key}=" "$ENV_FILE"; then
        sed -i "s|^${key}=.*|${key}=${escaped}|" "$ENV_FILE"
    else
        printf '%s=%s\n' "$key" "$value" >> "$ENV_FILE"
    fi
}

command -v docker >/dev/null 2>&1 || fail 'Docker belum terpasang.'
docker compose version >/dev/null 2>&1 || fail 'Docker Compose v2 belum tersedia.'
command -v openssl >/dev/null 2>&1 || fail 'OpenSSL belum tersedia.'

cd "$PROJECT_DIR"

if [[ ! -f "$ENV_FILE" ]]; then
    cp .env.example "$ENV_FILE"
    chmod 600 "$ENV_FILE"

    read -rp "Port aplikasi [${DEFAULT_PORT}]: " APP_PORT
    APP_PORT="${APP_PORT:-$DEFAULT_PORT}"

    if command -v ss >/dev/null 2>&1 && ss -ltn | awk '{print $4}' | grep -Eq "(^|:)${APP_PORT}$"; then
        fail "Port ${APP_PORT} sedang digunakan. Jalankan ulang dan pilih port lain."
    fi

    read -rp "URL aplikasi [http://localhost:${APP_PORT}]: " APP_URL
    APP_URL="${APP_URL:-http://localhost:${APP_PORT}}"

    set_env APP_ENV production
    set_env APP_DEBUG false
    set_env APP_URL "$APP_URL"
    set_env APP_PORT "$APP_PORT"
    set_env LOG_LEVEL warning
    set_env DB_PASSWORD "$(openssl rand -hex 32)"
    set_env DB_ROOT_PASSWORD "$(openssl rand -hex 32)"
    set_env MAIL_MAILER log

    if [[ "$APP_URL" == https://* ]]; then
        set_env SESSION_SECURE_COOKIE true
    else
        set_env SESSION_SECURE_COOKIE false
    fi
else
    info '.env sudah ada; konfigurasi yang ada dipertahankan.'
    APP_PORT="$(sed -n 's/^APP_PORT=//p' "$ENV_FILE" | tail -n 1)"
    APP_PORT="${APP_PORT:-$DEFAULT_PORT}"
    APP_URL="$(sed -n 's/^APP_URL=//p' "$ENV_FILE" | tail -n 1)"
    APP_URL="${APP_URL:-http://localhost:${APP_PORT}}"
fi

read -rp 'Nama admin [Administrator Pangkonan]: ' ADMIN_NAME
ADMIN_NAME="${ADMIN_NAME:-Administrator Pangkonan}"

read -rp 'Email admin: ' ADMIN_EMAIL
[[ "$ADMIN_EMAIL" == *@*.* ]] || fail 'Format email admin tidak valid.'

while true; do
    read -rsp 'Password admin (minimal 12 karakter): ' ADMIN_PASSWORD
    printf '\n'
    [[ ${#ADMIN_PASSWORD} -ge 12 ]] || {
        printf 'Password terlalu pendek.\n' >&2
        continue
    }

    read -rsp 'Ulangi password admin: ' ADMIN_PASSWORD_CONFIRMATION
    printf '\n'
    [[ "$ADMIN_PASSWORD" == "$ADMIN_PASSWORD_CONFIRMATION" ]] && break
    printf 'Konfirmasi password tidak sama.\n' >&2
done

trap 'unset ADMIN_PASSWORD ADMIN_PASSWORD_CONFIRMATION' EXIT

info 'Memvalidasi konfigurasi Docker Compose...'
docker compose config --quiet

info 'Membangun image aplikasi...'
docker compose build

info 'Menjalankan MySQL...'
docker compose up -d mysql

MYSQL_CONTAINER_ID="$(docker compose ps -q mysql)"
[[ -n "$MYSQL_CONTAINER_ID" ]] || fail 'Container MySQL gagal dibuat.'

for attempt in $(seq 1 60); do
    MYSQL_HEALTH="$(docker inspect --format '{{if .State.Health}}{{.State.Health.Status}}{{else}}{{.State.Status}}{{end}}' "$MYSQL_CONTAINER_ID")"
    [[ "$MYSQL_HEALTH" == 'healthy' ]] && break
    [[ "$attempt" -eq 60 ]] && fail 'MySQL tidak healthy setelah 120 detik.'
    sleep 2
done

if ! grep -Eq '^APP_KEY=base64:.+' "$ENV_FILE"; then
    info 'Membuat APP_KEY...'
    docker compose run --rm --no-deps \
        --user root \
        --volume "${ENV_FILE}:/var/www/html/.env" \
        app php artisan key:generate --force --no-interaction
fi

info 'Menjalankan migrasi database...'
docker compose run --rm app php artisan migrate --force --no-interaction

info 'Menjalankan service aplikasi...'
docker compose up -d app nginx queue scheduler

info 'Membuat atau memperbarui akun admin...'
docker compose exec -T \
    -e ADMIN_NAME="$ADMIN_NAME" \
    -e ADMIN_EMAIL="$ADMIN_EMAIL" \
    -e ADMIN_PASSWORD="$ADMIN_PASSWORD" \
    app php artisan app:ensure-admin --no-interaction

unset ADMIN_PASSWORD ADMIN_PASSWORD_CONFIRMATION

info 'Memeriksa kesehatan aplikasi...'
for attempt in $(seq 1 30); do
    if curl --fail --silent --show-error "http://127.0.0.1:${APP_PORT}/up" >/dev/null; then
        printf '\n\033[1;32mInstalasi berhasil.\033[0m\n'
        printf 'Aplikasi : %s\n' "$APP_URL"
        printf 'Login    : %s/masuk\n' "${APP_URL%/}"
        printf 'Admin    : %s\n\n' "$ADMIN_EMAIL"
        docker compose ps
        exit 0
    fi

    [[ "$attempt" -eq 30 ]] && break
    sleep 2
done

docker compose logs --tail=50 app nginx
fail "Health check gagal. Periksa log di atas."
