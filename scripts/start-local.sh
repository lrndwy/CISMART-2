#!/usr/bin/env bash
# Jalankan CISMART lokal (tanpa Docker)
set -e
cd "$(dirname "$0")/laravel_app"

export PATH="/opt/homebrew/bin:$PATH"

if [ ! -f .env ]; then
  cp .env.example .env
  php artisan key:generate
fi

if [ ! -f database/database.sqlite ]; then
  touch database/database.sqlite
  php artisan migrate --force
  php artisan db:seed --force
fi

[ -L public/storage ] || php artisan storage:link

echo "Beranda : http://127.0.0.1:8000"
echo "Admin   : http://127.0.0.1:8000/admin/login"
echo ""

# Vite di background, Laravel di foreground
npm run dev >/tmp/cismart-vite.log 2>&1 &
VITE_PID=$!
trap 'kill $VITE_PID 2>/dev/null || true' EXIT

php artisan serve --host=127.0.0.1 --port=8000
