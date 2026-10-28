#!/usr/bin/env bash
# Push to both remotes: public origin + private backup.
set -euo pipefail
cd "$(dirname "$0")/.."
git push origin main
