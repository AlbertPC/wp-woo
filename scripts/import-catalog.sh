#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "${BASH_SOURCE[0]}")/.."

archive=${1:-@inbox/oscar-evaluacion.zip}
[[ -f "$archive" ]] || { printf 'No se encuentra el ZIP: %s\n' "$archive" >&2; exit 1; }
container_archive=$(docker compose exec -T wordpress mktemp /tmp/bitforma-catalog.XXXXXX)
trap 'docker compose exec -T wordpress rm -f -- "$container_archive"' EXIT
docker compose cp "$archive" "wordpress:$container_archive"
docker compose exec -T wordpress chown www-data:www-data "$container_archive"
docker compose exec -T -u www-data wordpress wp eval-file /opt/bitforma/scripts/import-catalog.php "$container_archive"
