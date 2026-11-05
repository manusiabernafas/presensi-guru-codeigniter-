#!/usr/bin/env bash
# Setup Presensi Guru & Ekskul (Linux / macOS). Pastikan MySQL menyala.
set -e
cd "$(dirname "$0")"
php -v >/dev/null 2>&1 || { echo "PHP belum terpasang (butuh PHP 8.2+)."; exit 1; }
php -m | grep -qi '^intl$' || { echo "Ekstensi PHP intl belum aktif. Ubuntu: sudo apt install php-intl  | macOS (brew): sudah termasuk."; exit 1; }
if [ ! -f vendor/autoload.php ]; then
  command -v composer >/dev/null || { echo "Composer belum terpasang: https://getcomposer.org"; exit 1; }
  composer install --no-dev --optimize-autoloader --no-interaction
fi
[ -f .env ] || cp .env.example .env
mkdir -p writable/cache writable/logs writable/session writable/uploads/izin writable/debugbar public/uploads/foto
php spark db:create presensi_guru >/dev/null 2>&1 || true
php spark migrate --all
php spark db:seed DatabaseSeeder
chmod -R ug+rw writable public/uploads
echo
echo "Selesai. Jalankan: php spark serve   lalu buka http://localhost:8080"
echo "Login admin: admin / admin123   guru (pembina Pramuka & Paskibra): guru / guru123"
