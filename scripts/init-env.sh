#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "${BASH_SOURCE[0]}")/.."

if [[ -e .env || -L .env ]]; then
    chmod 600 .env
    printf '%s\n' '.env ya existe. Se conservarán sus credenciales y ajustes.'
    exit 0
fi

temp_env=$(mktemp .env.new.XXXXXX)
trap 'rm -f -- "$temp_env"' EXIT
chmod 600 "$temp_env"
db_password=$(openssl rand -hex 32)
root_password=$(openssl rand -hex 32)
printf 'WP_DB_PASSWORD=%s\nDB_ROOT_PASSWORD=%s\nDEV_UID=%s\nDEV_GID=%s\n' \
    "$db_password" "$root_password" "$(id -u)" "$(id -g)" > "$temp_env"
ln -- "$temp_env" .env
printf '%s\n' 'Credenciales locales creadas en .env.'
