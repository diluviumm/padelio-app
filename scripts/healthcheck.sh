#!/usr/bin/env bash
# Padelio uptime check with ntfy alert on state transitions.
# Exit 0 when healthy, 1 when down (systemd will show failed; alert is sent once).
set -uo pipefail

URL="${PADELIO_HEALTH_URL:-https://padelio.naygars.site/up}"
NTFY="${PADELIO_NTFY_URL:-${SVC_NTFY_URL:-}}"
TOPIC="${PADELIO_NTFY_TOPIC:-hermes}"
STATE_DIR="${XDG_STATE_HOME:-$HOME/.local/state}/padelio-health"
STATE_FILE="$STATE_DIR/last_status"
mkdir -p "$STATE_DIR"

CODE="$(curl -s -o /dev/null -w '%{http_code}' --max-time 15 "$URL" 2>/dev/null || echo 000)"
NOW="$(date -Is)"
PREV="$(cat "$STATE_FILE" 2>/dev/null || echo unknown)"

notify() {
  if [ -n "$NTFY" ]; then
    curl -fsS --max-time 15 -H "Title: $1" -d "$2" "$NTFY/$TOPIC" >/dev/null 2>&1 || true
  fi
}

if [ "$CODE" = "200" ]; then
  if [ "$PREV" = "down" ]; then
    notify "Padelio UP" "Padelio kembali normal pada $NOW ($URL)"
  fi
  printf 'up\n' > "$STATE_FILE"
  exit 0
fi

if [ "$PREV" != "down" ]; then
  notify "Padelio DOWN" "HTTP $CODE pada $NOW ($URL)"
fi
printf 'down\n' > "$STATE_FILE"
exit 1
