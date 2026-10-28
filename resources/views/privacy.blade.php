@extends('layouts.app')
@section('title', 'Kebijakan Privasi')

@section('content')
<div class="page-head">
    <h1>Kebijakan Privasi</h1>
    <p>Berlaku untuk Padelio di padelio.naygars.site.</p>
</div>

<div class="card pad" style="max-width:820px">
    <b class="sec-title" style="margin-top:0">Data yang diproses</b>
    <p class="small muted">Nama, email, kota, umur (opsional), foto profil (opsional), hasil pertandingan, rating ELO, tantangan, booking lapangan, dan review. Data ini diperlukan agar ladder, booking, dan panel admin berfungsi.</p>

    <b class="sec-title">Penyimpanan dan keamanan</b>
    <p class="small muted">Data disimpan di PostgreSQL milik pengelola. Password disimpan sebagai hash Argon2id, bukan teks biasa. Sesi memakai cookie <code>HttpOnly</code>, <code>Secure</code>, dan <code>SameSite=Lax</code>.</p>

    <b class="sec-title">Pihak ketiga</b>
    <p class="small muted">Cloudflare menangani DNS, TLS, dan proteksi edge; Cloudflare Web Analytics dapat menerima data teknis agregat (halaman, browser, perkiraan lokasi) sesuai kebijakan Cloudflare. Tidak ada penjualan data.</p>

    <b class="sec-title">Retensi dan hak</b>
    <p class="small muted">Data demo disimpan untuk showcase. Backup harian disimpan 14 hari. Kamu dapat meminta koreksi atau penghapusan akun dengan menghubungi <a href="mailto:admin@padelio.id">admin@padelio.id</a>; permintaan diverifikasi sebelum diproses.</p>

    <b class="sec-title">Cookie</b>
    <p class="small muted">Hanya cookie esensial: sesi login, token CSRF, dan preferensi tema (localStorage). Tidak ada cookie iklan.</p>

    <p class="faint small" style="margin-top:16px">Terakhir diperbarui: 29 Oktober 2026. Ini adalah kebijakan untuk aplikasi demo komunitas, bukan nasihat hukum.</p>
</div>
@endsection
