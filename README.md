# Padelio — Laravel 13

Aplikasi komunitas padel: ladder ELO, challenge, booking lapangan, event + bracket, dan panel admin.

> **Live:** https://padelio.naygars.site
> **Repo publik:** `github.com/diluviumm/padelio-app` · **Backup privat:** `github.com/diluviumm/padelio-app-backup`
> Setiap `git push origin main` dikirim ke **dua** repo (push URL ganda). Tidak memakai `padel.ishmly.space`.

---

## Arsitektur produksi

```
Cloudflare (TLS, WAF, Access /admin)
        │  tunnel "hermes"
        ▼
Caddy 2.6 di dilu :80  ──►  PHP-FPM 8.3 (127.0.0.1:9001)
        │                         │
        ▼                         ▼
/srv/padelio/public          Laravel 13 (/srv/padelio)
                                  │
                                  ▼
                     PostgreSQL 16 (peer auth, role www-data)
```

| Komponen | Produksi | Lokal |
|---|---|---|
| PHP | 8.3.6 FPM | 8.5 CLI |
| Laravel | 13.39 (platform composer 8.3.6) | sama |
| Database | PostgreSQL 16 di dilu | PostgreSQL 18 cluster user :5544 |
| Web server | Caddy → PHP-FPM | `php artisan serve` :8002 |
| Tunnel | `hermes` → Caddy dilu | — |
| Aplikasi | `/srv/padelio` (www-data) | `~/me/pkl/padelio-php` |
| Repo | publik + backup privat | lokal |

Laptop **tidak lagi** menjalankan aplikasi produksi; `padelio-app.service` lama sudah dihentikan. Database lama tetap ada untuk pengembangan dan test lokal.

---

## Fitur

- **Publik:** landing, ranking per level/periode, profil pemain, hasil, direktori lapangan, event, bracket, statistik, tentang, privasi, ketentuan.
- **Pemain:** dashboard, tantangan, jadwal, input/konfirmasi skor, sewa + checkout, review, event, notifikasi, profil + foto.
- **Admin:** KPI, approve/tolak hasil, pemain/rating/suspend, booking, lapangan + maintenance, peralatan + stok, review, event + pendaftar + bracket 8/16.

Data demo (24 pemain, 6 lapangan, 8 peralatan, 3 event, 4 match, 2 booking) **dipertahankan**.

---

## Akun Demo

| Peran | Email | Password | Pemain |
|---|---|---|---|
| Admin | `admin@padelio.id` | *dirotasi* | — |
| User | `user@padelio.id` | *dirotasi* | Hadi |
| User | `andi@padelio.id` | *dirotasi* | Andi |
| User | `rio@padelio.id` | *dirotasi* | Rio |

Password dirotasi 29 Okt 2026; nilai disimpan di luar repo (file mode 600) dan tidak pernah dicetak.

---

## Development lokal

```bash
# PostgreSQL lokal (sekali saja)
initdb -D ~/.local/share/padelio/pgdata -U padelio --auth=trust --encoding=UTF8 --locale=C.UTF-8
pg_ctl -D ~/.local/share/padelio/pgdata -l ~/.local/share/padelio/pg.log \
  -o "-p 5544 -k /tmp -c listen_addresses=127.0.0.1" start
createdb -h 127.0.0.1 -p 5544 -U padelio padelio
createdb -h 127.0.0.1 -p 5544 -U padelio padelio_test

composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
./scripts/dev-server.sh          # http://127.0.0.1:8002
```

`scripts/dev-server.sh` mengekspor `APP_ENV=local`, `APP_URL=http://127.0.0.1:8002`, dan DB lokal; tidak menyentuh produksi.

### Test

```bash
php artisan test
```

44 test / 119 assertion, database `padelio_test`, `RefreshDatabase`. CI berjalan di GitHub Actions (PHP 8.4 + PostgreSQL 16 service).

---

## Deployment (dilu, sudah terpasang)

```bash
# kode
rsync -a --exclude .git --exclude .env --exclude vendor \
  --exclude 'storage/logs/*' --exclude public/storage \
  ~/me/pkl/padelio-php/ dilu:/home/mael/padelio-deploy/
ssh dilu 'sudo rsync -a --delete /home/mael/padelio-deploy/ /srv/padelio/ && \
  sudo chown -R www-data:www-data /srv/padelio && \
  cd /srv/padelio && sudo composer install --no-dev --optimize-autoloader'

# DB (sekali)
ssh dilu 'sudo -u postgres psql -c "CREATE ROLE \"www-data\" LOGIN" && \
  sudo -u postgres createdb -O "www-data" padelio'

# migrate/seed
ssh dilu 'cd /srv/padelio && sudo -u www-data php artisan migrate --force && \
  sudo -u www-data php artisan db:seed --force'
```

Konfigurasi siap pakai ada di `deploy/dilu/`:

| File | Tujuan |
|---|---|
| `padelio.caddy` | site block Caddy `padelio.naygars.site` |
| `php-fpm-pool.conf` | pool `padelio` port 9001 |
| `backup.sh` + units | dump harian + rclone offsite |
| `promtail.yml` + unit | kirim log ke Loki |
| `padelio-health.*` | monitor 5 menit + ntfy |

Setelah mengubah kode: restart `php8.3-fpm` (opcache) dan `caddy reload`. Setelah mengubah `.env`: restart FPM.

---

## Operasional

| Layanan | Lokasi | Jadwal |
|---|---|---|
| `php8.3-fpm` + `caddy` | dilu | selalu |
| `padelio-backup-dilu.timer` | dilu | 03:50, retensi 14 hari + `rclone` ke `mael:padelio/db` |
| `padelio-health.timer` | dilu | tiap 5 menit → ntfy internal |
| `padelio-promtail-dilu` | dilu | stream ke Loki `{job="padelio",host="dilu"}` |
| `cloudflared` | laptop | tunnel `hermes` |

Backup produksi ada di `/var/backups/padelio`; restore drill sudah diuji ke database sementara.

---

## Struktur penting

```
app/Enums/            status challenge/match/booking/event
app/Http/Controllers/ publik, user, Auth, Admin
app/Http/Middleware/  SecurityHeaders (CSP), EnsureRole
app/Models/           18 model (relasi memakai ID)
app/Policies/         otorisasi challenge, match, booking, bracket, event
app/Services/         Elo, Match, Booking, Bracket, Notification, Audit
database/migrations/  skema + partial unique index booking + check constraint
resources/views/      Blade: publik/user + admin
public/css|js/        design system + interaksi kecil
deploy/dilu/          konfigurasi produksi
scripts/              dev-server, backup, healthcheck, push-all
```

---

## Keamanan

| Kontrol | Implementasi |
|---|---|
| Password | Argon2id, tanpa plaintext |
| Sesi | Database session, regenerasi login/logout, cookie Secure/HttpOnly/SameSite=Lax |
| CSRF | `@csrf`; POST tanpa token = 419 |
| Otorisasi | Policy per aksi + middleware `role:admin` |
| Admin | Cloudflare Access di `padelio.naygars.site/admin` (policy account member) |
| Edge | Rate limit login 10 POST/10s per IP (Cloudflare), security level high |
| XSS | Blade `{{ }}`; tidak ada `{!! !!}` untuk input user |
| CSP | `script-src 'self' https://static.cloudflareinsights.com`; frame-ancestors none |
| Header | HSTS, XFO DENY, nosniff, Referrer-Policy, Permissions-Policy, COOP/CORP |
| Anti-index | `robots.txt Disallow: /` + `X-Robots-Tag: noindex` |
| Kebocoran versi | `X-Powered-By` dihapus + `expose_php=Off` |
| Harga | Dihitung server; parameter klien diabaikan |
| Slot ganda | Partial unique index `(court_id,date,slot)` |
| Race ELO | Transaksi + lock + guard idempoten |
| Upload | Validasi image/MIME, re-encode 200×200 JPEG, nama acak |
| Audit | `audit_logs` untuk approve/cancel/suspend/rating/bracket |

---

## Batasan saat ini

- Pembayaran masih mock (QRIS/Transfer/Saldo), belum gateway.
- Belum ada verifikasi email/reset password (SMTP belum tersedia).
- Belum ada 2FA admin.
- Safari/WebKit dan screen reader manual belum diuji; Firefox + Chromium sudah.
- Cloudflare Web Analytics aktif; beacon diizinkan CSP (bisa dimatikan di dashboard).
- Host uji `padelio-test.naygars.site` sudah dihapus setelah audit.

---

## Referensi

- Rencana migrasi: `~/notes/plans/20261028_padelio-php-migration.{md,pdf}`
- Mock asli: `~/me/pkl/padel` (repo privat `diluviumm/padelio`)

_Dibangun 28–29 Oktober 2026 dari mock Padelio v5._
