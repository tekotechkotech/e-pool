#!/usr/bin/env bash
set -euo pipefail

# Directory locations
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

cd "$PROJECT_ROOT"

echo "=== [$(date '+%Y-%m-%d %H:%M:%S')] Starting e-pool automated backup ==="

# Run artisan backup command with any forwarded arguments (e.g. --dry-run)
php artisan app:backup "$@"

echo "=== [$(date '+%Y-%m-%d %H:%M:%S')] Backup process completed successfully ==="
