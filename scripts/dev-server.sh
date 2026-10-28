#!/usr/bin/env bash
# Local development server (port 8002) against the local PostgreSQL cluster.
# Production runs on dilu via PHP-FPM; this script never touches it.
set -euo pipefail
cd "$(dirname "$0")/.."

export APP_ENV=local
export APP_DEBUG=true
export APP_URL=http://127.0.0.1:8002
export SESSION_SECURE_COOKIE=false
export DB_CONNECTION=pgsql
export DB_HOST=127.0.0.1
export DB_PORT=5544
export DB_DATABASE=padelio
export DB_USERNAME=padelio
export DB_PASSWORD=
export LOG_LEVEL=debug

exec php artisan serve --host=127.0.0.1 --port=8002 "$@"
