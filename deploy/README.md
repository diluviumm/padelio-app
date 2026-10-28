# Deploy Padelio di dilu

Konfigurasi ini sudah terpasang di server `dilu` (Ubuntu 24.04). Tidak ada secret di folder ini.

## 1. Paket

```bash
sudo apt-get install -y php8.3-fpm php8.3-pgsql php8.3-mbstring php8.3-xml \
  php8.3-curl php8.3-zip php8.3-gd php8.3-bcmath php8.3-intl composer
```

## 2. Database

```bash
sudo -u postgres psql -c 'CREATE ROLE "www-data" LOGIN'
sudo -u postgres createdb -O "www-data" padelio
```

Aplikasi memakai peer auth melalui socket `/var/run/postgresql` sebagai user OS `www-data`.

## 3. Kode dan dependency

```bash
sudo rsync -a --delete /home/mael/padelio-deploy/ /srv/padelio/
sudo chown -R www-data:www-data /srv/padelio
cd /srv/padelio
sudo composer install --no-dev --prefer-dist --optimize-autoloader
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan db:seed --force
sudo -u www-data php artisan storage:link
```

`.env` produksi (mode 600, owner www-data) memakai:

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://padelio.naygars.site
DB_CONNECTION=pgsql
DB_HOST=/var/run/postgresql
DB_PORT=5432
DB_DATABASE=padelio
DB_USERNAME=www-data
DB_PASSWORD=
SESSION_SECURE_COOKIE=true
```

## 4. PHP-FPM + Caddy

```bash
sudo install -m 644 deploy/dilu/php-fpm-pool.conf /etc/php/8.3/fpm/pool.d/padelio.conf
sudo php-fpm8.3 -t && sudo systemctl restart php8.3-fpm

sudo install -m 644 deploy/dilu/padelio.caddy /etc/caddy/naygars-sites/padelio.caddy
sudo caddy validate --config /etc/caddy/Caddyfile
sudo systemctl reload caddy
```

Caddy meneruskan `padelio.naygars.site` ke `127.0.0.1:9001`.

## 5. Backup, monitor, log

```bash
sudo install -m 755 deploy/dilu/backup.sh /usr/local/bin/padelio-backup-dilu.sh
sudo install -m 644 deploy/dilu/padelio-backup.service /etc/systemd/system/
sudo install -m 644 deploy/dilu/padelio-backup.timer /etc/systemd/system/
sudo install -m 644 deploy/dilu/padelio-promtail.service /etc/systemd/system/
sudo install -m 644 deploy/dilu/promtail.yml /etc/promtail/padelio.yml
sudo install -m 644 deploy/dilu/padelio-health.service /etc/systemd/system/
sudo install -m 644 deploy/dilu/padelio-health.timer /etc/systemd/system/
sudo install -m 755 scripts/healthcheck.sh /usr/local/bin/padelio-health.sh
sudo systemctl daemon-reload
sudo systemctl enable --now padelio-backup-dilu.timer padelio-promtail-dilu.service padelio-health.timer
```

- Backup: `/var/backups/padelio`, retensi 14 hari, salinan ke `mael:padelio/db` (rclone config di `/root/.config/rclone/rclone.conf`, mode 600).
- Promtail: `/srv/padelio/storage/logs/*.log` + `/var/log/php8.3-fpm.log` → Loki vium.
- Health: `https://padelio.naygars.site/up` tiap 5 menit → ntfy `http://127.0.0.1:8090/hermes`.

## 6. Cloudflare

- DNS wildcard `*.naygars.site` → tunnel `hermes` (sudah).
- Tunnel ingress `padelio.naygars.site` → `http://100.108.225.101:80` (Caddy). File: `~/.cloudflared/config.yml` di laptop.
- Access app: domain `padelio.naygars.site/admin`, policy `cloudflare_account_member`.
- Rate limit: `/login` POST 10 per 10s per IP, block 10s (free plan: period & mitigation 10s).

## 7. Setelah deploy

```bash
curl -sI https://padelio.naygars.site/up
curl -sI https://padelio.naygars.site/admin   # 302 ke cloudflareaccess.com
sudo systemctl status php8.3-fpm caddy
systemctl list-timers 'padelio*'
```
