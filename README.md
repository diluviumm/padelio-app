# Padelio — Laravel 13

Aplikasi komunitas padel: ladder ELO, challenge, booking lapangan, event + bracket, dan panel admin.

> **Status:** berjalan lokal di `http://127.0.0.1:8001` (dev server). **Tidak** dideploy ke `padel.ishmly.space` sesuai instruksi 28 Okt 2026.
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

---

## Fitur

- **Publik:** landing, ranking per level/periode, profil pemain, hasil pertandingan, direktori lapangan, event, bracket, statistik, tentang.
- **Pemain:** dashboard, kirim/terima/tolak tantangan, atur jadwal, input skor best of 3, konfirmasi skor, sewa lapangan + checkout, review lapangan, daftar event, notifikasi, edit profil + upload foto.
- **Admin:** dashboard KPI, approve/tolak hasil (dua kontrol: pemain lalu admin), kelola pemain (rating/suspend), booking, lapangan + maintenance, peralatan + stok, review, event + pendaftar + bracket 8/16.

---

## Akun Demo

| Peran | Email | Password | Pemain |
|---|---|---|---|
| Admin | `admin@padelio.id` | `admin12345` | — |
| User | `user@padelio.id` | `user12345` | Hadi |
| User | `andi@padelio.id` | `andi12345` | Andi |
| User | `rio@padelio.id` | `rio12345` | Rio |

> Kredensial demo hanya untuk lokal. Hapus/rotasi sebelum produksi.

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

---

## Batasan (disengaja)

- Pembayaran masih mock (QRIS/Transfer/Saldo dicatat, tanpa gateway).
- Belum ada email verification / reset password via email.
- Notifikasi in-app saja (queue `sync`).
- Belum ada deployment publik; menunggu keputusan hosting (bukan `padel.ishmly.space`).

---

## Referensi

- Rencana migrasi: `~/notes/plans/20261028_padelio-php-migration.{md,pdf}`
- Mock asli: `~/me/pkl/padel` (repo privat `diluviumm/padelio`)

---

_Dibangun 28 Oktober 2026 dari mock Padelio v5; parity fitur, dengan perbaikan keamanan dan integritas data._
