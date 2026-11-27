#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "${BASH_SOURCE[0]}")/.."

bash scripts/init-env.sh
docker compose up -d --build --wait
wp=(docker compose exec -T -u www-data wordpress wp)
demo_url=${WP_LOCAL_URL:-http://localhost:8080}
if [[ -n ${CODESPACE_NAME:-} && -n ${GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN:-} ]]; then
    demo_url="https://${CODESPACE_NAME}-8080.${GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN}"
fi

if ! "${wp[@]}" core is-installed; then
    admin_login=${BITFORMA_ADMIN_LOGIN:-bitforma-admin}
    admin_email=${BITFORMA_ADMIN_EMAIL:-admin@example.invalid}
    if [[ ! -f .env.admin ]]; then
        temp_admin=$(mktemp .env.admin.XXXXXX)
        trap 'rm -f -- "$temp_admin"' EXIT
        chmod 600 "$temp_admin"
        openssl rand -hex 24 > "$temp_admin"
        ln -- "$temp_admin" .env.admin
    fi
    chmod 600 .env.admin
    "${wp[@]}" core install --url="$demo_url" --title=Bitforma \
        --admin_user="$admin_login" --admin_email="$admin_email" --skip-email \
        --prompt=admin_password < .env.admin >/dev/null 2>&1 || {
        printf '%s\n' "Falló la instalación del núcleo de WordPress." >&2
        exit 1
    }
fi

"${wp[@]}" language core install es_ES --activate
if ! "${wp[@]}" plugin is-installed woocommerce; then
    "${wp[@]}" plugin install woocommerce --version=11.3.0
fi
"${wp[@]}" plugin activate woocommerce
"${wp[@]}" language plugin install woocommerce es_ES
if ! "${wp[@]}" theme is-installed twentytwentyfive; then
    "${wp[@]}" theme install twentytwentyfive
fi
"${wp[@]}" theme activate wp-woo
"${wp[@]}" rewrite structure '/%postname%/' --hard
"${wp[@]}" eval-file /opt/bitforma/scripts/configure-store.php
"${wp[@]}" rewrite flush --hard
printf '\n%s\n' "$demo_url" "$demo_url/wp-admin/"
