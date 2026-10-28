# Padelio — Laravel 13

Aplikasi komunitas padel: ladder ELO, challenge, booking lapangan, event + bracket, dan panel admin.

> **Status:** live di **https://padelio.naygars.site** (Cloudflare Tunnel → `127.0.0.1:8001` di laptop).
> Tidak memakai `padel.ishmly.space`.
> **Repo publik:** `github.com/diluviumm/padelio-app` · **Backup privat:** `github.com/diluviumm/padelio-app-backup` (setiap push ke `origin` dikirim ke keduanya).
> Repo mock lama (`diluviumm/padelio`) tetap menjadi referensi tampilan; proyek ini adalah implementasi full PHP.

---

## Stack

| Komponen | Versi |
|---|---|
| PHP | 8.5 |
| Laravel | 13.39 |
| Database | PostgreSQL 18 (cluster user, port 5544) |
| Auth | Laravel Auth + Argon2id (`password` cast `hashed`) |
| Frontend | Blade + CSS custom (tanpa Node/build step) |
| Testing | PHPUnit 12 (44 test, 119 assertion) |
| Deployment | Cloudflare Tunnel `hermes` → systemd user `padelio-app` (:8001) + `padelio-pg` (:5544) |

---

## Fitur

- **Publik:** landing, ranking per level/periode, profil pemain, hasil pertandingan, direktori lapangan, event, bracket, statistik, tentang.
- **Pemain:** dashboard, kirim/terima/tolak tantangan, atur jadwal, input skor best of 3, konfirmasi skor, sewa lapangan + checkout, review lapangan, daftar event, notifikasi, edit profil + upload foto.
- **Admin:** dashboard KPI, approve/tolak hasil (dua kontrol: pemain lalu admin), kelola pemain (rating/suspend), booking, lapangan + maintenance, peralatan + stok, review, event + pendaftar + bracket 8/16.

---

## Akun Demo

| Peran | Email | Password | Pemain |
|---|---|---|---|
| Admin | `admin@padelio.id` | *dirotasi* | — |
| User | `user@padelio.id` | *dirotasi* | Hadi |
| User | `andi@padelio.id` | *dirotasi* | Andi |
| User | `rio@padelio.id` | *dirotasi* | Rio |

> Semua password demo dirotasi 29 Okt 2026. Nilai disimpan di luar repo; tidak pernah dicetak atau di-commit.

---

## Menjalankan

### 1. Database (cluster PostgreSQL milik user, tanpa root)

```bash
initdb -D ~/.local/share/padelio/pgdata -U padelio --auth=trust --encoding=UTF8 --locale=C.UTF-8
pg_ctl -D ~/.local/share/padelio/pgdata -l ~/.local/share/padelio/pg.log \
  -o "-p 5544 -k /tmp -c listen_addresses=127.0.0.1" start
createdb -h 127.0.0.1 -p 5544 -U padelio padelio
createdb -h 127.0.0.1 -p 5544 -U padelio padelio_test
```

### 2. Aplikasi

```bash
composer install
cp .env.example .env          # jika belum ada
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve --host=127.0.0.1 --port=8001
```

Buka `http://127.0.0.1:8001`.

### 3. Test

```bash
php artisan test
```

44 test / 119 assertion, memakai database `padelio_test` (lihat `phpunit.xml`) dan `RefreshDatabase`.

### 4. Layanan produksi (user systemd, tanpa root)

```bash
systemctl --user enable --now padelio-pg.service
systemctl --user enable --now padelio-app.service
systemctl --user restart padelio-app.service   # setelah ubah kode
systemctl --user status padelio-app padelio-pg
```

- `padelio-app.service` → `php artisan serve --host=127.0.0.1 --port=8001 --no-reload` (4 worker).
- `padelio-pg.service` → PostgreSQL cluster `~/.local/share/padelio/pgdata`.
- Cloudflare Tunnel: entri `padelio.naygars.site → http://localhost:8001` di `~/.cloudflared/config.yml`.
- Kredensial admin/pemain disimpan di luar repo (file mode 600); tidak pernah di-commit.

---

## Struktur Penting

```
app/Enums/            status challenge/match/booking/event
app/Http/Controllers/ publik, user, Auth, Admin
app/Http/Middleware/  SecurityHeaders (CSP), EnsureRole
app/Models/           18 model (relasi memakai ID, bukan nama)
app/Policies/         otorisasi challenge, match, booking, bracket, event
app/Services/         EloService, MatchService, BookingService, BracketService, NotificationService, AuditService
database/migrations/  skema + partial unique index booking + check constraint
resources/views/      Blade: 14 halaman user + 10 halaman admin
public/css/style.css  design system (dark/light)
public/js/app.js      tema, dropdown, konfirmasi, auto-submit, toast
public/js/booking.js  update total checkout
```

---

## Keamanan

| Kontrol | Implementasi |
|---|---|
| Password | Argon2id (cast `hashed`), tanpa plaintext |
| Sesi | Database session, regenerasi saat login/logout |
| CSRF | `@csrf` di semua form; POST tanpa token = 419 |
| Otorisasi | Policy per aksi + middleware `role:admin` |
| XSS | Blade `{{ }}`, tidak ada `{!! !!}` untuk input user |
| CSP | Header middleware: `script-src 'self'`, `frame-ancestors 'none'` |
| Header lain | HSTS (HTTPS), `X-Frame-Options DENY`, nosniff, Referrer-Policy, Permissions-Policy |
| Brute force | Login 5/menit per email+IP; throttle route sensitif |
| Integritas harga | Harga booking dihitung server; parameter klien diabaikan |
| Slot ganda | Partial unique index `(court_id,date,slot) WHERE status IN ('hold','confirmed')` |
| Race ELO | Transaksi + `lockForUpdate` + guard idempoten |
| Upload | Validasi `image` + MIME, re-encode 200×200 JPEG, nama acak |
| Audit | `audit_logs` untuk approve, cancel, suspend, rating, bracket |
| Tunnel | Cloudflare Tunnel; origin hanya `127.0.0.1:8001`, tidak ada port publik |
| Anti-index | `X-Robots-Tag: noindex, nofollow` + `APP_ENV=production` + `APP_DEBUG=false` |
| Header | X-Powered-By dihapus; COOP/CORP same-origin; CSP mengizinkan beacon Web Analytics Cloudflare |

---

## Batasan (disengaja)

- Pembayaran masih mock (QRIS/Transfer/Saldo dicatat, tanpa gateway).
- Belum ada email verification / reset password via email.
- Notifikasi in-app saja (queue `sync`).
- Server aplikasi masih **PHP built-in server** (4 worker) — belum PHP-FPM/Caddy karena butuh akses root di server.
- Cloudflare Web Analytics aktif di zone `naygars.site`; beacon-nya diizinkan CSP. Jika tidak diinginkan, matikan Web Analytics di dashboard Cloudflare.
- Backup DB manual; cron harian belum dipasang.
- Cloudflare Access untuk `/admin` belum dipasang (opsional).

---

## Referensi

- Rencana migrasi: `~/notes/plans/20261028_padelio-php-migration.{md,pdf}`
- Mock asli: `~/me/pkl/padel` (repo privat `diluviumm/padelio`)

---

_Dibangun 28 Oktober 2026 dari mock Padelio v5; parity fitur, dengan perbaikan keamanan dan integritas data._
