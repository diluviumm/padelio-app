#!/usr/bin/env bash
# Padelio daily PostgreSQL backup.
# Usage: scripts/backup-db.sh
set -euo pipefail

OUT_DIR="${PADELIO_BACKUP_DIR:-$HOME/notes/backups}"
PGHOST="${PGHOST:-127.0.0.1}"
PGPORT="${PGPORT:-5544}"
PGUSER="${PGUSER:-padelio}"
PGDATABASE="${PGDATABASE:-padelio}"

mkdir -p "$OUT_DIR"
STAMP="$(date +%Y%m%d-%H%M)"
FILE="$OUT_DIR/padelio-$STAMP.dump"

pg_dump -h "$PGHOST" -p "$PGPORT" -U "$PGUSER" -Fc "$PGDATABASE" > "$FILE"
pg_restore -l "$FILE" > /dev/null

find "$OUT_DIR" -maxdepth 1 -name 'padelio-*.dump' -type f -mtime +14 -delete

echo "backup ok: $FILE ($(du -h "$FILE" | cut -f1))"
