#!/usr/bin/env bash
# Padelio production backup on dilu (PostgreSQL 16, peer auth via postgres).
set -euo pipefail

OUT_DIR="${PADELIO_BACKUP_DIR:-/var/backups/padelio}"
OFFSITE="${PADELIO_OFFSITE_REMOTE:-}"

mkdir -p "$OUT_DIR"
STAMP="$(date +%Y%m%d-%H%M)"
FILE="$OUT_DIR/padelio-$STAMP.dump"

sudo -u postgres pg_dump -Fc padelio > "$FILE"
pg_restore -l "$FILE" > /dev/null
chown root:postgres "$FILE"
chmod 640 "$FILE"

find "$OUT_DIR" -maxdepth 1 -name 'padelio-*.dump' -type f -mtime +14 -delete

if [ -n "$OFFSITE" ] && command -v rclone >/dev/null 2>&1; then
  rclone copyto "$FILE" "${OFFSITE%/}/$(basename "$FILE")" --no-traverse
  rclone delete "$OFFSITE" --min-age 14d --include 'padelio-*.dump' 2>/dev/null || true
  echo "offsite ok: ${OFFSITE%/}/$(basename "$FILE")"
fi

echo "backup ok: $FILE ($(du -h "$FILE" | cut -f1))"
